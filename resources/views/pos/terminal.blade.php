@extends('layouts.pos')

@section('title', 'POS Terminal - Sneakers Colima')

@section('content')
<div>
    @if(session('returnSuccess'))
        <div style="padding:14px 20px;background:var(--green-dim);border:1px solid rgba(34,197,94,0.3);border-radius:var(--radius-lg);color:var(--green);font-size:14px;font-weight:500;margin-bottom:16px;display:flex;align-items:center;gap:10px;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:20px;height:20px;flex-shrink:0;"><polyline points="20 6 9 17 4 12"></polyline></svg>
            {{ session('returnSuccess') }}
        </div>
    @endif
    @if(session('error'))
        <div style="padding:14px 20px;background:var(--red-dim);border:1px solid rgba(239,68,68,0.3);border-radius:var(--radius-lg);color:var(--red);font-size:14px;font-weight:500;margin-bottom:16px;display:flex;align-items:center;gap:10px;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:20px;height:20px;flex-shrink:0;"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
            {{ session('error') }}
        </div>
    @endif

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
                    <strong id="shiftTimer" data-start="{{ $shiftStart }}">00:00 hrs</strong>
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
            <div style="display:flex;gap:8px;align-items:stretch;">
                <form method="GET" action="{{ route('pos.terminal') }}" class="search-bar" style="flex:1;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Buscar producto...">
                    @if($brandFilter !== 'todos')
                        <input type="hidden" name="brand" value="{{ $brandFilter }}">
                    @endif
                </form>
                <button type="button" onclick="document.getElementById('qrCameraModal').style.display='flex'" style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:var(--radius);padding:0 16px;cursor:pointer;color:var(--text-secondary);display:flex;align-items:center;gap:8px;font-size:13px;font-weight:500;transition:all 0.2s;" onmouseover="this.style.borderColor='var(--accent-gold)';this.style.color='var(--accent-gold)'" onmouseout="this.style.borderColor='var(--border-color)';this.style.color='var(--text-secondary)'">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:20px;height:20px;"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect></svg>
                    QR
                </button>
            </div>

            <div class="filter-tabs">
                @foreach($brands as $brand)
                    <a
                        href="{{ route('pos.terminal', ['brand' => $brand, 'search' => $search ?: null]) }}"
                        class="filter-tab {{ $brandFilter === $brand ? 'active' : '' }}"
                    >
                        {{ $brand === 'todos' ? 'Todos' : $brand }}
                    </a>
                @endforeach
            </div>

            <div class="product-grid">
                @foreach($products as $product)
                    <div class="product-card" style="cursor:pointer;" data-id="{{ $product->id }}" data-name="{{ $product->name }}" data-category="{{ $product->category }}" data-price="{{ $product->price }}" data-stock="{{ $product->stock }}" data-sizes="{{ $product->sizes->pluck('size')->toJson() }}" onclick="openSizeModal(this)">
                        <div class="product-card-image">
                            @if($product->image)
                                <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}">
                            @else
                                <div style="width:100%;height:100%;background:linear-gradient(135deg, #2a2a2a 0%, #3a3a3a 100%);display:flex;align-items:center;justify-content:center;color:var(--text-muted);font-size:32px;">👟</div>
                            @endif
                            <span class="brand-tag">{{ $product->brand }}</span>
                            <span class="stock-tag {{ $product->stockUrgency() }}">{{ $product->stockLabel() }}</span>
                        </div>
                        <div class="product-card-body">
                            <h3>{{ $product->name }}</h3>
                            <div class="product-meta">{{ $product->colorway }} &middot; {{ $product->category }}</div>
                            <div class="product-card-footer">
                                <span class="product-price">${{ number_format($product->price, 0, '.', ',') }}</span>
                                <span class="product-add-btn">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                </span>
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
                                @if(!empty($item['image']))
                                    <img src="{{ asset('storage/' . $item['image']) }}" alt="{{ $item['name'] }}">
                                @else
                                    <div style="width:100%;height:100%;background:#2a2a2a;display:flex;align-items:center;justify-content:center;font-size:18px;">👟</div>
                                @endif
                            </div>
                            <div class="cart-item-info">
                                <h4>{{ $item['name'] }}</h4>
                                <span>Talla {{ $item['size'] }} &middot; Cant: {{ $item['quantity'] }}</span>
                            </div>
                            <div class="cart-item-price">${{ number_format($item['price'] * $item['quantity'], 0, '.', ',') }}</div>
                            <button type="button" class="cart-item-remove" onclick="openCancelModal({{ $index }}, '{{ addslashes($item['name']) }}', '{{ $item['size'] }}', {{ $item['price'] }}, {{ $item['id'] }})">
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

                <button class="cart-checkout-btn" type="button" onclick="document.getElementById('paymentModal').style.display='flex'">
                    Cobrar ${{ number_format($cartTotal, 0, '.', ',') }}
                </button>
            @endif

            <button type="button" onclick="document.getElementById('returnModal').style.display='flex'" style="width:100%;padding:12px;margin-top:12px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:var(--radius);color:var(--text-secondary);font-size:13px;font-weight:600;display:flex;align-items:center;justify-content:center;gap:8px;transition:all 0.2s;cursor:pointer;" onmouseover="this.style.borderColor='var(--orange)';this.style.color='var(--orange)'" onmouseout="this.style.borderColor='var(--border-color)';this.style.color='var(--text-secondary)'">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;"><polyline points="1 4 1 10 7 10"></polyline><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path></svg>
                Devoluci&oacute;n
            </button>
        </div>
    </div>
</div>

{{-- Modal Método de Pago --}}
<div id="paymentModal" class="modal-overlay" style="display:none;" onclick="if(event.target===this)this.style.display='none'">
    <div class="modal-content" style="max-width:480px;">
        <div class="modal-header">
            <h2>M&eacute;todo de Pago</h2>
            <button class="modal-close" onclick="document.getElementById('paymentModal').style.display='none'">&times;</button>
        </div>
        <form method="POST" action="{{ route('pos.checkout') }}" class="modal-body">
            @csrf
            <div class="payment-total">
                <span>Total a cobrar</span>
                <strong>${{ number_format($cartTotal, 0, '.', ',') }}</strong>
            </div>
            <div class="payment-methods">
                <input type="radio" name="payment_method" value="efectivo" id="pm-efectivo" class="pm-radio" required>
                <label for="pm-efectivo" class="payment-method-btn">
                    <div class="payment-method-icon cash">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><circle cx="12" cy="12" r="3"></circle><line x1="1" y1="4" x2="5" y2="8"></line><line x1="23" y1="4" x2="19" y2="8"></line><line x1="1" y1="20" x2="5" y2="16"></line><line x1="23" y1="20" x2="19" y2="16"></line></svg>
                    </div>
                    <span>Efectivo</span>
                </label>
                <input type="radio" name="payment_method" value="tarjeta" id="pm-tarjeta" class="pm-radio">
                <label for="pm-tarjeta" class="payment-method-btn">
                    <div class="payment-method-icon card">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
                    </div>
                    <span>Tarjeta</span>
                </label>
                <input type="radio" name="payment_method" value="transferencia" id="pm-transferencia" class="pm-radio">
                <label for="pm-transferencia" class="payment-method-btn">
                    <div class="payment-method-icon transfer">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                    </div>
                    <span>Transferencia</span>
                </label>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;padding:16px;font-size:17px;font-weight:700;margin-top:20px;display:flex;align-items:center;justify-content:center;gap:10px;border-radius:12px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:20px;height:20px;"><polyline points="20 6 9 17 4 12"></polyline></svg>
                Cobrar ${{ number_format($cartTotal, 0, '.', ',') }}
            </button>
        </form>
    </div>
</div>

{{-- Modal Ticket --}}
@if(session('sale'))
@php $sale = session('sale'); @endphp
<div id="ticketModal" class="modal-overlay" style="display:flex;z-index:1200;" onclick="if(event.target===this)this.style.display='none'">
    <div class="modal-content" style="max-width:400px;background:#fff;color:#000;">
        <div style="padding:24px;">
            <div id="ticketContent">
                <div style="text-align:center;border-bottom:2px dashed #ccc;padding-bottom:16px;margin-bottom:16px;">
                    <h2 style="font-size:20px;font-weight:800;margin:0;color:#000;">SNEAKERS COLIMA</h2>
                    <p style="font-size:12px;color:#666;margin:4px 0 0;">Sistema POS v2.0</p>
                    <p style="font-size:11px;color:#999;margin:2px 0 0;">Ticket de Venta</p>
                </div>

                <div style="display:flex;justify-content:space-between;font-size:12px;color:#666;margin-bottom:12px;">
                    <div>
                        <strong style="color:#000;">{{ $sale['order_number'] }}</strong><br>
                        {{ $sale['date'] }}
                    </div>
                    <div style="text-align:right;">
                        Atendi&oacute;:<br>
                        <strong style="color:#000;">{{ $sale['employee'] }}</strong>
                    </div>
                </div>

                <div style="border-bottom:1px dashed #ccc;padding-bottom:4px;margin-bottom:8px;">
                    <div style="display:flex;justify-content:space-between;font-size:11px;color:#999;font-weight:600;text-transform:uppercase;">
                        <span>Producto</span>
                        <span>Importe</span>
                    </div>
                </div>

                @foreach($sale['items'] as $item)
                <div style="display:flex;justify-content:space-between;font-size:13px;padding:6px 0;border-bottom:1px solid #eee;">
                    <div>
                        <strong>{{ $item['name'] }}</strong><br>
                        <span style="font-size:11px;color:#888;">Talla {{ $item['size'] }} &times; {{ $item['quantity'] }}</span>
                    </div>
                    <div style="font-weight:600;white-space:nowrap;">${{ number_format($item['price'] * $item['quantity'], 0, '.', ',') }}</div>
                </div>
                @endforeach

                <div style="border-top:2px dashed #ccc;margin-top:12px;padding-top:12px;">
                    <div style="display:flex;justify-content:space-between;font-size:13px;color:#666;margin-bottom:4px;">
                        <span>Subtotal</span>
                        <span>${{ number_format($sale['total'], 0, '.', ',') }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;font-size:20px;font-weight:800;">
                        <span>TOTAL</span>
                        <span>${{ number_format($sale['total'], 0, '.', ',') }}</span>
                    </div>
                </div>

                <div style="text-align:center;margin-top:12px;padding:8px;background:#f5f5f5;border-radius:8px;font-size:12px;color:#666;">
                    Pago: <strong style="color:#000;text-transform:capitalize;">{{ $sale['payment_method'] }}</strong>
                </div>

                <div style="text-align:center;margin-top:16px;padding-top:16px;border-top:2px dashed #ccc;">
                    <p style="font-size:13px;font-weight:600;color:#000;margin:0;">&iexcl;Gracias por tu compra!</p>
                    <p style="font-size:11px;color:#999;margin:4px 0 0;">Sneakers Colima &bull; Colima, M&eacute;xico</p>
                </div>
            </div>

            <div style="display:flex;gap:12px;margin-top:20px;">
                <button class="btn btn-secondary" style="flex:1;justify-content:center;color:#333;border-color:#ddd;" onclick="document.getElementById('ticketModal').style.display='none'">Cerrar</button>
                <button class="btn btn-primary" style="flex:1;justify-content:center;" onclick="printTicket()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                    Imprimir
                </button>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Modal Seleccionar Talla --}}
<div id="sizeModal" class="modal-overlay" style="display:none;" onclick="if(event.target===this)this.style.display='none'">
    <div class="modal-content" style="max-width:420px;">
        <div class="modal-header">
            <h2>Seleccionar Talla</h2>
            <button class="modal-close" onclick="document.getElementById('sizeModal').style.display='none'">&times;</button>
        </div>
        <form method="POST" action="{{ route('pos.cart.add') }}" class="modal-body" id="sizeForm">
            @csrf
            <input type="hidden" name="product_id" id="sizeProductId" value="">
            <div style="text-align:center;margin-bottom:4px;">
                <h3 id="sizeProductName" style="margin:0;font-size:18px;font-weight:700;"></h3>
                <span id="sizeProductPrice" style="color:var(--accent-gold);font-size:16px;font-weight:600;"></span>
            </div>
            <div id="sizeOptions" class="size-grid"></div>
            <button type="submit" class="btn btn-primary" style="width:100%;padding:14px;font-size:16px;font-weight:700;display:flex;align-items:center;justify-content:center;gap:8px;border-radius:12px;margin-top:8px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:18px;height:18px;"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                Agregar al carrito
            </button>
        </form>
    </div>
</div>

{{-- Modal Cámara QR --}}
<div id="qrCameraModal" class="modal-overlay" style="display:none;" onclick="if(event.target===this){stopQrCamera();this.style.display='none'}">
    <div class="modal-content" style="max-width:420px;">
        <div class="modal-header">
            <h2>Escanear C&oacute;digo QR</h2>
            <button class="modal-close" onclick="stopQrCamera();document.getElementById('qrCameraModal').style.display='none'">&times;</button>
        </div>
        <div class="modal-body" style="padding:16px;">
            <div id="qrReader" style="width:100%;border-radius:12px;overflow:hidden;"></div>
            <p style="text-align:center;font-size:13px;color:var(--text-secondary);margin:12px 0 0;">Apunta la c&aacute;mara al c&oacute;digo QR o de barras del producto</p>
        </div>
    </div>
</div>

{{-- Form oculto para enviar código escaneado --}}
<form method="POST" action="{{ route('pos.cart.qr') }}" id="qrForm" style="display:none;">
    @csrf
    <input type="hidden" name="code" id="qrCodeInput">
</form>

<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
var html5QrCode = null;

function startQrCamera() {
    if (html5QrCode) return;
    html5QrCode = new Html5Qrcode('qrReader');
    html5QrCode.start(
        { facingMode: 'environment' },
        { fps: 10, qrbox: { width: 250, height: 250 } },
        function(decodedText) {
            stopQrCamera();
            document.getElementById('qrCameraModal').style.display = 'none';
            document.getElementById('qrCodeInput').value = decodedText;
            document.getElementById('qrForm').submit();
        },
        function() {}
    );
}

function stopQrCamera() {
    if (html5QrCode) {
        html5QrCode.stop().catch(function() {});
        html5QrCode = null;
    }
}

// Iniciar cámara al abrir el modal
var qrModal = document.getElementById('qrCameraModal');
var observer = new MutationObserver(function() {
    if (qrModal.style.display === 'flex') startQrCamera();
});
observer.observe(qrModal, { attributes: true, attributeFilter: ['style'] });

// Lector físico: escucha pulsaciones rápidas que terminan en Enter
(function() {
    var buffer = '';
    var lastKeyTime = 0;

    document.addEventListener('keydown', function(e) {
        var active = document.activeElement;
        var isInput = active && (active.tagName === 'INPUT' || active.tagName === 'TEXTAREA' || active.tagName === 'SELECT');

        if (isInput && active.name === 'search') {
            return;
        }

        var now = Date.now();
        if (now - lastKeyTime > 500) buffer = '';
        lastKeyTime = now;

        if (e.key === 'Enter' && buffer.length >= 3) {
            e.preventDefault();
            document.getElementById('qrCodeInput').value = buffer;
            document.getElementById('qrForm').submit();
            buffer = '';
            return;
        }

        if (e.key.length === 1) {
            buffer += e.key;
        }
    });
})();
</script>

<script>
var _unitallaCats = ['gorra','gorras','accesorio','accesorios','perfume','perfumes','fragancia','fragancias','llavero','llaveros','calceta','calcetas','calcetines'];
var _ropaCats = ['ropa','playera','playeras','camiseta','camisetas','hoodie','hoodies','sudadera','sudaderas','pants','pantalon','pantalones','short','shorts','chamarra','chamarras'];

function openSizeModal(el) {
    var stock = Number(el.getAttribute('data-stock'));
    if (stock <= 0) return;

    var id = el.getAttribute('data-id');
    var name = el.getAttribute('data-name');
    var category = el.getAttribute('data-category');
    var price = Number(el.getAttribute('data-price'));
    var productSizes = JSON.parse(el.getAttribute('data-sizes') || '[]');

    var cat = category.toLowerCase();

    // Unitalla: agregar directo sin modal
    if (_unitallaCats.indexOf(cat) !== -1 && productSizes.length === 0) {
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("pos.cart.add") }}';
        form.innerHTML = '<input type="hidden" name="_token" value="{{ csrf_token() }}">' +
            '<input type="hidden" name="product_id" value="' + id + '">' +
            '<input type="hidden" name="size" value="Unitalla">';
        document.body.appendChild(form);
        form.submit();
        return;
    }

    document.getElementById('sizeProductId').value = id;
    document.getElementById('sizeProductName').textContent = name;
    document.getElementById('sizeProductPrice').textContent = '$' + price.toLocaleString();

    var container = document.getElementById('sizeOptions');
    container.innerHTML = '';

    var sizes = [];

    if (productSizes.length > 0) {
        sizes = productSizes;
    } else if (_ropaCats.indexOf(cat) !== -1) {
        sizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];
    } else if (_unitallaCats.indexOf(cat) !== -1) {
        sizes = ['Unitalla'];
    } else {
        sizes = ['22','22.5','23','23.5','24','24.5','25','25.5','26','26.5','27','27.5','28','28.5','29','29.5','30','30.5','31'];
    }

    for (var i = 0; i < sizes.length; i++) {
        var s = sizes[i];
        var radio = document.createElement('input');
        radio.type = 'radio';
        radio.name = 'size';
        radio.value = s;
        radio.id = 'size-' + s;
        radio.className = 'size-radio';
        radio.required = true;

        var label = document.createElement('label');
        label.htmlFor = 'size-' + s;
        label.className = 'size-option';
        label.textContent = s;

        container.appendChild(radio);
        container.appendChild(label);
    }

    if (sizes.length === 1) {
        container.querySelector('input').checked = true;
    }

    document.getElementById('sizeModal').style.display = 'flex';
}

// Timer de turno
(function() {
    var timerEl = document.getElementById('shiftTimer');
    if (!timerEl) return;
    var start = new Date(timerEl.getAttribute('data-start')).getTime();

    function updateTimer() {
        var now = Date.now();
        var diff = Math.max(0, Math.floor((now - start) / 1000));
        var h = Math.floor(diff / 3600);
        var m = Math.floor((diff % 3600) / 60);
        var s = Math.floor(diff % 60);
        timerEl.textContent = String(h).padStart(2, '0') + ':' + String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0') + ' hrs';
    }

    updateTimer();
    setInterval(updateTimer, 1000);
})();

function printTicket() {
    var el = document.getElementById('ticketContent');
    if (!el) return;
    var win = window.open('', '_blank', 'width=380,height=600');
    var css = 'body{font-family:Arial,sans-serif;margin:0;padding:20px;color:#000;}@media print{body{padding:0;}}';
    win.document.write('<html><head><title>Ticket<\/title><style>' + css + '<\/style><\/head><body>' + el.innerHTML + '<\/body><\/html>');
    win.document.close();
    win.focus();
    win.print();
}

function openCancelModal(index, name, size, price, productId) {
    document.getElementById('cancelIndex').value = index;
    document.getElementById('cancelProductId').value = productId;
    document.getElementById('cancelSize').value = size;
    document.getElementById('cancelPrice').value = price;
    document.getElementById('cancelProductName').textContent = name;
    document.getElementById('cancelProductSize').textContent = 'Talla ' + size;
    document.getElementById('cancelProductPrice').textContent = '$' + Number(price).toLocaleString();
    var radios = document.querySelectorAll('input[name="cancel_reason_option"]');
    radios.forEach(function(r) { r.checked = false; });
    document.getElementById('cancelReasonCustom').value = '';
    document.getElementById('cancelReasonCustom').style.display = 'none';
    document.getElementById('cancelModal').style.display = 'flex';
}

function toggleCustomReason(el) {
    var custom = document.getElementById('cancelReasonCustom');
    custom.style.display = el.value === 'otro' ? '' : 'none';
    if (el.value !== 'otro') custom.value = '';
}
</script>

{{-- Modal: Motivo de Cancelación --}}
<div id="cancelModal" class="modal-overlay" style="display:none;" onclick="if(event.target===this)this.style.display='none'">
    <div class="modal-content" style="max-width:440px;">
        <div class="modal-header">
            <h2>Cancelar Art&iacute;culo</h2>
            <button class="modal-close" onclick="document.getElementById('cancelModal').style.display='none'">&times;</button>
        </div>
        <form method="POST" action="{{ route('pos.cart.remove') }}" class="modal-body">
            @csrf
            <input type="hidden" name="index" id="cancelIndex">
            <input type="hidden" name="product_id" id="cancelProductId">
            <input type="hidden" name="size" id="cancelSize">
            <input type="hidden" name="price" id="cancelPrice">

            <div style="text-align:center;padding:12px;background:var(--bg-secondary);border-radius:var(--radius-lg);margin-bottom:4px;">
                <div style="font-size:16px;font-weight:700;" id="cancelProductName"></div>
                <div style="font-size:13px;color:var(--text-muted);" id="cancelProductSize"></div>
                <div style="font-size:20px;font-weight:800;color:var(--accent-gold);margin-top:4px;" id="cancelProductPrice"></div>
            </div>

            <div style="font-size:13px;font-weight:600;color:var(--text-secondary);margin-bottom:8px;">Motivo de cancelaci&oacute;n:</div>

            <label style="display:flex;align-items:center;gap:10px;padding:12px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:var(--radius);cursor:pointer;margin-bottom:6px;font-size:14px;transition:border-color 0.2s;" onmouseover="this.style.borderColor='var(--accent-gold)'" onmouseout="this.style.borderColor='var(--border-color)'">
                <input type="radio" name="cancel_reason_option" value="Cliente cambió de opinión" required onclick="toggleCustomReason(this)" style="accent-color:var(--accent-gold);">
                Cliente cambi&oacute; de opini&oacute;n
            </label>
            <label style="display:flex;align-items:center;gap:10px;padding:12px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:var(--radius);cursor:pointer;margin-bottom:6px;font-size:14px;transition:border-color 0.2s;" onmouseover="this.style.borderColor='var(--accent-gold)'" onmouseout="this.style.borderColor='var(--border-color)'">
                <input type="radio" name="cancel_reason_option" value="Talla incorrecta" onclick="toggleCustomReason(this)" style="accent-color:var(--accent-gold);">
                Talla incorrecta
            </label>
            <label style="display:flex;align-items:center;gap:10px;padding:12px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:var(--radius);cursor:pointer;margin-bottom:6px;font-size:14px;transition:border-color 0.2s;" onmouseover="this.style.borderColor='var(--accent-gold)'" onmouseout="this.style.borderColor='var(--border-color)'">
                <input type="radio" name="cancel_reason_option" value="Producto equivocado" onclick="toggleCustomReason(this)" style="accent-color:var(--accent-gold);">
                Producto equivocado
            </label>
            <label style="display:flex;align-items:center;gap:10px;padding:12px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:var(--radius);cursor:pointer;margin-bottom:6px;font-size:14px;transition:border-color 0.2s;" onmouseover="this.style.borderColor='var(--accent-gold)'" onmouseout="this.style.borderColor='var(--border-color)'">
                <input type="radio" name="cancel_reason_option" value="Precio incorrecto" onclick="toggleCustomReason(this)" style="accent-color:var(--accent-gold);">
                Precio incorrecto
            </label>
            <label style="display:flex;align-items:center;gap:10px;padding:12px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:var(--radius);cursor:pointer;margin-bottom:6px;font-size:14px;transition:border-color 0.2s;" onmouseover="this.style.borderColor='var(--accent-gold)'" onmouseout="this.style.borderColor='var(--border-color)'">
                <input type="radio" name="cancel_reason_option" value="otro" onclick="toggleCustomReason(this)" style="accent-color:var(--accent-gold);">
                Otro motivo
            </label>
            <textarea name="custom_reason" id="cancelReasonCustom" class="form-input" style="display:none;width:100%;min-height:60px;resize:vertical;" placeholder="Describe el motivo..."></textarea>

            <button type="submit" class="btn" style="width:100%;padding:14px;font-size:15px;font-weight:700;justify-content:center;border-radius:12px;background:var(--red);color:#fff;margin-top:8px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:18px;height:18px;"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                Confirmar Cancelaci&oacute;n
            </button>
        </form>
    </div>
</div>

{{-- Modal: Devolución - Paso 1: Seleccionar venta --}}
<div id="returnModal" class="modal-overlay" style="display:none;" onclick="if(event.target===this)this.style.display='none'">
    <div class="modal-content" style="max-width:540px;">
        <div class="modal-header">
            <h2>Devoluci&oacute;n de Producto</h2>
            <button class="modal-close" onclick="document.getElementById('returnModal').style.display='none'">&times;</button>
        </div>
        <div class="modal-body" style="padding:16px;">
            <div style="font-size:13px;color:var(--text-muted);margin-bottom:16px;">Selecciona la venta que el cliente quiere devolver:</div>

            <div style="max-height:400px;overflow-y:auto;display:flex;flex-direction:column;gap:8px;">
                @forelse($recentSales as $rs)
                    <div onclick="openReturnDetail({{ $rs->id }})" style="background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:var(--radius);padding:14px;cursor:pointer;transition:all 0.2s;" onmouseover="this.style.borderColor='var(--accent-gold)'" onmouseout="this.style.borderColor='var(--border-color)'">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                            <span style="font-weight:700;font-size:14px;">{{ $rs->order_number }}</span>
                            <span style="font-size:18px;font-weight:800;color:var(--accent-gold);">${{ number_format($rs->total, 0, '.', ',') }}</span>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:center;">
                            <div style="font-size:12px;color:var(--text-muted);">
                                {{ $rs->items->pluck('product.name')->filter()->take(2)->implode(', ') }}
                                @if($rs->items->count() > 2) +{{ $rs->items->count() - 2 }} m&aacute;s @endif
                            </div>
                            <div style="font-size:11px;color:var(--text-muted);">
                                @if($rs->created_at->isToday())
                                    Hoy {{ $rs->created_at->format('H:i') }}
                                @elseif($rs->created_at->isYesterday())
                                    Ayer {{ $rs->created_at->format('H:i') }}
                                @else
                                    {{ $rs->created_at->format('d/m H:i') }}
                                @endif
                                &middot; {{ $rs->employee?->name ?? 'Empleado' }}
                            </div>
                        </div>
                    </div>
                @empty
                    <div style="text-align:center;padding:30px;color:var(--text-muted);">No hay ventas recientes</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- Modal: Devolución - Paso 2: Seleccionar artículos (uno por venta, generado por JS) --}}
@foreach($recentSales as $rs)
<div id="returnDetail-{{ $rs->id }}" class="modal-overlay" style="display:none;" onclick="if(event.target===this)this.style.display='none'">
    <div class="modal-content" style="max-width:500px;">
        <div class="modal-header">
            <h2>Devolver &mdash; {{ $rs->order_number }}</h2>
            <button class="modal-close" onclick="document.getElementById('returnDetail-{{ $rs->id }}').style.display='none'">&times;</button>
        </div>
        <form method="POST" action="{{ route('pos.return.process') }}" class="modal-body">
            @csrf
            <input type="hidden" name="sale_id" value="{{ $rs->id }}">

            <div style="display:flex;justify-content:space-between;padding:12px;background:var(--bg-secondary);border-radius:var(--radius-lg);margin-bottom:12px;">
                <div>
                    <div style="font-weight:700;">{{ $rs->order_number }}</div>
                    <div style="font-size:12px;color:var(--text-muted);">{{ $rs->created_at->format('d/m/Y H:i') }} &middot; {{ $rs->employee?->name ?? 'Empleado' }}</div>
                </div>
                <div style="font-size:20px;font-weight:800;color:var(--accent-gold);">${{ number_format($rs->total, 0, '.', ',') }}</div>
            </div>

            <div style="font-size:13px;font-weight:600;color:var(--text-secondary);margin-bottom:8px;">Selecciona los art&iacute;culos a devolver:</div>

            @foreach($rs->items as $item)
                <label style="display:flex;align-items:center;gap:12px;padding:12px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:var(--radius);cursor:pointer;margin-bottom:6px;transition:border-color 0.2s;" onmouseover="this.style.borderColor='var(--accent-gold)'" onmouseout="this.style.borderColor='var(--border-color)'">
                    <input type="checkbox" name="return_items[]" value="{{ $item->id }}" style="accent-color:var(--accent-gold);width:18px;height:18px;">
                    <div style="flex:1;">
                        <div style="font-weight:600;font-size:14px;">{{ $item->product?->name ?? 'Producto' }}</div>
                        <div style="font-size:12px;color:var(--text-muted);">Talla {{ $item->size }} &middot; Cant: {{ $item->quantity }}</div>
                    </div>
                    <div style="font-weight:700;font-size:15px;color:var(--accent-gold);">${{ number_format($item->price * $item->quantity, 0, '.', ',') }}</div>
                </label>
            @endforeach

            <div class="form-group" style="margin-top:8px;">
                <label style="font-size:12px;font-weight:600;color:var(--text-muted);">Motivo de devoluci&oacute;n</label>
                <select name="return_reason" class="form-input" required>
                    <option value="">Seleccionar motivo...</option>
                    <option value="Producto defectuoso">Producto defectuoso</option>
                    <option value="Talla incorrecta">Talla incorrecta</option>
                    <option value="Cliente insatisfecho">Cliente insatisfecho</option>
                    <option value="Error en la venta">Error en la venta</option>
                    <option value="Otro">Otro</option>
                </select>
            </div>

            <button type="submit" class="btn" style="width:100%;padding:14px;font-size:15px;font-weight:700;justify-content:center;border-radius:12px;background:var(--orange);color:#fff;margin-top:12px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:18px;height:18px;"><polyline points="1 4 1 10 7 10"></polyline><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path></svg>
                Procesar Devoluci&oacute;n
            </button>
        </form>
    </div>
</div>
@endforeach

<script>
function openReturnDetail(saleId) {
    document.getElementById('returnModal').style.display = 'none';
    document.getElementById('returnDetail-' + saleId).style.display = 'flex';
}
</script>
@endsection
