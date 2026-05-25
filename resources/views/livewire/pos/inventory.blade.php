<?php

use App\Models\Product;
use Livewire\Volt\Component;

new class extends Component {
    public string $search = '';
    public string $brandFilter = 'todos';
    public string $sortBy = 'name';

    public function with(): array
    {
        $query = Product::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('sku', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->brandFilter !== 'todos') {
            $query->where('brand', $this->brandFilter);
        }

        $products = $query->orderBy($this->sortBy)->get();

        $totalProducts = Product::count();
        $totalStock = Product::sum('stock');
        $lowStock = Product::where('stock', '<=', 2)->where('stock', '>', 0)->count();
        $outOfStock = Product::where('stock', 0)->count();
        $totalValue = Product::selectRaw('SUM(price * stock) as total')->value('total') ?? 0;

        return [
            'products' => $products,
            'brands' => ['todos', 'Nike', 'Jordan', 'Adidas', 'New Balance'],
            'totalProducts' => $totalProducts,
            'totalStock' => $totalStock,
            'lowStock' => $lowStock,
            'outOfStock' => $outOfStock,
            'totalValue' => $totalValue,
        ];
    }
}; ?>

<x-layouts.pos>
    <div>
        <div class="inventory-header">
            <div class="page-header-left">
                <h1>Inventario</h1>
                <div class="subtitle">Control de stock &middot; {{ $totalProducts }} productos registrados</div>
            </div>
            <div class="page-header-right">
                <button class="btn btn-secondary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                    Exportar
                </button>
                <button class="btn btn-primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    Nuevo Producto
                </button>
            </div>
        </div>

        {{-- Stats --}}
        <div class="inventory-stats">
            <div class="kpi-card">
                <div class="kpi-card-icon sales">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path></svg>
                </div>
                <h3>{{ $totalProducts }}</h3>
                <div class="kpi-label">Productos</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-card-icon orders">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline></svg>
                </div>
                <h3>{{ $totalStock }}</h3>
                <div class="kpi-label">Unidades en Stock</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-card-icon ticket">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                </div>
                <h3>{{ $lowStock }}</h3>
                <div class="kpi-label">Stock Bajo</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-card-icon clients">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                </div>
                <h3>${{ number_format($totalValue, 0, '.', ',') }}</h3>
                <div class="kpi-label">Valor Total</div>
            </div>
        </div>

        {{-- Filters --}}
        <div style="display:flex;gap:16px;margin-bottom:20px;align-items:center;">
            <div class="search-bar" style="flex:1;margin-bottom:0;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar por nombre o SKU...">
            </div>
            <div class="filter-tabs" style="margin-bottom:0;">
                @foreach($brands as $brand)
                    <button class="filter-tab {{ $brandFilter === $brand ? 'active' : '' }}" wire:click="$set('brandFilter', '{{ $brand }}')">
                        {{ $brand === 'todos' ? 'Todos' : $brand }}
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Table --}}
        <div class="inventory-table-wrap">
            <table class="inventory-table">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>SKU</th>
                        <th>Marca</th>
                        <th>Categor&iacute;a</th>
                        <th>Precio</th>
                        <th>Stock</th>
                        <th>Valor</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $product)
                        <tr>
                            <td>
                                <div class="inventory-product-cell">
                                    <div class="inventory-product-thumb">
                                        <div style="width:100%;height:100%;background:#2a2a2a;display:flex;align-items:center;justify-content:center;font-size:16px;">👟</div>
                                    </div>
                                    <div>
                                        <strong>{{ $product->name }}</strong>
                                        <div style="font-size:11px;color:var(--text-muted);">{{ $product->colorway }}</div>
                                    </div>
                                </div>
                            </td>
                            <td style="color:var(--text-muted);font-family:monospace;font-size:12px;">{{ $product->sku }}</td>
                            <td>{{ $product->brand }}</td>
                            <td>{{ $product->category }}</td>
                            <td style="font-weight:600;">${{ number_format($product->price, 0, '.', ',') }}</td>
                            <td>
                                <span style="font-weight:700;">{{ $product->stock }}</span>
                            </td>
                            <td style="color:var(--text-muted);">${{ number_format($product->price * $product->stock, 0, '.', ',') }}</td>
                            <td>
                                @if($product->stock <= 0)
                                    <span class="stock-indicator low">Agotado</span>
                                @elseif($product->stock <= 3)
                                    <span class="stock-indicator medium">Bajo</span>
                                @else
                                    <span class="stock-indicator high">OK</span>
                                @endif
                            </td>
                            <td>
                                <button class="btn-edit-product" onclick="window.location='{{ route('pos.inventory') }}/{{ $product->id }}/edit'" title="Editar producto">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                    Editar
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.pos>
