@extends('layouts.pos')

@section('title', 'Alertas - Sneakers Colima')

@section('content')
<div>
    {{-- Header --}}
    <div class="page-header">
        <div class="page-header-left">
            <h1>Centro de Alertas</h1>
            <div class="subtitle">{{ $totalAlerts }} alertas activas &middot; Monitoreo en tiempo real</div>
        </div>
        <div class="page-header-right">
            <a href="{{ route('pos.alerts') }}" class="btn btn-secondary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;">
                    <polyline points="23 4 23 10 17 10"></polyline>
                    <polyline points="1 20 1 14 7 14"></polyline>
                    <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
                </svg>
                Actualizar
            </a>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-card-icon" style="background:var(--red-dim);color:var(--red);">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                    <line x1="12" y1="9" x2="12" y2="13"></line>
                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                </svg>
            </div>
            <h3>{{ $countByLevel['alta'] }}</h3>
            <div class="kpi-label">Prioridad Alta</div>
            <div class="kpi-change {{ $countByLevel['alta'] > 0 ? 'down' : 'up' }}">
                {{ $countByLevel['alta'] > 0 ? '⚠ Requiere atención' : '✓ Sin alertas críticas' }}
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-card-icon" style="background:var(--orange-dim);color:var(--orange);">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
            </div>
            <h3>{{ $countByLevel['media'] }}</h3>
            <div class="kpi-label">Prioridad Media</div>
        </div>

        <div class="kpi-card">
            <div class="kpi-card-icon" style="background:var(--blue-dim);color:var(--blue);">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="16" x2="12" y2="12"></line>
                    <line x1="12" y1="8" x2="12.01" y2="8"></line>
                </svg>
            </div>
            <h3>{{ $countByLevel['info'] }}</h3>
            <div class="kpi-label">Informativas</div>
        </div>

        <div class="kpi-card">
            <div class="kpi-card-icon" style="background:var(--accent-gold-dim);color:var(--accent-gold);">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                </svg>
            </div>
            <h3>{{ $totalAlerts }}</h3>
            <div class="kpi-label">Total Alertas</div>
        </div>
    </div>

    {{-- Category Filters --}}
    <div class="filter-tabs" style="margin-bottom:24px;">
        <button class="filter-tab active" onclick="filterAlerts('todas')">Todas ({{ $totalAlerts }})</button>
        @if($countByType['seguridad'] > 0)
            <button class="filter-tab" onclick="filterAlerts('seguridad')">
                Seguridad ({{ $countByType['seguridad'] }})
            </button>
        @endif
        @if($countByType['inventario'] > 0)
            <button class="filter-tab" onclick="filterAlerts('inventario')">
                Inventario ({{ $countByType['inventario'] }})
            </button>
        @endif
        @if($countByType['ventas'] > 0)
            <button class="filter-tab" onclick="filterAlerts('ventas')">
                Ventas ({{ $countByType['ventas'] }})
            </button>
        @endif
        @if($countByType['equipo'] > 0)
            <button class="filter-tab" onclick="filterAlerts('equipo')">
                Equipo ({{ $countByType['equipo'] }})
            </button>
        @endif
    </div>

    {{-- Alert List --}}
    @if($totalAlerts === 0)
        <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:var(--radius-lg);padding:60px 40px;text-align:center;">
            <div style="width:64px;height:64px;border-radius:50%;background:var(--green-dim);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:32px;height:32px;"><polyline points="20 6 9 17 4 12"></polyline></svg>
            </div>
            <h3 style="font-size:18px;font-weight:700;margin-bottom:8px;">Todo en orden</h3>
            <p style="color:var(--text-muted);font-size:14px;">No hay alertas activas. El sistema est&aacute; funcionando correctamente.</p>
        </div>
    @else
        <div style="display:flex;flex-direction:column;gap:12px;">
            @foreach($alerts as $alert)
                @php
                    $levelColors = [
                        'alta' => ['border' => 'var(--red)', 'bg' => 'var(--red-dim)', 'text' => 'var(--red)', 'label' => 'ALTA'],
                        'media' => ['border' => 'var(--orange)', 'bg' => 'var(--orange-dim)', 'text' => 'var(--orange)', 'label' => 'MEDIA'],
                        'info' => ['border' => 'var(--blue)', 'bg' => 'var(--blue-dim)', 'text' => 'var(--blue)', 'label' => 'INFO'],
                    ];
                    $lc = $levelColors[$alert['level']];

                    $typeIcons = [
                        'seguridad' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:20px;height:20px;"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>',
                        'inventario' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:20px;height:20px;"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path></svg>',
                        'ventas' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:20px;height:20px;"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg>',
                        'equipo' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:20px;height:20px;"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle></svg>',
                    ];
                    $typeLabels = ['seguridad' => 'Seguridad', 'inventario' => 'Inventario', 'ventas' => 'Ventas', 'equipo' => 'Equipo'];
                @endphp
                <div class="alert-item" data-type="{{ $alert['type'] }}" style="background:var(--bg-card);border:1px solid var(--border-color);border-left:4px solid {{ $lc['border'] }};border-radius:var(--radius-lg);padding:20px;display:flex;align-items:flex-start;gap:16px;">
                    {{-- Icon --}}
                    <div style="width:44px;height:44px;border-radius:var(--radius);background:{{ $lc['bg'] }};color:{{ $lc['text'] }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        {!! $typeIcons[$alert['type']] !!}
                    </div>

                    {{-- Content --}}
                    <div style="flex:1;min-width:0;">
                        <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px;">
                            <h3 style="font-size:15px;font-weight:600;margin:0;">{{ $alert['title'] }}</h3>
                            <span style="font-size:10px;font-weight:700;padding:3px 8px;border-radius:4px;background:{{ $lc['bg'] }};color:{{ $lc['text'] }};text-transform:uppercase;letter-spacing:0.5px;">{{ $lc['label'] }}</span>
                            <span style="font-size:10px;font-weight:600;padding:3px 8px;border-radius:4px;background:var(--bg-secondary);color:var(--text-muted);text-transform:uppercase;">{{ $typeLabels[$alert['type']] }}</span>
                        </div>
                        <p style="font-size:13px;color:var(--text-secondary);margin:0;line-height:1.5;">{{ $alert['message'] }}</p>
                    </div>

                    {{-- Right side: amount + time --}}
                    <div style="text-align:right;flex-shrink:0;">
                        @if($alert['amount'])
                            <div style="font-size:16px;font-weight:700;color:{{ $alert['level'] === 'info' ? 'var(--green)' : $lc['text'] }};margin-bottom:4px;">${{ number_format($alert['amount'], 0, '.', ',') }}</div>
                        @endif
                        <div style="font-size:12px;color:var(--text-muted);white-space:nowrap;">
                            @if($alert['time']->isToday())
                                Hoy, {{ $alert['time']->format('H:i') }}
                            @elseif($alert['time']->isYesterday())
                                Ayer, {{ $alert['time']->format('H:i') }}
                            @else
                                {{ $alert['time']->translatedFormat('d M, H:i') }}
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<script>
function filterAlerts(type) {
    var tabs = document.querySelectorAll('.filter-tab');
    tabs.forEach(function(t) { t.classList.remove('active'); });
    event.target.classList.add('active');

    var items = document.querySelectorAll('.alert-item');
    items.forEach(function(item) {
        if (type === 'todas') {
            item.style.display = '';
        } else {
            item.style.display = item.getAttribute('data-type') === type ? '' : 'none';
        }
    });
}
</script>
@endsection
