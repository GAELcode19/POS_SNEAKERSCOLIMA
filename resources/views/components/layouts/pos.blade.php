<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Sneakers Colima - POS v2.0' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @livewireStyles
</head>
<body>
    <div class="app-layout">
        {{-- Sidebar --}}
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-brand">
                <div class="sidebar-brand-icon">SC</div>
                <div class="sidebar-brand-text">
                    <h2>Sneakers Colima</h2>
                    <span>Sistema POS v2.0</span>
                </div>
            </div>

            <div class="sidebar-security">
                <span class="sidebar-security-dot"></span>
                Sistema Seguro Activo
            </div>

            <nav class="sidebar-nav">
                <a href="{{ route('pos.terminal') }}" class="sidebar-nav-item {{ request()->routeIs('pos.terminal') ? 'active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                        <line x1="8" y1="21" x2="16" y2="21"></line>
                        <line x1="12" y1="17" x2="12" y2="21"></line>
                    </svg>
                    <div class="sidebar-nav-item-text">
                        POS Terminal
                        <span>Punto de Venta</span>
                    </div>
                </a>

                <a href="{{ route('pos.dashboard') }}" class="sidebar-nav-item {{ request()->routeIs('pos.dashboard') ? 'active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="7" height="7"></rect>
                        <rect x="14" y="3" width="7" height="7"></rect>
                        <rect x="3" y="14" width="7" height="7"></rect>
                        <rect x="14" y="14" width="7" height="7"></rect>
                    </svg>
                    <div class="sidebar-nav-item-text">
                        Dashboard Admin
                        <span>Panel Gerencial</span>
                    </div>
                </a>

                <a href="{{ route('pos.team') }}" class="sidebar-nav-item {{ request()->routeIs('pos.team') ? 'active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                    <div class="sidebar-nav-item-text">
                        Perfiles y Actividad
                        <span>Gesti&oacute;n de Equipo</span>
                    </div>
                </a>

                <a href="{{ route('pos.inventory') }}" class="sidebar-nav-item {{ request()->routeIs('pos.inventory') ? 'active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                        <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                        <line x1="12" y1="22.08" x2="12" y2="12"></line>
                    </svg>
                    <div class="sidebar-nav-item-text">
                        Inventario
                        <span>Control de Stock</span>
                    </div>
                </a>
            </nav>

            <div class="sidebar-bottom">
                <a href="#" class="sidebar-alerts">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:20px;height:20px;">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                    </svg>
                    <span class="badge">3</span>
                    Alertas
                </a>

                <div class="sidebar-user">
                    <div class="sidebar-user-avatar">{{ auth()->user()->initials() }}</div>
                    <div class="sidebar-user-info">
                        <h4>{{ auth()->user()->name }}</h4>
                        <span>{{ auth()->user()->roleLabel() }}</span>
                    </div>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="sidebar-logout">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:20px;height:20px;">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                            <polyline points="16 17 21 12 16 7"></polyline>
                            <line x1="21" y1="12" x2="9" y2="12"></line>
                        </svg>
                        Cerrar Sesi&oacute;n
                    </button>
                </form>
            </div>
        </aside>

        {{-- Main Content --}}
        <main class="main-content">
            {{ $slot }}
        </main>
    </div>

    @livewireScripts
</body>
</html>
