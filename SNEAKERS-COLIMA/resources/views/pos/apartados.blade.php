@extends('layouts.pos')

@section('title', 'Apartados - Sneakers Colima')

@section('content')
<div>
    @if(session('success'))
        <div style="padding:14px 20px;background:var(--green-dim);border:1px solid rgba(34,197,94,0.3);border-radius:var(--radius-lg);color:var(--green);font-size:14px;font-weight:500;margin-bottom:16px;display:flex;align-items:center;gap:10px;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:20px;height:20px;flex-shrink:0;"><polyline points="20 6 9 17 4 12"></polyline></svg>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div style="padding:14px 20px;background:var(--red-dim);border:1px solid rgba(239,68,68,0.3);border-radius:var(--radius-lg);color:var(--red);font-size:14px;font-weight:500;margin-bottom:16px;display:flex;align-items:center;gap:10px;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:20px;height:20px;flex-shrink:0;"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
            {{ session('error') }}
        </div>
    @endif

    <div class="inventory-header">
        <div class="page-header-left">
            <h1>Apartados</h1>
            <div class="subtitle">Anticipos, abonos y saldos a favor &middot; 30 d&iacute;as para liquidar</div>
        </div>
        <div class="page-header-right">
            <button class="btn btn-primary" onclick="document.getElementById('createLayawayModal').style.display='flex'">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                Nuevo Apartado
            </button>
        </div>
    </div>

    {{-- Stats --}}
    <div class="inventory-stats">
        <div class="kpi-card">
            <div class="kpi-card-icon sales">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path><line x1="7" y1="7" x2="7.01" y2="7"></line></svg>
            </div>
            <h3>{{ $stats['activos'] }}</h3>
            <p>Apartados activos</p>
        </div>
        <div class="kpi-card">
            <div class="kpi-card-icon" style="background:var(--blue-dim);color:var(--blue);">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
            </div>
            <h3>${{ number_format($stats['porCobrar'], 0, '.', ',') }}</h3>
            <p>Por cobrar (activos)</p>
        </div>
        <div class="kpi-card">
            <div class="kpi-card-icon" style="background:rgba(245,158,11,0.15);color:#f59e0b;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
            </div>
            <h3>{{ $stats['conSaldo'] }}</h3>
            <p>Con saldo a favor vigente</p>
        </div>
        <div class="kpi-card">
            <div class="kpi-card-icon" style="background:rgba(16,185,129,0.15);color:#10b981;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
            </div>
            <h3>${{ number_format($stats['saldoTotal'], 0, '.', ',') }}</h3>
            <p>Saldo a favor acumulado</p>
        </div>
    </div>

    {{-- Filtros --}}
    <div style="display:flex;gap:8px;align-items:stretch;margin:20px 0 16px;flex-wrap:wrap;">
        <form method="GET" action="{{ route('pos.apartados') }}" class="search-bar" style="flex:1;min-width:240px;margin-bottom:0;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:18px;height:18px;color:var(--text-muted);"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            <input type="text" name="search" value="{{ $search }}" placeholder="Buscar folio, cliente, tel&eacute;fono o producto..." style="border:none;background:transparent;outline:none;color:var(--text-primary);width:100%;font-size:14px;">
            <input type="hidden" name="status" value="{{ $statusFilter }}">
        </form>
        @foreach(['activo' => 'Activos', 'vencido' => 'Con saldo', 'liquidado' => 'Liquidados', 'reclamado' => 'Saldo usado', 'todos' => 'Todos'] as $key => $label)
            <a href="{{ route('pos.apartados', ['status' => $key, 'search' => $search ?: null]) }}"
               class="brand-chip {{ $statusFilter === $key ? 'active' : '' }}"
               style="text-decoration:none;padding:0 16px;display:flex;align-items:center;border-radius:var(--radius);border:1px solid var(--border-color);font-size:13px;font-weight:500;{{ $statusFilter === $key ? 'background:var(--accent-gold);color:#111;border-color:var(--accent-gold);' : 'background:var(--bg-card);color:var(--text-secondary);' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    {{-- Tabla --}}
    <div class="inventory-table-wrap" style="overflow-x:auto;background:var(--bg-card);border:1px solid var(--border-color);border-radius:var(--radius-lg);">
        <table style="width:100%;border-collapse:collapse;font-size:13px;min-width:900px;">
            <thead>
                <tr style="text-align:left;color:var(--text-muted);border-bottom:1px solid var(--border-color);">
                    <th style="padding:14px 16px;">Folio</th>
                    <th style="padding:14px 16px;">Producto</th>
                    <th style="padding:14px 16px;">Cliente</th>
                    <th style="padding:14px 16px;">Total</th>
                    <th style="padding:14px 16px;">Pagado</th>
                    <th style="padding:14px 16px;">Restante</th>
                    <th style="padding:14px 16px;">Vence / Saldo hasta</th>
                    <th style="padding:14px 16px;">Estado</th>
                    <th style="padding:14px 16px;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($layaways as $l)
                    <tr style="border-bottom:1px solid var(--border-color);">
                        <td style="padding:12px 16px;font-family:monospace;font-weight:600;color:var(--accent-gold);">{{ $l->folio }}</td>
                        <td style="padding:12px 16px;">
                            <div style="font-weight:600;color:var(--text-primary);">{{ $l->product_name }}</div>
                            @if($l->size)<div style="color:var(--text-muted);font-size:12px;">Talla {{ $l->size }}</div>@endif
                        </td>
                        <td style="padding:12px 16px;color:var(--text-secondary);">
                            {{ $l->customer_name ?: '—' }}
                            @if($l->customer_phone)<div style="color:var(--text-muted);font-size:12px;">{{ $l->customer_phone }}</div>@endif
                        </td>
                        <td style="padding:12px 16px;color:var(--text-secondary);">${{ number_format($l->total_price, 0, '.', ',') }}</td>
                        <td style="padding:12px 16px;color:#10b981;font-weight:600;">${{ number_format($l->paid_amount, 0, '.', ',') }}</td>
                        <td style="padding:12px 16px;font-weight:600;color:{{ $l->remaining() > 0 ? 'var(--text-primary)' : 'var(--text-muted)' }};">
                            ${{ number_format($l->remaining(), 0, '.', ',') }}
                        </td>
                        <td style="padding:12px 16px;color:var(--text-secondary);font-size:12px;">
                            @if($l->status === 'activo')
                                Liquidar antes: <strong>{{ $l->due_at->format('d/m/Y') }}</strong>
                                <div style="color:{{ $l->due_at->isPast() ? 'var(--red)' : 'var(--text-muted)' }};">{{ $l->due_at->diffForHumans() }}</div>
                            @elseif($l->status === 'vencido')
                                Saldo hasta: <strong>{{ $l->credit_expires_at->format('d/m/Y') }}</strong>
                                <div style="color:var(--text-muted);">{{ $l->credit_expires_at->diffForHumans() }}</div>
                            @else
                                —
                            @endif
                        </td>
                        <td style="padding:12px 16px;">
                            @php $c = $l->statusColor(); @endphp
                            <span class="stock-indicator" style="background:var(--{{ $c }}-dim, rgba(148,163,184,0.15));color:var(--{{ $c }}, #94a3b8);">{{ $l->statusLabel() }}</span>
                        </td>
                        <td style="padding:12px 16px;">
                            <div style="display:flex;gap:6px;flex-wrap:wrap;">
                                @if($l->status === 'activo')
                                    <button class="btn-edit-product" style="background:var(--green-dim);color:var(--green);"
                                        onclick="openPaymentModal('{{ $l->id }}', '{{ $l->folio }}', {{ $l->remaining() }})">
                                        Abonar
                                    </button>
                                @endif
                                <a href="{{ route('pos.apartados.folio', $l) }}" target="_blank" class="btn-edit-product" style="background:var(--bg-hover, rgba(148,163,184,0.12));color:var(--text-secondary);text-decoration:none;">
                                    Folio
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" style="padding:40px;text-align:center;color:var(--text-muted);">No hay apartados en esta categor&iacute;a.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modal Nuevo Apartado --}}
<div id="createLayawayModal" class="modal-overlay" style="display:none;">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Nuevo Apartado</h2>
            <button class="modal-close" onclick="document.getElementById('createLayawayModal').style.display='none'">&times;</button>
        </div>
        <form method="POST" action="{{ route('pos.apartados.store') }}">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label>Producto a apartar</label>
                    <select name="product_id" id="layaway_product" class="form-input" required onchange="onLayawayProductChange()">
                        <option value="">Selecciona un producto...</option>
                        @foreach($products as $p)
                            <option value="{{ $p->id }}"
                                data-price="{{ $p->price }}"
                                data-sizes='@json($p->sizes->map(fn($s) => ["size" => $s->size, "stock" => $s->stock])->values())'>
                                {{ $p->name }} — ${{ number_format($p->price, 0, '.', ',') }} ({{ $p->stock }} disp.)
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Talla</label>
                        <select name="size" id="layaway_size" class="form-input">
                            <option value="">Unitalla / sin talla</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Precio del producto</label>
                        <input type="text" id="layaway_price_display" class="form-input" value="—" readonly style="background:var(--bg-hover, rgba(148,163,184,0.08));">
                    </div>
                </div>
                <div class="form-group">
                    <label>Anticipo que deja el cliente</label>
                    <input type="number" name="deposit" id="layaway_deposit" class="form-input" min="1" step="0.01" required oninput="updateRemainingHint()">
                    <small id="layaway_remaining_hint" style="color:var(--text-muted);font-size:12px;margin-top:4px;">Restar&iacute;a por liquidar: —</small>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Nombre del cliente (opcional)</label>
                        <input type="text" name="customer_name" class="form-input" maxlength="255">
                    </div>
                    <div class="form-group">
                        <label>Tel&eacute;fono (opcional)</label>
                        <input type="text" name="customer_phone" class="form-input" maxlength="30">
                    </div>
                </div>
                <p style="font-size:12px;color:var(--text-muted);margin:4px 0 0;">Se reservar&aacute; 1 unidad del stock. El cliente tiene 30 d&iacute;as para liquidar. Al crear se genera un folio imprimible.</p>
            </div>
            <div class="modal-footer" style="padding:16px 20px;display:flex;gap:10px;justify-content:flex-end;border-top:1px solid var(--border-color);">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('createLayawayModal').style.display='none'">Cancelar</button>
                <button type="submit" class="btn btn-primary">Crear apartado</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Abonar --}}
<div id="paymentModal" class="modal-overlay" style="display:none;">
    <div class="modal-content" style="max-width:420px;">
        <div class="modal-header">
            <h2>Registrar abono</h2>
            <button class="modal-close" onclick="document.getElementById('paymentModal').style.display='none'">&times;</button>
        </div>
        <form method="POST" id="paymentForm">
            @csrf
            <div class="modal-body">
                <p style="color:var(--text-secondary);font-size:14px;margin:0 0 12px;">Folio <strong id="paymentFolio" style="color:var(--accent-gold);font-family:monospace;"></strong></p>
                <div class="form-group">
                    <label>Monto del abono</label>
                    <input type="number" name="amount" id="paymentAmount" class="form-input" min="1" step="0.01" required>
                    <small id="paymentRemainingHint" style="color:var(--text-muted);font-size:12px;margin-top:4px;"></small>
                </div>
                <p style="font-size:12px;color:var(--text-muted);margin:4px 0 0;">Si el abono completa el total, el apartado se liquida y se registra como venta autom&aacute;ticamente.</p>
            </div>
            <div class="modal-footer" style="padding:16px 20px;display:flex;gap:10px;justify-content:flex-end;border-top:1px solid var(--border-color);">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('paymentModal').style.display='none'">Cancelar</button>
                <button type="submit" class="btn btn-primary">Registrar abono</button>
            </div>
        </form>
    </div>
</div>

@php $printId = request()->query('folio_print'); @endphp
@if($printId)
<script>
    window.addEventListener('load', function () {
        window.open('{{ url('pos/apartados/' . $printId . '/folio') }}', '_blank');
    });
</script>
@endif

<script>
function onLayawayProductChange() {
    var sel = document.getElementById('layaway_product');
    var opt = sel.options[sel.selectedIndex];
    var price = opt.getAttribute('data-price');
    var sizeSel = document.getElementById('layaway_size');
    sizeSel.innerHTML = '<option value="">Unitalla / sin talla</option>';

    if (price) {
        document.getElementById('layaway_price_display').value = '$' + Number(price).toLocaleString('es-MX');
        try {
            var sizes = JSON.parse(opt.getAttribute('data-sizes') || '[]');
            sizes.forEach(function (s) {
                if (s.stock > 0) {
                    var o = document.createElement('option');
                    o.value = s.size;
                    o.textContent = 'Talla ' + s.size + ' (' + s.stock + ' disp.)';
                    sizeSel.appendChild(o);
                }
            });
        } catch (e) {}
    } else {
        document.getElementById('layaway_price_display').value = '—';
    }
    updateRemainingHint();
}

function updateRemainingHint() {
    var sel = document.getElementById('layaway_product');
    var opt = sel.options[sel.selectedIndex];
    var price = Number(opt.getAttribute('data-price') || 0);
    var deposit = Number(document.getElementById('layaway_deposit').value || 0);
    var hint = document.getElementById('layaway_remaining_hint');
    if (price && deposit) {
        var rem = Math.max(0, price - deposit);
        hint.textContent = rem > 0
            ? 'Restaría por liquidar: $' + rem.toLocaleString('es-MX')
            : 'El anticipo cubre el total: se liquida de inmediato.';
    } else {
        hint.textContent = 'Restaría por liquidar: —';
    }
}

function openPaymentModal(id, folio, remaining) {
    var form = document.getElementById('paymentForm');
    form.action = '{{ url('pos/apartados') }}/' + id + '/payment';
    document.getElementById('paymentFolio').textContent = folio;
    var input = document.getElementById('paymentAmount');
    input.value = remaining;
    input.max = remaining;
    document.getElementById('paymentRemainingHint').textContent = 'Restante del apartado: $' + Number(remaining).toLocaleString('es-MX');
    document.getElementById('paymentModal').style.display = 'flex';
}

// Cerrar modales al hacer click fuera
document.querySelectorAll('.modal-overlay').forEach(function (m) {
    m.addEventListener('click', function (e) { if (e.target === m) m.style.display = 'none'; });
});
</script>
@endsection
