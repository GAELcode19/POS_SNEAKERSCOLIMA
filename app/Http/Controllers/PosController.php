<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductSize;
use App\Models\Sale;
use App\Models\CartCancellation;
use App\Models\User;
use App\Models\Setting;
use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    public function terminal(Request $request)
    {
        $search = $request->query('search', '');
        $brandFilter = $request->query('brand', 'todos');

        $query = Product::query();

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        if ($brandFilter !== 'todos') {
            $query->where('brand', $brandFilter);
        }

        $cart = session('cart', []);
        $cartTotal = array_sum(array_map(fn($item) => $item['price'] * $item['quantity'], $cart));
        $cartCount = array_sum(array_column($cart, 'quantity'));

        $shift = Shift::where('employee_id', auth()->id())
            ->whereNull('ended_at')
            ->first();

        if (!$shift) {
            $shift = Shift::create([
                'employee_id' => auth()->id(),
                'started_at' => now(),
            ]);
        }

        $recentSales = Sale::with('items.product', 'employee')
            ->where('status', 'completada')
            ->latest()
            ->take(20)
            ->get();

        return view('pos.terminal', [
            'products' => $query->get(),
            'brands' => array_merge(['todos'], Brand::orderBy('name')->pluck('name')->toArray()),
            'search' => $search,
            'brandFilter' => $brandFilter,
            'cart' => $cart,
            'cartTotal' => $cartTotal,
            'cartCount' => $cartCount,
            'employee' => auth()->user(),
            'shiftStart' => $shift->started_at->toIso8601String(),
            'recentSales' => $recentSales,
        ]);
    }

    public function addToCart(Request $request)
    {
        $product = Product::find($request->input('product_id'));
        if (!$product || $product->stock <= 0) {
            return back();
        }

        $size = $request->input('size', 'Unitalla');
        $cart = session('cart', []);

        $key = null;
        foreach ($cart as $i => $item) {
            if ($item['id'] === $product->id && $item['size'] === $size) {
                $key = $i;
                break;
            }
        }

        if ($key !== null) {
            $cart[$key]['quantity']++;
        } else {
            $cart[] = [
                'id' => $product->id,
                'name' => $product->name,
                'brand' => $product->brand,
                'colorway' => $product->colorway,
                'image' => $product->image,
                'price' => (float) $product->price,
                'quantity' => 1,
                'size' => $size,
            ];
        }

        session(['cart' => $cart]);
        return back();
    }

    public function addToCartByQr(Request $request)
    {
        $code = trim($request->input('code', ''));
        if (!$code) {
            return back()->with('error', 'Código QR vacío.');
        }

        $product = Product::where('sku', $code)
            ->orWhere('id', is_numeric($code) ? $code : 0)
            ->first();

        if (!$product || $product->stock <= 0) {
            return back()->with('error', 'Producto no encontrado o sin stock.');
        }

        $size = 'Unitalla';
        $cart = session('cart', []);

        $key = null;
        foreach ($cart as $i => $item) {
            if ($item['id'] === $product->id && $item['size'] === $size) {
                $key = $i;
                break;
            }
        }

        if ($key !== null) {
            $cart[$key]['quantity']++;
        } else {
            $cart[] = [
                'id' => $product->id,
                'name' => $product->name,
                'brand' => $product->brand,
                'colorway' => $product->colorway,
                'image' => $product->image,
                'price' => (float) $product->price,
                'quantity' => 1,
                'size' => $size,
            ];
        }

        session(['cart' => $cart]);
        return back()->with('success', $product->name . ' agregado al carrito.');
    }

    public function removeFromCart(Request $request)
    {
        $index = (int) $request->input('index');
        $cart = session('cart', []);

        if (isset($cart[$index])) {
            $item = $cart[$index];

            $reasonOption = $request->input('cancel_reason_option', '');
            $customReason = $request->input('custom_reason', '');
            $reason = $reasonOption === 'otro' && $customReason ? $customReason : ($reasonOption ?: null);

            if ($request->has('product_id')) {
                $cancellationsCount = CartCancellation::where('employee_id', auth()->id())
                    ->where('created_at', '>=', now()->startOfWeek())
                    ->count();
                $alertLevel = 'bajo';
                if (!$reason) $alertLevel = 'alto';
                elseif ($cancellationsCount >= 3) $alertLevel = 'medio';

                CartCancellation::create([
                    'employee_id' => auth()->id(),
                    'product_id' => $item['id'],
                    'size' => $item['size'],
                    'price' => $item['price'],
                    'reason' => $reason,
                    'alert_level' => $alertLevel,
                ]);
            }

            unset($cart[$index]);
            $cart = array_values($cart);
        }

        session(['cart' => $cart]);
        return back();
    }

    public function checkout(Request $request)
    {
        $cart = session('cart', []);
        if (empty($cart)) {
            return back();
        }

        $request->validate([
            'payment_method' => 'required|in:efectivo,tarjeta,transferencia',
        ]);

        $total = array_sum(array_map(fn($item) => $item['price'] * $item['quantity'], $cart));
        $orderNumber = 'SC-' . now()->format('Ymd') . '-' . str_pad(Sale::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);

        $sale = Sale::create([
            'order_number' => $orderNumber,
            'employee_id' => auth()->id(),
            'total' => $total,
            'payment_method' => $request->input('payment_method'),
            'status' => 'completada',
        ]);

        foreach ($cart as $item) {
            $sale->items()->create([
                'product_id' => $item['id'],
                'size' => $item['size'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
            ]);

            Product::where('id', $item['id'])->where('stock', '>=', $item['quantity'])->decrement('stock', $item['quantity']);
            ProductSize::where('product_id', $item['id'])->where('size', $item['size'])->where('stock', '>=', $item['quantity'])->decrement('stock', $item['quantity']);
        }

        session()->forget('cart');

        return redirect()->route('pos.terminal')->with('sale', [
            'id' => $sale->id,
            'order_number' => $sale->order_number,
            'employee' => auth()->user()->name,
            'payment_method' => $sale->payment_method,
            'items' => $cart,
            'total' => $total,
            'date' => now()->format('d/m/Y H:i'),
        ]);
    }

    public function dashboard()
    {
        $today = today();
        $yesterday = today()->subDay();

        $salesToday = Sale::whereDate('created_at', $today)->where('status', '!=', 'cancelada')->sum('total');
        $salesYesterday = Sale::whereDate('created_at', $yesterday)->where('status', '!=', 'cancelada')->sum('total');

        $onlineOrders = Sale::whereDate('created_at', $today)->where('is_online', true)->count();
        $clientsToday = Sale::whereDate('created_at', $today)->where('status', 'completada')->count();
        $clientsYesterday = Sale::whereDate('created_at', $yesterday)->where('status', 'completada')->count();

        $avgTicket = $clientsToday > 0 ? $salesToday / $clientsToday : 0;
        $avgTicketYesterday = $clientsYesterday > 0 ? $salesYesterday / $clientsYesterday : 0;

        $salesChange = $salesYesterday > 0 ? round((($salesToday - $salesYesterday) / $salesYesterday) * 100, 1) : 0;
        $ticketChange = $avgTicketYesterday > 0 ? round((($avgTicket - $avgTicketYesterday) / $avgTicketYesterday) * 100, 1) : 0;

        $onlineOrdersList = Sale::with(['items.product', 'employee'])
            ->whereDate('created_at', $today)
            ->where('is_online', true)
            ->latest()
            ->get();

        $cancellations = CartCancellation::with(['employee', 'product'])
            ->whereDate('created_at', $today)
            ->latest()
            ->get();

        $tz = config('app.timezone');
        $hourlyData = [];
        for ($h = 9; $h <= 21; $h++) {
            $hourlyData[$h] = Sale::whereDate('created_at', $today)
                ->whereRaw("EXTRACT(HOUR FROM created_at AT TIME ZONE 'UTC' AT TIME ZONE ?) = ?", [$tz, $h])
                ->where('status', '!=', 'cancelada')
                ->sum('total');
        }

        $weeklyData = [];
        for ($d = 6; $d >= 0; $d--) {
            $date = today()->subDays($d);
            $weeklyData[$date->translatedFormat('D')] = Sale::whereDate('created_at', $date)
                ->where('status', '!=', 'cancelada')
                ->sum('total');
        }

        $todaySales = Sale::with(['items.product', 'employee'])
            ->whereDate('created_at', $today)
            ->where('status', '!=', 'cancelada')
            ->latest()
            ->get();

        $onlineOrdersYesterday = Sale::whereDate('created_at', $yesterday)->where('is_online', true)->count();

        $topProducts = \Illuminate\Support\Facades\DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->whereDate('sales.created_at', $today)
            ->where('sales.status', '!=', 'cancelada')
            ->select('products.name', 'products.brand', 'products.price',
                \Illuminate\Support\Facades\DB::raw('SUM(sale_items.quantity) as total_qty'),
                \Illuminate\Support\Facades\DB::raw('SUM(sale_items.quantity * sale_items.price) as total_revenue'))
            ->groupBy('products.id', 'products.name', 'products.brand', 'products.price')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        $paymentMethods = Sale::whereDate('created_at', $today)
            ->where('status', '!=', 'cancelada')
            ->selectRaw('payment_method, COUNT(*) as count, SUM(total) as total')
            ->groupBy('payment_method')
            ->get()
            ->keyBy('payment_method');

        return view('pos.dashboard', [
            'salesToday' => $salesToday,
            'salesChange' => $salesChange,
            'onlineOrders' => $onlineOrders,
            'onlineOrdersYesterday' => $onlineOrdersYesterday,
            'clientsToday' => $clientsToday,
            'clientsYesterday' => $clientsYesterday,
            'avgTicket' => $avgTicket,
            'ticketChange' => $ticketChange,
            'onlineOrdersList' => $onlineOrdersList,
            'cancellations' => $cancellations,
            'hourlyData' => $hourlyData,
            'weeklyData' => $weeklyData,
            'todaySales' => $todaySales,
            'topProducts' => $topProducts,
            'paymentMethods' => $paymentMethods,
        ]);
    }

    public function team(Request $request)
    {
        $search = $request->query('search', '');
        $statusFilter = $request->query('status', 'todos');
        $selectedEmployeeId = $request->query('employee');

        $query = User::where('role', '!=', 'gerencia');

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        if ($statusFilter !== 'todos') {
            $query->where('status', $statusFilter);
        }

        $employees = $query->get();

        if (!$selectedEmployeeId) {
            $first = $employees->first();
            $selectedEmployeeId = $first?->id;
        }

        $weekStart = now()->startOfWeek();
        $totalHoursWeek = Shift::where('started_at', '>=', $weekStart)->sum('hours_logged');
        $totalSalesWeek = Sale::where('created_at', '>=', $weekStart)->where('status', '!=', 'cancelada')->sum('total');
        $activeCount = User::where('role', '!=', 'gerencia')->where('status', 'activo')->count();

        $selected = null;
        $selectedWeeklyHours = 0;
        $selectedSales = 0;
        $selectedSalesTotal = 0;
        $selectedCancellations = 0;
        $selectedCancellationsNoReason = 0;
        $selectedDailyHours = [];
        $hoursPercent = 0;
        $riskLevel = 'Bajo';
        $lastConnection = null;
        $selectedTodaySales = 0;
        $selectedTodaySalesTotal = 0;
        $selectedAvgTicket = 0;
        $selectedRecentSales = collect();

        if ($selectedEmployeeId) {
            $selected = User::find($selectedEmployeeId);
            if ($selected) {
                $selectedWeeklyHours = Shift::where('employee_id', $selected->id)
                    ->where('started_at', '>=', $weekStart)
                    ->sum('hours_logged');

                $selectedSales = Sale::where('employee_id', $selected->id)
                    ->where('created_at', '>=', $weekStart)
                    ->where('status', '!=', 'cancelada')
                    ->count();

                $selectedSalesTotal = Sale::where('employee_id', $selected->id)
                    ->where('created_at', '>=', $weekStart)
                    ->where('status', '!=', 'cancelada')
                    ->sum('total');

                $selectedTodaySales = Sale::where('employee_id', $selected->id)
                    ->whereDate('created_at', today())
                    ->where('status', '!=', 'cancelada')
                    ->count();

                $selectedTodaySalesTotal = Sale::where('employee_id', $selected->id)
                    ->whereDate('created_at', today())
                    ->where('status', '!=', 'cancelada')
                    ->sum('total');

                $selectedAvgTicket = $selectedSales > 0 ? round($selectedSalesTotal / $selectedSales) : 0;

                $lastShift = Shift::where('employee_id', $selected->id)
                    ->latest('started_at')
                    ->first();
                $lastConnection = $lastShift?->started_at;

                $selectedCancellations = CartCancellation::where('employee_id', $selected->id)
                    ->where('created_at', '>=', $weekStart)
                    ->count();

                $selectedCancellationsNoReason = CartCancellation::where('employee_id', $selected->id)
                    ->where('created_at', '>=', $weekStart)
                    ->whereNull('reason')
                    ->count();

                $days = ['Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sab', 'Dom'];
                for ($d = 0; $d < 7; $d++) {
                    $day = $weekStart->copy()->addDays($d);
                    $hours = Shift::where('employee_id', $selected->id)
                        ->whereDate('started_at', $day)
                        ->sum('hours_logged');
                    $selectedDailyHours[] = [
                        'label' => $days[$d],
                        'hours' => (float) $hours,
                    ];
                }

                $hoursPercent = $selected->weekly_hours_target > 0
                    ? round(($selectedWeeklyHours / $selected->weekly_hours_target) * 100)
                    : 0;

                $selectedRecentSales = Sale::with('items.product')
                    ->where('employee_id', $selected->id)
                    ->where('created_at', '>=', $weekStart)
                    ->where('status', '!=', 'cancelada')
                    ->latest()
                    ->take(5)
                    ->get();

                if ($selectedCancellationsNoReason >= 2) $riskLevel = 'Alto';
                elseif ($selectedCancellations >= 2) $riskLevel = 'Medio';
            }
        }

        return view('pos.team', [
            'employees' => $employees,
            'search' => $search,
            'statusFilter' => $statusFilter,
            'selectedEmployeeId' => $selectedEmployeeId,
            'totalHoursWeek' => $totalHoursWeek,
            'totalSalesWeek' => $totalSalesWeek,
            'activeCount' => $activeCount,
            'selected' => $selected,
            'selectedWeeklyHours' => $selectedWeeklyHours,
            'selectedSales' => $selectedSales,
            'selectedSalesTotal' => $selectedSalesTotal,
            'selectedTodaySales' => $selectedTodaySales,
            'selectedTodaySalesTotal' => $selectedTodaySalesTotal,
            'selectedAvgTicket' => $selectedAvgTicket,
            'lastConnection' => $lastConnection,
            'selectedCancellations' => $selectedCancellations,
            'selectedCancellationsNoReason' => $selectedCancellationsNoReason,
            'selectedDailyHours' => $selectedDailyHours,
            'hoursPercent' => $hoursPercent,
            'selectedRecentSales' => $selectedRecentSales,
            'riskLevel' => $riskLevel,
        ]);
    }

    public function inventory(Request $request)
    {
        $search = $request->query('search', '');
        $brandFilter = $request->query('brand', 'todos');
        $sortBy = $request->query('sort', 'name');

        $query = Product::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('sku', 'like', '%' . $search . '%');
            });
        }

        if ($brandFilter !== 'todos') {
            $query->where('brand', $brandFilter);
        }

        $products = $query->with('sizes')->orderBy($sortBy)->get();

        $totalProducts = Product::count();
        $totalStock = Product::sum('stock');
        $lowStock = Product::where('stock', '<=', 2)->where('stock', '>', 0)->count();
        $outOfStock = Product::where('stock', 0)->count();
        $totalValue = Product::selectRaw('SUM(price * stock) as total')->value('total') ?? 0;

        return view('pos.inventory', [
            'products' => $products,
            'brands' => array_merge(['todos'], Brand::orderBy('name')->pluck('name')->toArray()),
            'categories' => Category::orderBy('name')->pluck('name')->toArray(),
            'search' => $search,
            'brandFilter' => $brandFilter,
            'totalProducts' => $totalProducts,
            'totalStock' => $totalStock,
            'lowStock' => $lowStock,
            'outOfStock' => $outOfStock,
            'totalValue' => $totalValue,
        ]);
    }

    public function storeProduct(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'colorway' => 'nullable|string|max:255',
            'sku' => 'required|string|max:100',
            'brand' => 'required|string|max:100',
            'category' => 'required|string|max:100',
            'price' => 'required|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image' => 'nullable|image|max:5120',
        ]);
        $validated['cost'] = $validated['cost'] ?? 0;

        $baseSku = $validated['sku'];
        $sku = $baseSku;
        $counter = 1;
        while (Product::where('sku', $sku)->exists()) {
            $sku = $baseSku . '-' . $counter;
            $counter++;
        }
        $validated['sku'] = $sku;

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        Product::create($validated);

        return redirect()->route('pos.inventory')->with('success', 'Producto creado correctamente.');
    }

    public function updateProduct(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'colorway' => 'nullable|string|max:255',
            'sku' => 'required|string|max:100|unique:products,sku,' . $product->id,
            'brand' => 'required|string|max:100',
            'category' => 'required|string|max:100',
            'price' => 'required|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image' => 'nullable|image|max:5120',
        ]);
        $validated['cost'] = $validated['cost'] ?? 0;

        if ($request->hasFile('image')) {
            if ($product->image) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($product->image);
            }
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($validated);

        return redirect()->route('pos.inventory')->with('success', 'Producto actualizado correctamente.');
    }

    public function updateImage(Request $request, Product $product)
    {
        $request->validate([
            'image' => 'required|image|max:5120',
        ]);

        if ($product->image) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($product->image);
        }

        $product->update([
            'image' => $request->file('image')->store('products', 'public'),
        ]);

        return redirect()->route('pos.inventory')->with('success', 'Imagen actualizada.');
    }

    public function updateSizes(Request $request, Product $product)
    {
        $request->validate([
            'sizes' => 'required|array|min:1',
            'sizes.*.size' => 'required|string|max:10',
            'sizes.*.stock' => 'required|integer|min:0',
        ]);

        $product->sizes()->delete();

        $totalStock = 0;
        foreach ($request->input('sizes') as $entry) {
            if (empty($entry['size'])) continue;
            $product->sizes()->create([
                'size' => $entry['size'],
                'stock' => (int) $entry['stock'],
            ]);
            $totalStock += (int) $entry['stock'];
        }

        $product->update(['stock' => $totalStock]);

        return redirect()->route('pos.inventory')->with('success', 'Tallas actualizadas correctamente.');
    }

    public function destroyProduct(Product $product)
    {
        if ($product->image) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($product->image);
        }
        $product->delete();

        return redirect()->route('pos.inventory')->with('success', 'Producto eliminado correctamente.');
    }

    public function storeEmployee(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|in:asesor_ventas,supervisor,cajero',
            'weekly_hours_target' => 'nullable|numeric|min:0|max:168',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'role' => $validated['role'],
            'status' => 'desconectado',
            'hired_at' => now(),
            'weekly_hours_target' => $validated['weekly_hours_target'] ?? 40,
        ]);

        return redirect()->route('pos.team')->with('success', 'Empleado creado correctamente.');
    }

    public function updateEmployee(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6',
            'role' => 'required|in:asesor_ventas,supervisor,cajero,gerencia',
            'status' => 'required|in:activo,descanso,desconectado',
            'weekly_hours_target' => 'nullable|numeric|min:0|max:168',
        ]);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'status' => $validated['status'],
            'weekly_hours_target' => $validated['weekly_hours_target'] ?? $user->weekly_hours_target,
        ];

        if (!empty($validated['password'])) {
            $data['password'] = bcrypt($validated['password']);
        }

        $user->update($data);

        return redirect()->route('pos.team', ['employee' => $user->id])->with('success', 'Empleado actualizado.');
    }

    public function destroyEmployee(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'No puedes eliminar tu propia cuenta.');
        }

        $user->shifts()->delete();
        $user->cartCancellations()->update(['employee_id' => null]);
        $user->delete();

        return redirect()->route('pos.team')->with('success', 'Empleado eliminado correctamente.');
    }

    public function returnSearch(Request $request)
    {
        $request->validate(['order_number' => 'required|string']);

        $sale = Sale::with('items.product', 'employee')
            ->where('order_number', $request->input('order_number'))
            ->first();

        if (!$sale) {
            return back()->with('returnError', 'No se encontró la venta con ese número de orden.');
        }

        if ($sale->status === 'devuelta') {
            return back()->with('returnError', 'Esta venta ya fue devuelta completamente.');
        }

        $returnSale = [
            'id' => $sale->id,
            'order_number' => $sale->order_number,
            'employee' => $sale->employee?->name ?? 'Empleado',
            'date' => $sale->created_at->format('d/m/Y H:i'),
            'total' => $sale->total,
            'items' => $sale->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->product?->name ?? 'Producto eliminado',
                    'size' => $item->size,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'product_id' => $item->product_id,
                ];
            })->toArray(),
        ];

        return back()->with('returnSale', $returnSale);
    }

    public function returnProcess(Request $request)
    {
        $request->validate([
            'sale_id' => 'required|exists:sales,id',
            'return_items' => 'required|array|min:1',
            'return_reason' => 'required|string',
        ]);

        $sale = Sale::with('items.product')->findOrFail($request->input('sale_id'));
        $returnItemIds = $request->input('return_items');
        $reason = $request->input('return_reason');
        $returnTotal = 0;

        foreach ($sale->items as $item) {
            if (!in_array($item->id, $returnItemIds)) continue;

            if ($item->product) {
                Product::where('id', $item->product_id)->increment('stock', $item->quantity);
                ProductSize::where('product_id', $item->product_id)->where('size', $item->size)->increment('stock', $item->quantity);
            }

            CartCancellation::create([
                'employee_id' => auth()->id(),
                'product_id' => $item->product_id,
                'size' => $item->size,
                'price' => $item->price * $item->quantity,
                'reason' => 'DEVOLUCIÓN: ' . $reason . ' (Orden: ' . $sale->order_number . ')',
                'alert_level' => 'medio',
            ]);

            $returnTotal += $item->price * $item->quantity;
        }

        if (count($returnItemIds) === $sale->items->count()) {
            $sale->update(['status' => 'devuelta']);
        }

        return redirect()->route('pos.terminal')
            ->with('returnSuccess', 'Devolución procesada correctamente. Se reembolsan $' . number_format($returnTotal, 0, '.', ',') . ' al cliente.');
    }

    public function alerts()
    {
        $alerts = collect();
        $weekStart = now()->startOfWeek();

        // 1. Cancelaciones sin motivo (últimos 7 días)
        $cancellationsNoReason = CartCancellation::with(['employee', 'product'])
            ->where('created_at', '>=', $weekStart)
            ->whereNull('reason')
            ->latest()
            ->get();

        foreach ($cancellationsNoReason as $c) {
            $alerts->push([
                'type' => 'seguridad',
                'level' => 'alta',
                'icon' => 'shield',
                'title' => 'Cancelación sin motivo',
                'message' => ($c->employee?->name ?? 'Empleado eliminado') . ' canceló "' . ($c->product?->name ?? 'Producto') . '" (Talla ' . $c->size . ') sin declarar motivo.',
                'amount' => $c->price,
                'time' => $c->created_at,
            ]);
        }

        // 2. Cancelaciones con motivo (info)
        $cancellationsWithReason = CartCancellation::with(['employee', 'product'])
            ->where('created_at', '>=', $weekStart)
            ->whereNotNull('reason')
            ->latest()
            ->get();

        foreach ($cancellationsWithReason as $c) {
            $alerts->push([
                'type' => 'seguridad',
                'level' => 'media',
                'icon' => 'shield',
                'title' => 'Cancelación registrada',
                'message' => ($c->employee?->name ?? 'Empleado') . ' canceló "' . ($c->product?->name ?? 'Producto') . '". Motivo: ' . $c->reason,
                'amount' => $c->price,
                'time' => $c->created_at,
            ]);
        }

        // 3. Productos agotados
        $outOfStock = Product::where('stock', 0)->get();
        foreach ($outOfStock as $p) {
            $alerts->push([
                'type' => 'inventario',
                'level' => 'alta',
                'icon' => 'package',
                'title' => 'Producto agotado',
                'message' => '"' . $p->name . '" (' . $p->brand . ') está sin stock. Necesita resurtido urgente.',
                'amount' => null,
                'time' => $p->updated_at,
            ]);
        }

        // 4. Stock bajo (1-2 unidades)
        $lowStock = Product::where('stock', '>', 0)->where('stock', '<=', 2)->get();
        foreach ($lowStock as $p) {
            $alerts->push([
                'type' => 'inventario',
                'level' => 'media',
                'icon' => 'package',
                'title' => 'Stock bajo: ' . $p->stock . ' unidad(es)',
                'message' => '"' . $p->name . '" (' . $p->brand . ') tiene solo ' . $p->stock . ' unidad(es). Considerar resurtido.',
                'amount' => null,
                'time' => $p->updated_at,
            ]);
        }

        // 5. Ventas grandes hoy (> $3,000)
        $bigSales = Sale::with('employee')
            ->whereDate('created_at', today())
            ->where('status', '!=', 'cancelada')
            ->where('total', '>=', 3000)
            ->latest()
            ->get();

        foreach ($bigSales as $s) {
            $alerts->push([
                'type' => 'ventas',
                'level' => 'info',
                'icon' => 'trending',
                'title' => 'Venta importante',
                'message' => ($s->employee?->name ?? 'Empleado') . ' realizó venta ' . $s->order_number . ' por $' . number_format($s->total, 0, '.', ','),
                'amount' => $s->total,
                'time' => $s->created_at,
            ]);
        }

        // 6. Empleados activos sin turno esta semana
        $inactiveEmployees = User::where('role', '!=', 'gerencia')
            ->where('status', 'activo')
            ->whereDoesntHave('shifts', function ($q) use ($weekStart) {
                $q->where('started_at', '>=', $weekStart);
            })
            ->get();

        foreach ($inactiveEmployees as $emp) {
            $alerts->push([
                'type' => 'equipo',
                'level' => 'media',
                'icon' => 'user',
                'title' => 'Empleado sin actividad',
                'message' => $emp->name . ' (' . $emp->roleLabel() . ') está marcado como activo pero no ha registrado turno esta semana.',
                'amount' => null,
                'time' => now(),
            ]);
        }

        // Ordenar por nivel de prioridad y luego por tiempo
        $levelOrder = ['alta' => 0, 'media' => 1, 'info' => 2];
        $alerts = $alerts->sortBy(function ($a) use ($levelOrder) {
            return ($levelOrder[$a['level']] ?? 3) . $a['time']->timestamp;
        })->values();

        $countByLevel = [
            'alta' => $alerts->where('level', 'alta')->count(),
            'media' => $alerts->where('level', 'media')->count(),
            'info' => $alerts->where('level', 'info')->count(),
        ];

        $countByType = [
            'seguridad' => $alerts->where('type', 'seguridad')->count(),
            'inventario' => $alerts->where('type', 'inventario')->count(),
            'ventas' => $alerts->where('type', 'ventas')->count(),
            'equipo' => $alerts->where('type', 'equipo')->count(),
        ];

        return view('pos.alerts', [
            'alerts' => $alerts,
            'totalAlerts' => $alerts->count(),
            'countByLevel' => $countByLevel,
            'countByType' => $countByType,
        ]);
    }

    public function storeBrand(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:brands,name',
        ]);

        $brand = Brand::create($validated);

        return response()->json(['name' => $brand->name]);
    }

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:categories,name',
        ]);

        $category = Category::create($validated);

        return response()->json(['name' => $category->name]);
    }

    public function destroyBrand(string $brandName)
    {
        $brand = Brand::where('name', $brandName)->firstOrFail();
        $inUse = Product::where('brand', $brand->name)->count();
        if ($inUse > 0) {
            return response()->json(['error' => 'No se puede eliminar, hay ' . $inUse . ' producto(s) con esta marca.'], 422);
        }

        $brand->delete();
        return response()->json(['success' => true]);
    }

    public function destroyCategory(string $categoryName)
    {
        $category = Category::where('name', $categoryName)->firstOrFail();
        $inUse = Product::where('category', $category->name)->count();
        if ($inUse > 0) {
            return response()->json(['error' => 'No se puede eliminar, hay ' . $inUse . ' producto(s) con esta categoría.'], 422);
        }

        $category->delete();
        return response()->json(['success' => true]);
    }

    public function exportInventory()
    {
        $products = Product::with('sizes')->orderBy('name')->get();

        $csv = "\xEF\xBB\xBF"; // BOM for Excel UTF-8
        $csv .= "SKU,Nombre,Marca,Categoría,Colorway,Precio,Costo,Stock,Tallas\n";

        foreach ($products as $p) {
            $sizes = $p->sizes->map(fn($s) => $s->size . ':' . $s->stock)->implode(' | ');
            $csv .= '"' . str_replace('"', '""', $p->sku) . '",';
            $csv .= '"' . str_replace('"', '""', $p->name) . '",';
            $csv .= '"' . str_replace('"', '""', $p->brand) . '",';
            $csv .= '"' . str_replace('"', '""', $p->category) . '",';
            $csv .= '"' . str_replace('"', '""', $p->colorway ?? '') . '",';
            $csv .= $p->price . ',';
            $csv .= $p->cost . ',';
            $csv .= $p->stock . ',';
            $csv .= '"' . $sizes . '"' . "\n";
        }

        $filename = 'inventario_' . now()->format('Y-m-d_His') . '.csv';

        return response($csv)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    public function updateAvatar(Request $request, User $user)
    {
        $request->validate([
            'avatar' => 'required|image|max:2048',
        ]);

        if ($user->avatar) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($user->avatar);
        }

        $user->update([
            'avatar' => $request->file('avatar')->store('avatars', 'public'),
        ]);

        return back()->with('success', 'Foto actualizada.');
    }

    public function updateLogo(Request $request)
    {
        $request->validate([
            'logo' => 'required|image|max:2048',
        ]);

        $oldLogo = Setting::get('logo');
        if ($oldLogo) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($oldLogo);
        }

        $path = $request->file('logo')->store('branding', 'public');
        Setting::set('logo', $path);

        return back()->with('success', 'Logo actualizado.');
    }
}
