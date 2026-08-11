<?php

namespace App\Http\Controllers;

use App\Models\Layaway;
use App\Models\Product;
use App\Models\ProductSize;
use App\Models\Sale;
use App\Services\WhatsAppNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LayawayController extends Controller
{
    public function index(Request $request)
    {
        Layaway::processExpirations();

        $statusFilter = $request->query('status', 'activo');
        $search = trim($request->query('search', ''));

        $query = Layaway::with(['product', 'employee', 'payments']);

        if ($statusFilter !== 'todos') {
            $query->where('status', $statusFilter);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('folio', 'like', '%' . $search . '%')
                  ->orWhere('customer_name', 'like', '%' . $search . '%')
                  ->orWhere('customer_phone', 'like', '%' . $search . '%')
                  ->orWhere('product_name', 'like', '%' . $search . '%');
            });
        }

        $layaways = $query->latest()->get();

        // Productos disponibles para apartar (con stock)
        $products = Product::where('stock', '>', 0)->orderBy('name')->get();

        $stats = [
            'activos' => Layaway::where('status', 'activo')->count(),
            'porCobrar' => Layaway::where('status', 'activo')->sum(DB::raw('total_price - paid_amount')),
            'conSaldo' => Layaway::where('status', 'vencido')->where('credit_expires_at', '>', now())->count(),
            'saldoTotal' => Layaway::where('status', 'vencido')->where('credit_expires_at', '>', now())->sum('paid_amount'),
        ];

        return view('pos.apartados', [
            'layaways' => $layaways,
            'products' => $products,
            'statusFilter' => $statusFilter,
            'search' => $search,
            'stats' => $stats,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'size' => 'nullable|string|max:10',
            'deposit' => 'required|numeric|min:1',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:30',
        ]);

        $product = Product::findOrFail($validated['product_id']);

        if ($product->stock <= 0) {
            return back()->with('error', 'El producto no tiene stock disponible para apartar.');
        }

        $size = $validated['size'] ?? null;
        if ($size) {
            $sizeStock = ProductSize::where('product_id', $product->id)->where('size', $size)->value('stock');
            if ($sizeStock !== null && $sizeStock <= 0) {
                return back()->with('error', 'La talla ' . $size . ' no tiene stock disponible.');
            }
        }

        $total = (float) $product->price;
        $deposit = round((float) $validated['deposit'], 2);

        if ($deposit > $total) {
            return back()->with('error', 'El anticipo no puede ser mayor al precio del producto ($' . number_format($total, 0, '.', ',') . ').');
        }

        $folio = 'AP-' . now()->format('Ymd') . '-' . str_pad(
            Layaway::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT
        );

        $layaway = DB::transaction(function () use ($product, $size, $deposit, $total, $folio, $validated) {
            // Reservar el stock (baja del disponible)
            Product::where('id', $product->id)->where('stock', '>', 0)->decrement('stock', 1);
            if ($size) {
                ProductSize::where('product_id', $product->id)->where('size', $size)
                    ->where('stock', '>', 0)->decrement('stock', 1);
            }

            $layaway = Layaway::create([
                'folio' => $folio,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'size' => $size,
                'employee_id' => auth()->id(),
                'customer_name' => $validated['customer_name'] ?? null,
                'customer_phone' => $validated['customer_phone'] ?? null,
                'total_price' => $total,
                'paid_amount' => $deposit,
                'status' => 'activo',
                'due_at' => now()->addDays(30),
                'credit_expires_at' => now()->addDays(30)->addMonth(),
            ]);

            $layaway->payments()->create([
                'employee_id' => auth()->id(),
                'amount' => $deposit,
                'type' => 'anticipo',
            ]);

            return $layaway;
        });

        // Si el anticipo cubre el total, se liquida de inmediato
        if ($layaway->remaining() <= 0) {
            $this->liquidate($layaway);
            return redirect()->route('pos.apartados', ['folio_print' => $layaway->id])
                ->with('success', 'Apartado liquidado de inmediato. Folio ' . $layaway->folio);
        }

        WhatsAppNotifier::send(
            "📌 *Nuevo apartado* {$layaway->folio}\n" .
            "Producto: {$layaway->product_name}" . ($layaway->size ? " (Talla {$layaway->size})" : "") . "\n" .
            "Anticipo: $" . number_format($layaway->paid_amount, 0, '.', ',') . " · Resta: $" . number_format($layaway->remaining(), 0, '.', ',') . "\n" .
            ($layaway->customer_name ? "Cliente: {$layaway->customer_name}\n" : "") .
            "Atendió: " . auth()->user()->name . "\n" .
            "Liquidar antes del " . $layaway->due_at->format('d/m/Y') . " · Sneakers Colima"
        );

        return redirect()->route('pos.apartados', ['folio_print' => $layaway->id])
            ->with('success', 'Apartado creado. Folio ' . $layaway->folio . ' · Restan $' . number_format($layaway->remaining(), 0, '.', ','));
    }

    public function addPayment(Request $request, Layaway $layaway)
    {
        if ($layaway->status !== 'activo') {
            return back()->with('error', 'Solo se puede abonar a apartados activos.');
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:1|max:' . $layaway->remaining(),
        ]);

        $amount = round((float) $validated['amount'], 2);

        DB::transaction(function () use ($layaway, $amount) {
            $layaway->payments()->create([
                'employee_id' => auth()->id(),
                'amount' => $amount,
                'type' => 'abono',
            ]);
            $layaway->increment('paid_amount', $amount);
        });

        $layaway->refresh();

        if ($layaway->remaining() <= 0) {
            $this->liquidate($layaway);
            return back()->with('success', 'Apartado ' . $layaway->folio . ' liquidado. ¡Producto entregado!');
        }

        return back()->with('success', 'Abono registrado. Restan $' . number_format($layaway->remaining(), 0, '.', ',') . ' del apartado ' . $layaway->folio . '.');
    }

    /**
     * Convierte un apartado pagado por completo en una venta.
     * El stock ya está reservado desde la creación, no se toca aquí.
     */
    private function liquidate(Layaway $layaway): void
    {
        DB::transaction(function () use ($layaway) {
            $orderNumber = 'SC-' . now()->format('Ymd') . '-' . str_pad(
                Sale::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT
            );

            $sale = Sale::create([
                'order_number' => $orderNumber,
                'employee_id' => auth()->id(),
                'total' => $layaway->total_price,
                'payment_method' => 'apartado',
                'status' => 'completada',
                'customer_name' => $layaway->customer_name,
            ]);

            $sale->items()->create([
                'product_id' => $layaway->product_id,
                'size' => $layaway->size,
                'quantity' => 1,
                'price' => $layaway->total_price,
            ]);

            $layaway->update([
                'status' => 'liquidado',
                'liquidated_at' => now(),
            ]);
        });

        WhatsAppNotifier::send(
            "✅ *Apartado liquidado* {$layaway->folio}\n" .
            "Producto: {$layaway->product_name}" . ($layaway->size ? " (Talla {$layaway->size})" : "") . "\n" .
            "Total pagado: $" . number_format($layaway->total_price, 0, '.', ',') . "\n" .
            ($layaway->customer_name ? "Cliente: {$layaway->customer_name}\n" : "") .
            "Producto entregado · " . now()->format('d/m/Y H:i') . " · Sneakers Colima"
        );
    }

    public function folio(Layaway $layaway)
    {
        $layaway->load(['product', 'employee', 'payments']);

        return view('pos.apartado-folio', [
            'layaway' => $layaway,
        ]);
    }
}
