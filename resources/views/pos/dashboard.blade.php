@extends('layouts.pos')

@section('title', 'Dashboard - Sneakers Colima')

@section('content')
<div>
    {{-- Header --}}
    <div class="page-header">
        <div class="page-header-left">
            <h1>Dashboard Gerencial</h1>
            <div class="subtitle">{{ now()->translatedFormat('l, d \\d\\e F \\d\\e Y') }} &mdash; Vista en tiempo real</div>
        </div>
        <div class="page-header-right">
            <a href="{{ route('pos.dashboard') }}" class="btn btn-secondary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;">
                    <polyline points="23 4 23 10 17 10"></polyline>
                    <polyline points="1 20 1 14 7 14"></polyline>
                    <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
                </svg>
                Actualizar
            </a>
            <button class="btn btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;">
                    <line x1="18" y1="20" x2="18" y2="10"></line>
                    <line x1="12" y1="20" x2="12" y2="4"></line>
                    <line x1="6" y1="20" x2="6" y2="14"></line>
                </svg>
                Exportar reporte
            </button>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-card-icon sales">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
                    <polyline points="17 6 23 6 23 12"></polyline>
                </svg>
            </div>
            <h3>${{ number_format($salesToday, 0, '.', ',') }}</h3>
            <div class="kpi-label">Ventas del D&iacute;a</div>
            <div class="kpi-change {{ $salesChange >= 0 ? 'up' : 'down' }}">
                {{ $salesChange >= 0 ? '↗' : '↘' }} {{ $salesChange >= 0 ? '+' : '' }}{{ $salesChange }}%
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-card-icon orders">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="2" y1="12" x2="22" y2="12"></line>
                    <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                </svg>
            </div>
            <h3>{{ $onlineOrders }}</h3>
            <div class="kpi-label">&Oacute;rdenes en L&iacute;nea</div>
            <div class="kpi-change {{ $onlineOrders >= $onlineOrdersYesterday ? 'up' : 'down' }}">
                {{ $onlineOrders >= $onlineOrdersYesterday ? '↗' : '↘' }} vs {{ $onlineOrdersYesterday }} ayer
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-card-icon clients">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
            </div>
            <h3>{{ $clientsToday }}</h3>
            <div class="kpi-label">Clientes Atendidos</div>
            <div class="kpi-change up">↗ vs {{ $clientsYesterday }} ayer</div>
        </div>

        <div class="kpi-card">
            <div class="kpi-card-icon ticket">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 12 20 22 4 22 4 12"></polyline>
                    <rect x="2" y="7" width="20" height="5"></rect>
                    <line x1="12" y1="22" x2="12" y2="7"></line>
                    <path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"></path>
                    <path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"></path>
                </svg>
            </div>
            <h3>${{ number_format($avgTicket, 0, '.', ',') }}</h3>
            <div class="kpi-label">Ticket Promedio</div>
            <div class="kpi-change {{ $ticketChange >= 0 ? 'up' : 'down' }}">
                {{ $ticketChange >= 0 ? '↗' : '↘' }} {{ $ticketChange >= 0 ? '+' : '' }}{{ $ticketChange }}%
            </div>
        </div>
    </div>

    {{-- Chart + Orders --}}
    <div class="dashboard-grid">
        <div class="chart-card">
            <div class="chart-header">
                <div>
                    <h2 id="chartTitle">Ventas por Hora</h2>
                    <div class="subtitle" id="chartSubtitle">Hoy, {{ now()->translatedFormat('d \\d\\e F') }}</div>
                </div>
                <div class="chart-toggle" id="chartToggle">
                    <button class="active" data-mode="hora">Por Hora</button>
                    <button data-mode="semanal">Semanal</button>
                </div>
            </div>
            <div class="chart-area" id="chartHourly">
                @php
                $maxVal = max(array_values($hourlyData)) ?: 1;
                $points = [];
                $i = 0;
                $totalHours = count($hourlyData);
                foreach ($hourlyData as $hour => $val) {
                    $x = ($i / max($totalHours - 1, 1)) * 700 + 40;
                    $y = 200 - ($val / $maxVal) * 170;
                    $points[] = "$x,$y";
                    $i++;
                }
                $pointsStr = implode(' ', $points);
                $areaPoints = $pointsStr . " 740,200 40,200";
                @endphp
                <svg viewBox="0 0 780 240" preserveAspectRatio="none">
                    @for($g = 0; $g < 5; $g++)
                        <line x1="40" y1="{{ 30 + $g * 42.5 }}" x2="740" y2="{{ 30 + $g * 42.5 }}" stroke="#2a2a2a" stroke-width="1" stroke-dasharray="4" />
                    @endfor
                    @for($g = 0; $g < 5; $g++)
                        <text x="30" y="{{ 34 + $g * 42.5 }}" fill="#6b6b6b" font-size="10" text-anchor="end">${{ number_format($maxVal - ($g * $maxVal / 4), 0, '.', ',') }}</text>
                    @endfor
                    @php $xi = 0; @endphp
                    @foreach($hourlyData as $hour => $val)
                        <text x="{{ ($xi / max($totalHours - 1, 1)) * 700 + 40 }}" y="225" fill="#6b6b6b" font-size="10" text-anchor="middle">{{ sprintf('%02d:00', $hour) }}</text>
                        @php $xi++; @endphp
                    @endforeach
                    <polygon points="{{ $areaPoints }}" fill="url(#goldGrad)" opacity="0.2" />
                    <polyline points="{{ $pointsStr }}" fill="none" stroke="#c8a84e" stroke-width="2.5" stroke-linejoin="round" stroke-linecap="round" />
                    <defs>
                        <linearGradient id="goldGrad" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#c8a84e" stop-opacity="0.4" />
                            <stop offset="100%" stop-color="#c8a84e" stop-opacity="0" />
                        </linearGradient>
                    </defs>
                </svg>
            </div>
            <div class="chart-area" id="chartWeekly" style="display:none;">
                @php
                $wMaxVal = max(array_values($weeklyData)) ?: 1;
                $wPoints = [];
                $wi = 0;
                $totalDays = count($weeklyData);
                foreach ($weeklyData as $day => $val) {
                    $x = ($wi / max($totalDays - 1, 1)) * 700 + 40;
                    $y = 200 - ($val / $wMaxVal) * 170;
                    $wPoints[] = "$x,$y";
                    $wi++;
                }
                $wPointsStr = implode(' ', $wPoints);
                $wAreaPoints = $wPointsStr . " 740,200 40,200";
                @endphp
                <svg viewBox="0 0 780 240" preserveAspectRatio="none">
                    @for($g = 0; $g < 5; $g++)
                        <line x1="40" y1="{{ 30 + $g * 42.5 }}" x2="740" y2="{{ 30 + $g * 42.5 }}" stroke="#2a2a2a" stroke-width="1" stroke-dasharray="4" />
                    @endfor
                    @for($g = 0; $g < 5; $g++)
                        <text x="30" y="{{ 34 + $g * 42.5 }}" fill="#6b6b6b" font-size="10" text-anchor="end">${{ number_format($wMaxVal - ($g * $wMaxVal / 4), 0, '.', ',') }}</text>
                    @endfor
                    @php $wxi = 0; @endphp
                    @foreach($weeklyData as $day => $val)
                        <text x="{{ ($wxi / max($totalDays - 1, 1)) * 700 + 40 }}" y="225" fill="#6b6b6b" font-size="10" text-anchor="middle">{{ $day }}</text>
                        @php $wxi++; @endphp
                    @endforeach
                    <polygon points="{{ $wAreaPoints }}" fill="url(#goldGrad2)" opacity="0.2" />
                    <polyline points="{{ $wPointsStr }}" fill="none" stroke="#c8a84e" stroke-width="2.5" stroke-linejoin="round" stroke-linecap="round" />
                    <defs>
                        <linearGradient id="goldGrad2" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#c8a84e" stop-opacity="0.4" />
                            <stop offset="100%" stop-color="#c8a84e" stop-opacity="0" />
                        </linearGradient>
                    </defs>
                </svg>
            </div>
        </div>

        <div class="orders-panel">
            <div class="orders-header">
                <h2>&Oacute;rdenes en L&iacute;nea</h2>
                <span class="orders-count">{{ $onlineOrders }} total</span>
            </div>

            @foreach($onlineOrdersList->take(3) as $order)
            <div class="order-item">
                <div class="order-item-header">
                    <span class="order-number">#{{ $order->order_number }}</span>
                    <span class="order-status {{ $order->statusColor() }}">{{ $order->statusLabel() }}</span>
                </div>
                <h3>{{ $order->items->first()?->product?->name ?? 'Producto' }}</h3>
                <div class="order-item-footer">
                    <span>{{ $order->customer_name ?? 'Cliente' }}</span>
                    <span class="order-item-price">${{ number_format($order->total, 0, '.', ',') }}</span>
                </div>
            </div>
            @endforeach

            <a href="#" class="orders-view-all">Ver todas las &oacute;rdenes &rsaquo;</a>
        </div>
    </div>

    {{-- Ventas del Día + Productos Top + Métodos de Pago --}}
    <div class="dashboard-grid" style="margin-bottom:24px;">
        {{-- Ventas del Día --}}
        <div class="chart-card" style="overflow:hidden;">
            <div class="chart-header">
                <div>
                    <h2>Ventas del D&iacute;a</h2>
                    <div class="subtitle">{{ $todaySales->count() }} transacciones &middot; Hoy</div>
                </div>
            </div>
            <div style="max-height:360px;overflow-y:auto;">
                @if($todaySales->count() > 0)
                    <table style="width:100%;border-collapse:collapse;">
                        <thead>
                            <tr style="border-bottom:1px solid var(--border-color);">
                                <th style="padding:10px 16px;text-align:left;font-size:11px;color:var(--text-muted);font-weight:600;text-transform:uppercase;">Orden</th>
                                <th style="padding:10px 8px;text-align:left;font-size:11px;color:var(--text-muted);font-weight:600;text-transform:uppercase;">Productos</th>
                                <th style="padding:10px 8px;text-align:left;font-size:11px;color:var(--text-muted);font-weight:600;text-transform:uppercase;">Pago</th>
                                <th style="padding:10px 8px;text-align:left;font-size:11px;color:var(--text-muted);font-weight:600;text-transform:uppercase;">Hora</th>
                                <th style="padding:10px 16px;text-align:right;font-size:11px;color:var(--text-muted);font-weight:600;text-transform:uppercase;">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($todaySales as $sale)
                            <tr style="border-bottom:1px solid rgba(255,255,255,0.04);">
                                <td style="padding:10px 16px;">
                                    <span style="font-weight:600;font-size:13px;">{{ $sale->order_number }}</span>
                                    <div style="font-size:11px;color:var(--text-muted);">{{ $sale->employee?->name }}</div>
                                </td>
                                <td style="padding:10px 8px;font-size:13px;">
                                    @foreach($sale->items->take(2) as $item)
                                        <div>{{ $item->product?->name ?? 'Producto' }} <span style="color:var(--text-muted);">&times;{{ $item->quantity }}</span></div>
                                    @endforeach
                                    @if($sale->items->count() > 2)
                                        <span style="font-size:11px;color:var(--text-muted);">+{{ $sale->items->count() - 2 }} m&aacute;s</span>
                                    @endif
                                </td>
                                <td style="padding:10px 8px;">
                                    <span style="display:inline-block;padding:3px 10px;border-radius:6px;font-size:11px;font-weight:600;text-transform:capitalize;
                                        background:{{ $sale->payment_method === 'efectivo' ? 'var(--green-dim)' : ($sale->payment_method === 'tarjeta' ? 'var(--blue-dim)' : 'rgba(168,85,247,0.15)') }};
                                        color:{{ $sale->payment_method === 'efectivo' ? 'var(--green)' : ($sale->payment_method === 'tarjeta' ? 'var(--blue)' : 'var(--purple)') }};">
                                        {{ $sale->payment_method }}
                                    </span>
                                </td>
                                <td style="padding:10px 8px;font-size:13px;color:var(--text-secondary);">{{ $sale->created_at->format('H:i') }}</td>
                                <td style="padding:10px 16px;text-align:right;font-weight:700;font-size:14px;">${{ number_format($sale->total, 0, '.', ',') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div style="padding:40px;text-align:center;color:var(--text-muted);">
                        <p>No hay ventas hoy</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Panel derecho: Top Productos + Métodos de Pago --}}
        <div style="display:flex;flex-direction:column;gap:16px;">
            {{-- Top Productos --}}
            <div class="orders-panel" style="flex:1;">
                <div class="orders-header">
                    <h2>Top Productos</h2>
                    <span class="orders-count">Hoy</span>
                </div>
                @if($topProducts->count() > 0)
                    @foreach($topProducts as $tp)
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 0;border-bottom:1px solid rgba(255,255,255,0.04);">
                        <div>
                            <div style="font-weight:600;font-size:13px;">{{ $tp->name }}</div>
                            <div style="font-size:11px;color:var(--text-muted);">{{ $tp->brand }} &middot; {{ $tp->total_qty }} vendidos</div>
                        </div>
                        <div style="font-weight:700;color:var(--accent-gold);">${{ number_format($tp->total_revenue, 0, '.', ',') }}</div>
                    </div>
                    @endforeach
                @else
                    <div style="padding:20px;text-align:center;color:var(--text-muted);font-size:13px;">Sin ventas hoy</div>
                @endif
            </div>

            {{-- Métodos de Pago --}}
            <div class="orders-panel">
                <div class="orders-header">
                    <h2>M&eacute;todos de Pago</h2>
                    <span class="orders-count">Hoy</span>
                </div>
                @php
                    $methods = ['efectivo' => ['color' => 'var(--green)', 'bg' => 'var(--green-dim)'], 'tarjeta' => ['color' => 'var(--blue)', 'bg' => 'var(--blue-dim)'], 'transferencia' => ['color' => 'var(--purple)', 'bg' => 'rgba(168,85,247,0.15)']];
                    $totalSalesCount = $paymentMethods->sum('count') ?: 1;
                @endphp
                @foreach($methods as $method => $style)
                    @php $pm = $paymentMethods->get($method); @endphp
                    <div style="padding:8px 0;">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">
                            <span style="font-size:13px;font-weight:600;text-transform:capitalize;color:{{ $style['color'] }};">{{ $method }}</span>
                            <span style="font-size:13px;font-weight:700;">{{ $pm ? $pm->count : 0 }} ventas &middot; ${{ number_format($pm ? $pm->total : 0, 0, '.', ',') }}</span>
                        </div>
                        <div style="height:6px;background:var(--bg-secondary);border-radius:3px;overflow:hidden;">
                            <div style="height:100%;width:{{ $pm ? round(($pm->count / $totalSalesCount) * 100) : 0 }}%;background:{{ $style['color'] }};border-radius:3px;"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Cancellation History --}}
    <div class="cancellation-section">
        <div class="cancellation-header">
            <div class="cancellation-header-left">
                <div class="icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:20px;height:20px;">
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                        <line x1="12" y1="9" x2="12" y2="13"></line>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                </div>
                <div>
                    <h2>Historial de Cancelaciones</h2>
                    <div class="subtitle">M&oacute;dulo de seguridad anti-fraude &middot; Monitoreo de art&iacute;culos eliminados</div>
                </div>
            </div>
            <div class="cancellation-header-right">
                <span class="alert-badge">{{ $cancellations->count() }} alertas activas</span>
                <button class="btn btn-outline">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;">
                        <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                    </svg>
                    Filtrar
                </button>
            </div>
        </div>

        <table class="cancel-table">
            <thead>
                <tr>
                    <th>Empleado</th>
                    <th>Art&iacute;culo eliminado</th>
                    <th>Talla</th>
                    <th>Precio</th>
                    <th>Hora</th>
                    <th>Fecha</th>
                    <th>Motivo declarado</th>
                    <th>Alerta</th>
                </tr>
            </thead>
            <tbody>
                @foreach($cancellations as $cancel)
                <tr>
                    <td>{{ $cancel->employee?->name ?? 'Empleado no encontrado' }}</td>
                    <td>{{ $cancel->product?->name ?? 'Producto no encontrado' }}</td>
                    <td>{{ $cancel->size }}</td>
                    <td>${{ number_format($cancel->price, 0, '.', ',') }}</td>
                    <td>{{ $cancel->created_at->format('H:i') }} hrs</td>
                    <td>{{ $cancel->created_at->format('d/m/Y') }}</td>
                    <td>{{ $cancel->reason ?? 'Sin motivo' }}</td>
                    <td>
                        <div class="cancel-table alert-cell">
                            <span class="alert-dot" style="background: {{ $cancel->alertColor() }}; display:inline-block;"></span>
                            {{ ucfirst($cancel->alert_level) }}
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('chartToggle').addEventListener('click', function(e) {
    if (e.target.tagName === 'BUTTON') {
        this.querySelectorAll('button').forEach(b => b.classList.remove('active'));
        e.target.classList.add('active');
        var mode = e.target.getAttribute('data-mode');
        document.getElementById('chartHourly').style.display = mode === 'hora' ? '' : 'none';
        document.getElementById('chartWeekly').style.display = mode === 'semanal' ? '' : 'none';
        document.getElementById('chartTitle').textContent = mode === 'hora' ? 'Ventas por Hora' : 'Ventas Semanal';
        document.getElementById('chartSubtitle').textContent = mode === 'hora' ? 'Hoy, {{ now()->translatedFormat("d \\\\d\\\\e F") }}' : 'Últimos 7 días';
    }
});
</script>
@endpush
