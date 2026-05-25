<?php

use App\Models\Product;
use App\Models\Brand;
use App\Models\ProductSize;
use Livewire\Volt\Component;

new class extends Component {
    public string $search = '';
    public string $brandFilter = 'todos';
    public array $cart = [];
    public ?int $selectingProductId = null;
    public string $selectedSize = '';

    public function openSizeSelector(int $productId): void
    {
        $this->selectingProductId = $productId;
        $this->selectedSize = '';
    }

    public function closeSizeSelector(): void
    {
        $this->selectingProductId = null;
        $this->selectedSize = '';
    }

    public function confirmAddToCart(): void
    {
        if (!$this->selectingProductId || !$this->selectedSize) return;

        $product = Product::find($this->selectingProductId);
        if (!$product) return;

        $sizeRecord = ProductSize::where('product_id', $product->id)
            ->where('size', $this->selectedSize)
            ->where('stock', '>', 0)
            ->first();

        if (!$sizeRecord) return;

        $existingKey = null;
        foreach ($this->cart as $i => $item) {
            if ($item['id'] === $product->id && $item['size'] === $this->selectedSize) {
                $existingKey = $i;
                break;
            }
        }

        if ($existingKey !== null) {
            if ($this->cart[$existingKey]['quantity'] < $sizeRecord->stock) {
                $this->cart[$existingKey]['quantity']++;
            }
        } else {
            $this->cart[] = [
                'id' => $product->id,
                'name' => $product->name,
                'brand' => $product->brand,
                'colorway' => $product->colorway,
                'price' => (float) $product->price,
                'quantity' => 1,
                'size' => $this->selectedSize,
            ];
        }

        $this->closeSizeSelector();
    }

    public function removeFromCart(int $index): void
    {
        if (isset($this->cart[$index])) {
            unset($this->cart[$index]);
            $this->cart = array_values($this->cart);
        }
    }

    public function getCartTotal(): float
    {
        return array_sum(array_map(fn($item) => $item['price'] * $item['quantity'], $this->cart));
    }

    public function getCartCount(): int
    {
        return array_sum(array_column($this->cart, 'quantity'));
    }

    public function with(): array
    {
        $query = Product::with(['sizes'])->where('stock', '>', 0);

        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        if ($this->brandFilter !== 'todos') {
            $query->where('brand', $this->brandFilter);
        }

        $brands = Brand::orderBy('name')->pluck('name')->toArray();
        array_unshift($brands, 'todos');

        $selectingProduct = null;
        $availableSizes = collect();
        if ($this->selectingProductId) {
            $selectingProduct = Product::find($this->selectingProductId);
            if ($selectingProduct) {
                $availableSizes = ProductSize::where('product_id', $selectingProduct->id)
                    ->where('stock', '>', 0)
                    ->orderBy('size')
                    ->get();
            }
        }

        return [
            'products' => $query->get(),
            'brands' => $brands,
            'cartTotal' => $this->getCartTotal(),
            'cartCount' => $this->getCartCount(),
            'employee' => auth()->user(),
            'selectingProduct' => $selectingProduct,
            'availableSizes' => $availableSizes,
        ];
    }
}; ?>

<x-layouts.pos>
    <div>
        {{-- POS Header --}}
        <div class="pos-header">
            <div class="pos-header-left">
                <h1>Punto de Venta</h1>
                <div class="subtitle">{{ now()->translatedFormat('l, d \\d\\e F \\d\\e Y') }}</div>
            </div>

            <div class="pos-header-center">
                <div class="pos-advisor">
                    <div class="pos-advisor-avatar">{{ $employee->initials() }}</div>
                    <div class="pos-advisor-info">
                        Asesor activo
                        <strong>{{ $employee->name }}</strong>
                    </div>
                </div>
                <div class="pos-timer">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                    <div>
                        Tiempo en turno
                        <br>
                        <strong>04:12 hrs</strong>
                    </div>
                </div>
            </div>

            <div class="pos-session-status">
                <span class="pos-session-dot"></span>
                Sesi&oacute;n activa
            </div>
        </div>

        <div class="pos-layout">
            {{-- Left Side: Products --}}
            <div>
                <div class="search-bar">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar producto...">
                </div>

                <div class="filter-tabs">
                    @foreach($brands as $brand)
                        <button
                            class="filter-tab {{ $brandFilter === $brand ? 'active' : '' }}"
                            wire:click="$set('brandFilter', '{{ $brand }}')"
                        >
                            {{ $brand === 'todos' ? 'Todos' : $brand }}
                        </button>
                    @endforeach
                </div>

                <div class="product-grid">
                    @foreach($products as $product)
                        <div class="product-card" wire:click="openSizeSelector({{ $product->id }})">
                            <div class="product-card-image">
                                @if($product->image)
                                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}">
                                @else
                                    <div style="width:100%;height:100%;background:linear-gradient(135deg, #2a2a2a 0%, #3a3a3a 100%);display:flex;align-items:center;justify-content:center;color:var(--text-muted);font-size:32px;">
                                        👟
                                    </div>
                                @endif
                                <span class="brand-tag">{{ $product->brand }}</span>
                                <span class="stock-tag {{ $product->stockUrgency() }}">{{ $product->stockLabel() }}</span>
                            </div>
                            <div class="product-card-body">
                                <h3>{{ $product->name }}</h3>
                                <div class="product-meta">{{ $product->colorway }} &middot; {{ $product->category }}</div>
                                <div class="product-card-footer">
                                    <span class="product-price">${{ number_format($product->price, 0, '.', ',') }}</span>
                                    <button class="product-add-btn" title="Agregar al carrito">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Right Side: Cart --}}
            <div class="cart-panel">
                <div class="cart-header">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                    Carrito
                </div>

                @if(count($cart) === 0)
                    <div class="cart-empty">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                        <p>Carrito vac&iacute;o</p>
                        <span>Selecciona productos para agregar</span>
                    </div>
                @else
                    <div class="cart-items">
                        @foreach($cart as $index => $item)
                            <div class="cart-item">
                                <div class="cart-item-image">
                                    <div style="width:100%;height:100%;background:#2a2a2a;display:flex;align-items:center;justify-content:center;font-size:18px;">👟</div>
                                </div>
                                <div class="cart-item-info">
                                    <h4>{{ $item['name'] }}</h4>
                                    <span>Talla {{ $item['size'] }} &middot; Cant: {{ $item['quantity'] }}</span>
                                </div>
                                <div class="cart-item-price">${{ number_format($item['price'] * $item['quantity'], 0, '.', ',') }}</div>
                                <button class="cart-item-remove" wire:click="removeFromCart({{ $index }})">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                </button>
                            </div>
                        @endforeach
                    </div>

                    <div class="cart-summary">
                        <div class="cart-summary-row">
                            <span>Subtotal ({{ $cartCount }} art.)</span>
                            <span>${{ number_format($cartTotal, 0, '.', ',') }}</span>
                        </div>
                        <div class="cart-summary-row total">
                            <span>Total</span>
                            <span>${{ number_format($cartTotal, 0, '.', ',') }}</span>
                        </div>
                    </div>

                    <button class="cart-checkout-btn">
                        Cobrar ${{ number_format($cartTotal, 0, '.', ',') }}
                    </button>
                @endif
            </div>
        </div>

        {{-- Modal Seleccionar Talla --}}
        @if($selectingProduct)
            <div class="modal-overlay" wire:click.self="closeSizeSelector">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2>Seleccionar Talla</h2>
                        <button class="modal-close" wire:click="closeSizeSelector">&times;</button>
                    </div>
                    <div class="modal-body" style="text-align:center;">
                        <div>
                            <strong style="font-size:16px;">{{ $selectingProduct->name }}</strong>
                            <div style="color:var(--accent-gold);font-size:15px;margin-top:4px;">${{ number_format($selectingProduct->price, 0, '.', ',') }}</div>
                        </div>

                        @if($availableSizes->count() > 0)
                            <div class="size-grid">
                                @foreach($availableSizes as $sizeOption)
                                    <label>
                                        <input type="radio" name="size" class="size-radio" value="{{ $sizeOption->size }}" wire:model="selectedSize">
                                        <span class="size-option">{{ $sizeOption->size }}</span>
                                    </label>
                                @endforeach
                            </div>

                            <button
                                class="btn btn-primary"
                                style="width:100%;padding:14px;font-size:15px;margin-top:8px;{{ !$selectedSize ? 'opacity:0.4;cursor:not-allowed;' : '' }}"
                                wire:click="confirmAddToCart"
                                @if(!$selectedSize) disabled @endif
                            >
                                + Agregar al carrito
                            </button>
                        @else
                            <div style="color:var(--red);padding:20px 0;">
                                No hay tallas disponibles para este producto.
                            </div>
                            <button class="btn btn-secondary" style="width:100%;padding:14px;" wire:click="closeSizeSelector">
                                Cerrar
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-layouts.pos>
