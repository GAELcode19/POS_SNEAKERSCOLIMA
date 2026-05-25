<?php

use App\Models\Sale;
use App\Models\CartCancellation;
use Livewire\Volt\Component;

new class extends Component {
    public string $chartMode = 'hora';

    public function with(): array
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

        $hourlyData = [];
        for ($h = 9; $h <= 18; $h++) {
            $hourlyData[$h] = Sale::whereDate('created_at', $today)
                ->whereRaw('EXTRACT(HOUR FROM created_at) = ?', [$h])
                ->where('status', '!=', 'cancelada')
                ->sum('total');
        }

        return [
            'salesToday' => $salesToday,
            'salesChange' => $salesChange,
            'onlineOrders' => $onlineOrders,
            'clientsToday' => $clientsToday,
            'clientsYesterday' => $clientsYesterday,
            'avgTicket' => $avgTicket,
            'ticketChange' => $ticketChange,
            'onlineOrdersList' => $onlineOrdersList,
            'cancellations' => $cancellations,
            'hourlyData' => $hourlyData,
        ];
    }
}; ?>

<x-layouts.pos>
    <div>
        {{-- Header --}}
        <div class="page-header">
            <div class="page-header-left">
                <h1>Dashboard Gerencial</h1>
                <div class="subtitle">{{ now()->translatedFormat('l, d \\d\\e F \\d\\e Y') }} &mdash; Vista en tiempo real</div>
            </div>
            <div class="page-header-right">
                <button class="btn btn-secondary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;">
                        <polyline points="23 4 23 10 17 10"></polyline>
                        <polyline points="1 20 1 14 7 14"></polyline>
                        <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
                    </svg>
                    Actualizar
                </button>
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
                <div class="kpi-change up">↗ +3 nuevas</div>
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
                        <h2>Ventas por Hora</h2>
                        <div class="subtitle">Hoy, {{ now()->translatedFormat('d \\d\\e F') }}</div>
                    </div>
                    <div class="chart-toggle">
                        <button class="{{ $chartMode === 'hora' ? 'active' : '' }}" wire:click="$set('chartMode', 'hora')">Por Hora</button>
                        <button class="{{ $chartMode === 'semanal' ? 'active' : '' }}" wire:click="$set('chartMode', 'semanal')">Semanal</button>
                    </div>
                </div>
                <div class="chart-area">
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
                        {{-- Grid lines --}}
                        @for($g = 0; $g
                        < 5; $g++)
                            <line x1="40" y1="{{ 30 + $g * 42.5 }}" x2="740" y2="{{ 30 + $g * 42.5 }}" stroke="#2a2a2a" stroke-width="1" stroke-dasharray="4" />
                        @endfor
                        {{-- Y axis labels --}}
                        @for($g = 0; $g < 5; $g++)
                            <text x="30" y="{{ 34 + $g * 42.5 }}" fill="#6b6b6b" font-size="10" text-anchor="end">${{ number_format($maxVal - ($g * $maxVal / 4), 0, '.', ',') }}</text>
                            @endfor
                            {{-- X axis labels --}}
                            @php $xi = 0; @endphp
                            @foreach($hourlyData as $hour => $val)
                            <text x="{{ ($xi / max($totalHours - 1, 1)) * 700 + 40 }}" y="225" fill="#6b6b6b" font-size="10" text-anchor="middle">{{ sprintf('%02d:00', $hour) }}</text>
                            @php $xi++; @endphp
                            @endforeach
                            {{-- Area fill --}}
                            <polygon points="{{ $areaPoints }}" fill="url(#goldGrad)" opacity="0.2" />
                            {{-- Line --}}
                            <polyline points="{{ $pointsStr }}" fill="none" stroke="#c8a84e" stroke-width="2.5" stroke-linejoin="round" stroke-linecap="round" />
                            <defs>
                                <linearGradient id="goldGrad" x1="0" y1="0" x2="0" y2="1">
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
</x-layouts.pos>