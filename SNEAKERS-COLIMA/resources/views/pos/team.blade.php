@extends('layouts.pos')

@section('title', 'Equipo - Sneakers Colima')

@section('content')
<div>
    <div class="team-section">
        {{-- Left Panel: Employee List --}}
        <div class="team-list-panel">
            <div class="team-list-header">
                <h2>Equipo de Trabajo</h2>
                <div style="display:flex;align-items:center;gap:8px;">
                    <span class="team-active-badge">{{ $activeCount }} activos</span>
                    <button type="button" onclick="document.getElementById('createEmployeeModal').style.display='flex'" class="btn btn-primary" style="padding:6px 14px;font-size:12px;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        Nuevo
                    </button>
                </div>
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

            <form method="GET" action="{{ route('pos.team') }}" class="team-search">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                <input type="text" name="search" value="{{ $search }}" placeholder="Buscar empleado...">
                @if($statusFilter !== 'todos')
                    <input type="hidden" name="status" value="{{ $statusFilter }}">
                @endif
                @if($selectedEmployeeId)
                    <input type="hidden" name="employee" value="{{ $selectedEmployeeId }}">
                @endif
            </form>

            <div class="team-filters">
                @foreach(['todos' => 'Todos', 'activo' => 'Activos', 'descanso' => 'Descanso', 'desconectado' => 'Offline'] as $key => $label)
                    <a
                        href="{{ route('pos.team', ['status' => $key, 'search' => $search ?: null, 'employee' => $selectedEmployeeId]) }}"
                        class="team-filter {{ $statusFilter === $key ? 'active' : '' }}"
                    >{{ $label }}</a>
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
                    <a
                        href="{{ route('pos.team', ['employee' => $emp->id, 'search' => $search ?: null, 'status' => $statusFilter]) }}"
                        class="employee-card {{ $selectedEmployeeId == $emp->id ? 'active' : '' }}"
                        style="text-decoration:none;color:inherit;"
                    >
                        <div class="employee-card-top">
                            <div class="employee-avatar {{ $emp->statusColor() }}" style="{{ $emp->avatar ? 'padding:0;' : '' }}">
                                @if($emp->avatar)
                                    <img src="{{ asset('storage/' . $emp->avatar) }}" alt="" style="width:100%;height:100%;border-radius:50%;object-fit:cover;">
                                @else
                                    {{ $emp->initials() }}
                                @endif
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
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Right Panel: Employee Detail --}}
        @if($selected)
            <div class="employee-detail">
                <div class="employee-detail-header">
                    <div class="employee-detail-profile">
                        <div class="employee-detail-avatar" style="{{ $selected->avatar ? 'padding:0;background:none;' : '' }}">
                            @if($selected->avatar)
                                <img src="{{ asset('storage/' . $selected->avatar) }}" alt="" style="width:100%;height:100%;border-radius:50%;object-fit:cover;">
                            @else
                                {{ $selected->initials() }}
                            @endif
                        </div>
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
                        <button class="btn-icon" type="button" onclick="openEditEmployee()" title="Editar empleado">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                        </button>
                        <button class="btn-icon" type="button" onclick="document.getElementById('deleteEmployeeModal').style.display='flex'" title="Eliminar empleado" style="color:var(--red);">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                        </button>
                    </div>
                </div>

                {{-- Stats --}}
                <div class="employee-detail-stats">
                    <div class="detail-stat">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        <h3>{{ $lastConnection ? ($lastConnection->isToday() ? 'Hoy, ' . $lastConnection->format('h:i A') : $lastConnection->translatedFormat('d M, h:i A')) : 'Sin registro' }}</h3>
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
                        <span>Transacciones semana</span>
                    </div>
                    <div class="detail-stat">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                        <h3>{{ $selectedCancellations }} registros</h3>
                        <span>Cancelaciones</span>
                    </div>
                </div>

                {{-- Resumen Hoy --}}
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:24px;">
                    <div style="background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:var(--radius);padding:16px;">
                        <div style="font-size:11px;color:var(--text-muted);margin-bottom:4px;">Ventas hoy</div>
                        <div style="font-size:20px;font-weight:700;color:var(--accent-gold);">{{ $selectedTodaySales }}</div>
                    </div>
                    <div style="background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:var(--radius);padding:16px;">
                        <div style="font-size:11px;color:var(--text-muted);margin-bottom:4px;">Ingresos hoy</div>
                        <div style="font-size:20px;font-weight:700;color:var(--green);">${{ number_format($selectedTodaySalesTotal, 0, '.', ',') }}</div>
                    </div>
                    <div style="background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:var(--radius);padding:16px;">
                        <div style="font-size:11px;color:var(--text-muted);margin-bottom:4px;">Ticket promedio</div>
                        <div style="font-size:20px;font-weight:700;">${{ number_format($selectedAvgTicket, 0, '.', ',') }}</div>
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

                {{-- Ventas Recientes del Empleado --}}
                @if($selectedRecentSales->count() > 0)
                <div style="background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:var(--radius-lg);padding:20px;margin-bottom:24px;">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
                        <h3 style="font-size:16px;font-weight:600;display:flex;align-items:center;gap:8px;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:18px;height:18px;color:var(--green);"><polyline points="20 12 20 22 4 22 4 12"></polyline><rect x="2" y="7" width="20" height="5"></rect><line x1="12" y1="22" x2="12" y2="7"></line></svg>
                            Ventas Recientes
                        </h3>
                        <span style="font-size:12px;color:var(--text-muted);">Esta semana</span>
                    </div>
                    @foreach($selectedRecentSales as $sale)
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 0;border-bottom:1px solid rgba(255,255,255,0.04);">
                        <div>
                            <div style="font-weight:600;font-size:13px;">{{ $sale->order_number }}</div>
                            <div style="font-size:11px;color:var(--text-muted);">
                                {{ $sale->items->pluck('product.name')->filter()->implode(', ') ?: 'Productos' }}
                            </div>
                        </div>
                        <div style="text-align:right;">
                            <div style="font-weight:700;color:var(--accent-gold);">${{ number_format($sale->total, 0, '.', ',') }}</div>
                            <div style="font-size:11px;color:var(--text-muted);">{{ $sale->created_at->translatedFormat('d M H:i') }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif

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

{{-- Modal: Crear Empleado --}}
<div id="createEmployeeModal" class="modal-overlay" style="display:none;" onclick="if(event.target===this)this.style.display='none'">
    <div class="modal-content" style="max-width:520px;">
        <div class="modal-header">
            <h2>Nuevo Empleado</h2>
            <button class="modal-close" onclick="document.getElementById('createEmployeeModal').style.display='none'">&times;</button>
        </div>
        <form method="POST" action="{{ route('pos.team.store') }}" class="modal-body">
            @csrf
            <div class="form-group">
                <label>Nombre completo</label>
                <input type="text" name="name" class="form-input" placeholder="Ej: Juan P&eacute;rez" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Correo electr&oacute;nico</label>
                    <input type="email" name="email" class="form-input" placeholder="correo@ejemplo.com" required>
                </div>
                <div class="form-group">
                    <label>Contrase&ntilde;a</label>
                    <input type="password" name="password" class="form-input" placeholder="M&iacute;nimo 6 caracteres" required minlength="6">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Rol</label>
                    <select name="role" class="form-input" required>
                        <option value="asesor_ventas">Asesor de Ventas</option>
                        <option value="cajero">Cajero</option>
                        <option value="supervisor">Supervisor</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Horas semanales</label>
                    <input type="number" name="weekly_hours_target" class="form-input" value="40" min="0" max="168" step="0.5">
                </div>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;padding:14px;font-size:15px;font-weight:700;justify-content:center;border-radius:12px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:18px;height:18px;"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="23" y1="11" x2="17" y2="11"></line></svg>
                Crear Empleado
            </button>
        </form>
    </div>
</div>

{{-- Modal: Editar Empleado --}}
@if($selected)
<div id="editEmployeeModal" class="modal-overlay" style="display:none;" onclick="if(event.target===this)this.style.display='none'">
    <div class="modal-content" style="max-width:520px;">
        <div class="modal-header">
            <h2>Editar Empleado</h2>
            <button class="modal-close" onclick="document.getElementById('editEmployeeModal').style.display='none'">&times;</button>
        </div>
        <form method="POST" action="{{ route('pos.team.update', $selected->id) }}" class="modal-body">
            @csrf
            @method('PUT')

            <div style="display:flex;align-items:center;gap:16px;margin-bottom:16px;padding:14px;background:var(--bg-secondary);border-radius:var(--radius-lg);">
                <label style="cursor:pointer;position:relative;" title="Cambiar foto">
                    @if($selected->avatar)
                        <img src="{{ asset('storage/' . $selected->avatar) }}" alt="" style="width:56px;height:56px;border-radius:50%;object-fit:cover;">
                    @else
                        <div style="width:56px;height:56px;border-radius:50%;background:var(--accent-gold);color:#000;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:20px;">{{ $selected->initials() }}</div>
                    @endif
                    <div style="position:absolute;bottom:-2px;right:-2px;background:var(--bg-card);border:2px solid var(--border-color);border-radius:50%;width:22px;height:22px;display:flex;align-items:center;justify-content:center;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:12px;height:12px;"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle></svg>
                    </div>
                    <input type="file" accept="image/*" style="display:none;" onchange="document.getElementById('avatarForm-{{ $selected->id }}').querySelector('input[type=file]').files = this.files; document.getElementById('avatarForm-{{ $selected->id }}').submit();">
                </label>
                <div>
                    <div style="font-weight:700;font-size:16px;">{{ $selected->name }}</div>
                    <div style="font-size:13px;color:var(--text-muted);">Click en la foto para cambiarla</div>
                </div>
            </div>

            <div class="form-group">
                <label>Nombre completo</label>
                <input type="text" name="name" class="form-input" value="{{ $selected->name }}" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Correo electr&oacute;nico</label>
                    <input type="email" name="email" class="form-input" value="{{ $selected->email }}" required>
                </div>
                <div class="form-group">
                    <label>Nueva contrase&ntilde;a <span style="color:var(--text-muted);font-weight:400;text-transform:none;">(dejar vac&iacute;o para no cambiar)</span></label>
                    <input type="password" name="password" class="form-input" placeholder="Sin cambios" minlength="6">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Rol</label>
                    <select name="role" class="form-input" required>
                        <option value="asesor_ventas" {{ $selected->role === 'asesor_ventas' ? 'selected' : '' }}>Asesor de Ventas</option>
                        <option value="cajero" {{ $selected->role === 'cajero' ? 'selected' : '' }}>Cajero</option>
                        <option value="supervisor" {{ $selected->role === 'supervisor' ? 'selected' : '' }}>Supervisor</option>
                        <option value="gerencia" {{ $selected->role === 'gerencia' ? 'selected' : '' }}>Gerencia</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Estado</label>
                    <select name="status" class="form-input" required>
                        <option value="activo" {{ $selected->status === 'activo' ? 'selected' : '' }}>Activo</option>
                        <option value="descanso" {{ $selected->status === 'descanso' ? 'selected' : '' }}>En descanso</option>
                        <option value="desconectado" {{ $selected->status === 'desconectado' ? 'selected' : '' }}>Desconectado</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Horas semanales objetivo</label>
                <input type="number" name="weekly_hours_target" class="form-input" value="{{ $selected->weekly_hours_target }}" min="0" max="168" step="0.5">
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;padding:14px;font-size:15px;font-weight:700;justify-content:center;border-radius:12px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:18px;height:18px;"><polyline points="20 6 9 17 4 12"></polyline></svg>
                Guardar Cambios
            </button>
        </form>
    </div>
</div>

{{-- Form oculto para avatar --}}
<form id="avatarForm-{{ $selected->id }}" method="POST" action="{{ route('pos.team.avatar', $selected->id) }}" enctype="multipart/form-data" style="display:none;">
    @csrf
    <input type="file" name="avatar">
</form>

{{-- Modal: Eliminar Empleado --}}
<div id="deleteEmployeeModal" class="modal-overlay" style="display:none;" onclick="if(event.target===this)this.style.display='none'">
    <div class="modal-content" style="max-width:420px;">
        <div class="modal-header">
            <h2 style="color:var(--red);">Eliminar Empleado</h2>
            <button class="modal-close" onclick="document.getElementById('deleteEmployeeModal').style.display='none'">&times;</button>
        </div>
        <div class="modal-body" style="text-align:center;">
            <div style="width:64px;height:64px;border-radius:50%;background:var(--red-dim);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="var(--red)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:32px;height:32px;"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
            </div>
            <h3 style="font-size:18px;font-weight:700;margin-bottom:8px;">&iquest;Eliminar a {{ $selected->name }}?</h3>
            <p style="font-size:13px;color:var(--text-muted);margin-bottom:24px;">Esta acci&oacute;n no se puede deshacer. Se eliminar&aacute;n todos los turnos y registros de cancelaci&oacute;n asociados. Las ventas se conservar&aacute;n.</p>
            <div style="display:flex;gap:12px;">
                <button type="button" class="btn btn-secondary" style="flex:1;justify-content:center;" onclick="document.getElementById('deleteEmployeeModal').style.display='none'">Cancelar</button>
                <form method="POST" action="{{ route('pos.team.destroy', $selected->id) }}" style="flex:1;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn" style="width:100%;justify-content:center;background:var(--red);color:#fff;font-weight:700;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                        Eliminar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

<script>
function openEditEmployee() {
    document.getElementById('editEmployeeModal').style.display = 'flex';
}
</script>
@endsection
