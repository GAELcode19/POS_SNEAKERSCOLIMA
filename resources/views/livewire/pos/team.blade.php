<?php

use App\Models\User;
use App\Models\Shift;
use App\Models\CartCancellation;
use App\Models\Sale;
use Livewire\Volt\Component;

new class extends Component {
    public string $search = '';
    public string $statusFilter = 'todos';
    public ?int $selectedEmployee = null;

    public function selectEmployee(int $id): void
    {
        $this->selectedEmployee = $id;
    }

    public function mount(): void
    {
        $first = User::where('role', '!=', 'gerencia')->first();
        if ($first) {
            $this->selectedEmployee = $first->id;
        }
    }

    public function with(): array
    {
        $query = User::where('role', '!=', 'gerencia');

        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        if ($this->statusFilter !== 'todos') {
            $query->where('status', $this->statusFilter);
        }

        $employees = $query->get();

        $weekStart = now()->startOfWeek();
        $totalHoursWeek = Shift::where('started_at', '>=', $weekStart)->sum('hours_logged');
        $totalSalesWeek = Sale::where('created_at', '>=', $weekStart)->where('status', '!=', 'cancelada')->sum('total');
        $activeCount = User::where('role', '!=', 'gerencia')->where('status', 'activo')->count();

        $selected = null;
        $selectedShifts = [];
        $selectedCancellations = 0;
        $selectedCancellationsNoReason = 0;
        $selectedSales = 0;
        $selectedWeeklyHours = 0;
        $selectedDailyHours = [];
        $selectedSalesTotal = 0;

        if ($this->selectedEmployee) {
            $selected = User::find($this->selectedEmployee);
            if ($selected) {
                $selectedWeeklyHours = Shift::where('employee_id', $selected->id)
                    ->where('started_at', '>=', $weekStart)
                    ->sum('hours_logged');

                $selectedSales = Sale::where('employee_id', $selected->id)
                    ->where('status', '!=', 'cancelada')
                    ->count();

                $selectedSalesTotal = Sale::where('employee_id', $selected->id)
                    ->where('status', '!=', 'cancelada')
                    ->sum('total');

                $selectedCancellations = CartCancellation::where('employee_id', $selected->id)
                    ->where('created_at', '>=', $weekStart)
                    ->count();

                $selectedCancellationsNoReason = CartCancellation::where('employee_id', $selected->id)
                    ->where('created_at', '>=', $weekStart)
                    ->whereNull('reason')
                    ->count();

                $days = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];
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
            }
        }

        $hoursPercent = $selected && $selected->weekly_hours_target > 0
            ? round(($selectedWeeklyHours / $selected->weekly_hours_target) * 100)
            : 0;

        $riskLevel = 'Bajo';
        if ($selectedCancellationsNoReason >= 2) $riskLevel = 'Alto';
        elseif ($selectedCancellations >= 2) $riskLevel = 'Medio';

        return [
            'employees' => $employees,
            'totalHoursWeek' => $totalHoursWeek,
            'totalSalesWeek' => $totalSalesWeek,
            'activeCount' => $activeCount,
            'selected' => $selected,
            'selectedWeeklyHours' => $selectedWeeklyHours,
            'selectedSales' => $selectedSales,
            'selectedSalesTotal' => $selectedSalesTotal,
            'selectedCancellations' => $selectedCancellations,
            'selectedCancellationsNoReason' => $selectedCancellationsNoReason,
            'selectedDailyHours' => $selectedDailyHours,
            'hoursPercent' => $hoursPercent,
            'riskLevel' => $riskLevel,
        ];
    }
}; ?>

<x-layouts.pos>
    <div>
        <div class="team-section">
            {{-- Left Panel: Employee List --}}
            <div class="team-list-panel">
                <div class="team-list-header">
                    <h2>Equipo de Trabajo</h2>
                    <span class="team-active-badge">{{ $activeCount }} activos</span>
                </div>

                <div class="team-stats">
                    <div class="team-stat">
                        <span>Hrs esta semana</span>
                        <h3>{{ number_format($totalHoursWeek, 0) }}h</h3>
                    </div>
                    <div class="team-stat">
                        <span>Ventas totales</span>
                        <h3>${{ $totalSalesWeek >= 1000 ? number_format($totalSalesWeek / 1000, 0) . 'k' : number_format($totalSalesWeek, 0) }}</h3>
                    </div>
                </div>

                <div class="team-search">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar empleado...">
                </div>

                <div class="team-filters">
                    @foreach(['todos' => 'Todos', 'activo' => 'Activos', 'descanso' => 'Descanso', 'desconectado' => 'Offline'] as $key => $label)
                        <button class="team-filter {{ $statusFilter === $key ? 'active' : '' }}" wire:click="$set('statusFilter', '{{ $key }}')">{{ $label }}</button>
                    @endforeach
                </div>

                <div class="employee-list">
                    @foreach($employees as $emp)
                        @php
                            $empHours = \App\Models\Shift::where('employee_id', $emp->id)
                                ->where('started_at', '>=', now()->startOfWeek())
                                ->sum('hours_logged');
                            $empPercent = $emp->weekly_hours_target > 0 ? min(100, ($empHours / $emp->weekly_hours_target) * 100) : 0;
                            $barColor = $empPercent >= 90 ? 'green' : ($empPercent >= 60 ? 'yellow' : 'red');
                        @endphp
                        <div class="employee-card {{ $selectedEmployee === $emp->id ? 'active' : '' }}" wire:click="selectEmployee({{ $emp->id }})">
                            <div class="employee-card-top">
                                <div class="employee-avatar {{ $emp->statusColor() }}">
                                    {{ $emp->initials() }}
                                    <span class="employee-status-dot {{ $emp->statusColor() }}"></span>
                                </div>
                                <div class="employee-card-info">
                                    <h4>{{ $emp->name }}</h4>
                                    <span>{{ $emp->roleLabel() }}</span>
                                </div>
                                <span class="employee-card-status {{ $emp->statusColor() }}">{{ $emp->statusLabel() }}</span>
                            </div>
                            <div class="employee-hours">
                                <span>Hrs esta semana</span>
                                <div class="employee-hours-bar">
                                    <div class="employee-hours-fill {{ $barColor }}" style="width: {{ $empPercent }}%"></div>
                                </div>
                                <span>{{ number_format($empHours, 1) }}h / {{ number_format($emp->weekly_hours_target, 0) }}h</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Right Panel: Employee Detail --}}
            @if($selected)
                <div class="employee-detail">
                    <div class="employee-detail-header">
                        <div class="employee-detail-profile">
                            <div class="employee-detail-avatar">{{ $selected->initials() }}</div>
                            <div class="employee-detail-info">
                                <h2>{{ $selected->name }}</h2>
                                <div class="role">{{ $selected->roleLabel() }} &middot; Sucursal Principal</div>
                                <div class="employee-detail-badges">
                                    <span class="status-badge {{ $selected->statusColor() }}">
                                        ✓ {{ $selected->statusLabel() }}
                                    </span>
                                    <span class="date-badge">Desde {{ $selected->hired_at?->translatedFormat('M Y') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="employee-detail-actions">
                            <button class="btn-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                            </button>
                            <button class="btn-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;"><circle cx="12" cy="12" r="1"></circle><circle cx="19" cy="12" r="1"></circle><circle cx="5" cy="12" r="1"></circle></svg>
                            </button>
                        </div>
                    </div>

                    {{-- Stats --}}
                    <div class="employee-detail-stats">
                        <div class="detail-stat">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                            <h3>Hoy, {{ now()->format('H:i') }} AM</h3>
                            <span>&Uacute;ltima conexi&oacute;n</span>
                        </div>
                        <div class="detail-stat">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg>
                            <h3>${{ number_format($selectedSalesTotal, 0, '.', ',') }}</h3>
                            <span>Ventas semana</span>
                        </div>
                        <div class="detail-stat">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
                            <h3>{{ $selectedSales }} ventas</h3>
                            <span>Transacciones</span>
                        </div>
                        <div class="detail-stat">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                            <h3>{{ $selectedCancellations }} registros</h3>
                            <span>Cancelaciones</span>
                        </div>
                    </div>

                    {{-- Semaforo de Actividad --}}
                    <div class="semaforo-section">
                        <div class="semaforo-header">
                            <h3>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:18px;height:18px;color:var(--accent-gold);"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                                Sem&aacute;foro de Actividad
                            </h3>
                            <span style="font-size:12px;color:var(--text-muted);">Semana actual</span>
                        </div>

                        <div class="semaforo-legend">
                            <div class="semaforo-legend-item">
                                <span class="semaforo-legend-dot" style="background:var(--green);"></span>
                                <strong>Optimo</strong> &ge;90% hrs
                            </div>
                            <div class="semaforo-legend-item">
                                <span class="semaforo-legend-dot" style="background:var(--yellow);"></span>
                                <strong>Aceptable</strong> 60-89%
                            </div>
                            <div class="semaforo-legend-item">
                                <span class="semaforo-legend-dot" style="background:var(--red);"></span>
                                <strong>Bajo</strong> &lt;60% hrs
                            </div>
                            <div style="margin-left:auto;">
                                @php
                                    $semaforoColor = $hoursPercent >= 90 ? 'green' : ($hoursPercent >= 60 ? 'yellow' : 'red');
                                    $semaforoLabel = $hoursPercent >= 90 ? 'Optimo' : ($hoursPercent >= 60 ? 'Aceptable' : 'Bajo');
                                @endphp
                                <span class="semaforo-result {{ $semaforoColor }}">
                                    <span class="semaforo-legend-dot" style="background:var(--{{ $semaforoColor }});"></span>
                                    {{ $semaforoLabel }} &mdash; {{ $hoursPercent }}%
                                </span>
                            </div>
                        </div>

                        <div style="font-size:13px;color:var(--text-muted);margin-bottom:12px;">Horas por d&iacute;a esta semana</div>

                        <div class="weekly-chart">
                            @foreach($selectedDailyHours as $day)
                                @php $dayPercent = $day['hours'] > 0 ? min(100, ($day['hours'] / 12) * 100) : 0; @endphp
                                <div class="weekly-chart-day">
                                    <div class="weekly-chart-bar">
                                        <div class="weekly-chart-fill" style="height: {{ $dayPercent }}%"></div>
                                    </div>
                                    <span class="weekly-chart-label">{{ $day['label'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Security Profile --}}
                    <div class="security-section">
                        <div class="security-header">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:20px;height:20px;"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                            Perfil de Seguridad
                        </div>
                        <div class="security-grid">
                            <div class="security-stat">
                                <h3>{{ $selectedCancellations }}</h3>
                                <span>Cancelaciones</span>
                                <span style="display:block;font-size:11px;color:var(--text-muted);">esta semana</span>
                            </div>
                            <div class="security-stat">
                                <h3>{{ $selectedCancellationsNoReason }}</h3>
                                <span>Sin motivo</span>
                                <span style="display:block;font-size:11px;color:var(--text-muted);">sin justificar</span>
                            </div>
                            <div class="security-stat high">
                                <h3>{{ $riskLevel }}</h3>
                                <span>Nivel de riesgo</span>
                                <span style="display:block;font-size:11px;color:var(--text-muted);">monitoreo</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-layouts.pos>
