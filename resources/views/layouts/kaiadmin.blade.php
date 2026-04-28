<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>@yield('title', 'Hotel Lencería') — {{ config('app.name') }}</title>
    <meta content="width=device-width, initial-scale=1.0, shrink-to-fit=no" name="viewport" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome 5 CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <!-- Kaiadmin CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/plugins.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/kaiadmin.min.css') }}" />

    @stack('styles')
    <style>
        /* ── Mobile sidebar overlay ── */
        #sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,.5);
            z-index: 1044;
        }
        .btn-mobile-sidebar {
            display: none;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background: transparent;
            border: none;
            color: #fff;
            font-size: 1.4rem;
            cursor: pointer;
            padding: 0;
            line-height: 1;
        }
        @media (max-width: 991.98px) {
            .btn-mobile-sidebar { display: inline-flex !important; }
        }
        /* Neutralizar el efecto topbar_open de Kaiadmin (el rectángulo blanco) */
        html.topbar_open .navbar-header,
        html.topbar_open .main-header,
        html.topbar_open .main-panel,
        html.topbar_open .main-panel > .container {
            transform: none !important;
            background: transparent !important;
            margin-top: 0 !important;
        }
        html.topbar_open { overflow: auto !important; }
        /* Drawer derecho de perfil (mismo estilo que sidebar izquierdo) */
        #right-drawer {
            position: fixed;
            top: 0;
            right: 0;
            width: 265px;
            max-width: 85vw;
            height: 100vh;
            background: #1a2035;
            color: #fff;
            z-index: 1060;
            overflow-y: auto;
            box-shadow: -4px 0 10px rgba(0,0,0,.2);
        }
        #right-drawer-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,.45);
            z-index: 1055;
        }
        #right-drawer .rd-header {
            padding: 20px 16px;
            border-bottom: 1px solid rgba(255,255,255,.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        #right-drawer .rd-avatar {
            width: 42px; height: 42px; border-radius: 50%;
            background: #5a8dee; color: #fff; display: flex;
            align-items: center; justify-content: center;
            font-weight: 700; font-size: 1.1rem; flex-shrink: 0;
        }
        #right-drawer .rd-info { flex: 1; min-width: 0; padding-left: 12px; }
        #right-drawer .rd-info h6 { color: #fff; margin: 0 0 2px; font-size: .95rem; }
        #right-drawer .rd-info p { color: rgba(255,255,255,.55); margin: 0; font-size: .8rem; }
        #right-drawer .rd-close {
            background: rgba(255,255,255,.1); border: none; color: #fff;
            width: 30px; height: 30px; border-radius: 50%; cursor: pointer;
            font-size: .9rem; display: flex; align-items: center; justify-content: center;
        }
        #right-drawer .rd-nav { list-style: none; padding: 10px 0; margin: 0; }
        #right-drawer .rd-nav li a,
        #right-drawer .rd-nav li button {
            display: flex; align-items: center; gap: 10px;
            padding: 12px 20px; color: rgba(255,255,255,.8);
            text-decoration: none; background: transparent; border: none;
            font-size: .9rem; width: 100%; text-align: left; cursor: pointer;
            transition: background .15s;
        }
        #right-drawer .rd-nav li a:hover,
        #right-drawer .rd-nav li button:hover { background: rgba(255,255,255,.08); color: #fff; }
        #right-drawer .rd-nav li a i,
        #right-drawer .rd-nav li button i { width: 20px; text-align: center; }
        @media (min-width: 992px) {
            #right-drawer, #right-drawer-backdrop { display: none !important; }
        }
    </style>
</head>
<body>
<div class="wrapper">

    {{-- ============================================================ Sidebar --}}
    <div class="sidebar" data-background-color="dark">
        <div class="sidebar-logo">
            <div class="logo-header" data-background-color="dark">
                <a href="{{ auth()->user()?->role === 'admin' ? route('admin.dashboard') : route('empleado.dashboard') }}" class="logo">
                    <span class="text-white fw-bold fs-5">🏨 Hotel</span>
                </a>
                <div class="nav-toggle">
                    <button class="btn btn-toggle toggle-sidebar"><i class="gg-menu-right"></i></button>
                    <button class="btn btn-toggle sidenav-toggler"><i class="gg-menu-left"></i></button>
                </div>
                <button class="topbar-toggler more"><i class="gg-more-vertical-alt"></i></button>
            </div>
        </div>

        <div class="sidebar-wrapper scrollbar scrollbar-inner">
            <div class="sidebar-content">
                <ul class="nav nav-secondary">

                @auth
                    @if(auth()->user()->role === 'admin')
                        {{-- Admin Nav --}}
                        <li class="nav-section">
                            <span class="sidebar-mini-icon"><i class="fa fa-ellipsis-h"></i></span>
                            <h4 class="text-section">Administración</h4>
                        </li>
                        <li class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                            <a href="{{ route('admin.dashboard') }}">
                                <i class="fas fa-tachometer-alt"></i>
                                <p>Tablero en vivo</p>
                            </a>
                        </li>
                        <li class="nav-item {{ request()->routeIs('admin.habitaciones.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.habitaciones.index') }}">
                                <i class="fas fa-door-open"></i>
                                <p>Habitaciones</p>
                            </a>
                        </li>
                        <li class="nav-item {{ request()->routeIs('admin.lencerias.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.lencerias.index') }}">
                                <i class="fas fa-tshirt"></i>
                                <p>Lencería</p>
                            </a>
                        </li>
                        <li class="nav-item {{ request()->routeIs('admin.empleados.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.empleados.index') }}">
                                <i class="fas fa-users"></i>
                                <p>Empleados</p>
                            </a>
                        </li>
                        <li class="nav-item {{ request()->routeIs('admin.asignaciones.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.asignaciones.create') }}">
                                <i class="fas fa-user-plus"></i>
                                <p>Asignar habitación</p>
                            </a>
                        </li>
                        <li class="nav-item {{ request()->routeIs('admin.historial.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.historial.index') }}">
                                <i class="fas fa-history"></i>
                                <p>Historial</p>
                            </a>
                        </li>

                    @else
                        {{-- Empleado Nav --}}
                        <li class="nav-section">
                            <span class="sidebar-mini-icon"><i class="fa fa-ellipsis-h"></i></span>
                            <h4 class="text-section">Mi turno</h4>
                        </li>
                        <li class="nav-item {{ request()->routeIs('empleado.dashboard') ? 'active' : '' }}">
                            <a href="{{ route('empleado.dashboard') }}">
                                <i class="fas fa-door-open"></i>
                                <p>Mis habitaciones</p>
                            </a>
                        </li>
                        <li class="nav-item {{ request()->routeIs('empleado.scanner') ? 'active' : '' }}">
                            <a href="{{ route('empleado.scanner') }}">
                                <i class="fas fa-qrcode"></i>
                                <p>Escanear QR</p>
                            </a>
                        </li>
                    @endif
                @endauth

                </ul>
            </div>
        </div>
    </div>
    {{-- ============================================================ End Sidebar --}}

    @auth
    {{-- Right drawer (perfil) - fuera del wrapper para evitar problemas de z-index --}}
    <div id="right-drawer-backdrop" style="display:none;"></div>
    <div id="right-drawer" style="transform:translateX(100%);transition:transform .3s ease;">
        <div class="rd-header">
            <div class="rd-avatar">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
            <div class="rd-info">
                <h6>{{ auth()->user()->name }}</h6>
                <p>{{ auth()->user()->email }}</p>
                @if(auth()->user()->turno)
                    <span class="badge bg-info mt-1" style="font-size:.7rem;">Turno {{ auth()->user()->turno }}</span>
                @endif
            </div>
            <button type="button" class="rd-close" id="rd-close-btn"><i class="fas fa-times"></i></button>
        </div>
        <ul class="rd-nav">
            <li><a href="{{ route('profile.edit') }}"><i class="fas fa-user"></i>Mi perfil</a></li>
            <li>
                <form method="POST" action="{{ route('logout') }}" class="mb-0">
                    @csrf
                    <button type="submit"><i class="fas fa-sign-out-alt" style="color:#e74c3c;"></i><span style="color:#e74c3c;">Cerrar sesión</span></button>
                </form>
            </li>
        </ul>
    </div>
    @endauth

    <div class="main-panel">

        {{-- ============================================================ Topbar --}}
        <div class="main-header">
            <div class="main-header-logo">
                <div class="logo-header" data-background-color="dark">
                    <a href="#" class="logo"><span class="text-white fw-bold">🏨</span></a>
                    <div class="nav-toggle">
                        <button class="btn btn-toggle toggle-sidebar"><i class="gg-menu-right"></i></button>
                        <button class="btn btn-toggle sidenav-toggler"><i class="gg-menu-left"></i></button>
                    </div>
                    <button class="topbar-toggler more"><i class="gg-more-vertical-alt"></i></button>
                </div>
            </div>

            <nav class="navbar navbar-header navbar-header-transparent navbar-expand-lg border-bottom">
                <div class="container-fluid">
                    <div class="d-flex align-items-center">
                        <h5 class="mb-0 text-muted d-none d-md-block">@yield('page-title', config('app.name'))</h5>
                    </div>
                    <ul class="navbar-nav topbar-nav ms-md-auto align-items-center">
                        @auth
                        <li class="nav-item topbar-user dropdown hidden-caret">
                            <a class="dropdown-toggle profile-pic" data-bs-toggle="dropdown" href="#" aria-expanded="false">
                                <div class="avatar-sm">
                                    <span class="avatar-title rounded-circle bg-primary text-white fw-bold">
                                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                    </span>
                                </div>
                                <span class="profile-username">
                                    <span class="op-7">Hola,</span>
                                    <span class="fw-bold">{{ auth()->user()->name }}</span>
                                </span>
                            </a>
                            <ul class="dropdown-menu dropdown-user animated fadeIn">
                                <div class="dropdown-user-scroll scrollbar-outer">
                                    <li>
                                        <div class="user-box">
                                            <div class="u-text">
                                                <h4>{{ auth()->user()->name }}</h4>
                                                <p class="text-muted">{{ auth()->user()->email }}</p>
                                                @if(auth()->user()->turno)
                                                    <span class="badge bg-info">Turno {{ auth()->user()->turno }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                            <i class="fas fa-user me-2"></i> Mi perfil
                                        </a>
                                        <div class="dropdown-divider"></div>
                                        <form method="POST" action="{{ route('logout') }}" id="logout-form">
                                            @csrf
                                            <a class="dropdown-item text-danger" href="{{ route('logout') }}"
                                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                                <i class="fas fa-sign-out-alt me-2"></i> Cerrar sesión
                                            </a>
                                        </form>
                                    </li>
                                </div>
                            </ul>
                        </li>
                        @endauth
                    </ul>
                </div>
            </nav>
        </div>
        {{-- ============================================================ End Topbar --}}

        <div class="container">
            <div class="page-inner">

                {{-- Breadcrumb / header --}}
                <div class="page-header">
                    <h4 class="page-title">@yield('page-title', '')</h4>
                    <ul class="breadcrumbs">
                        <li class="nav-home">
                            <a href="{{ auth()->user()?->role === 'admin' ? route('admin.dashboard') : route('empleado.dashboard') }}">
                                <i class="icon-home"></i>
                            </a>
                        </li>
                        @yield('breadcrumbs')
                    </ul>
                </div>

                {{-- Flash messages --}}
                @if(session('status'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('status') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                {{-- Page Content --}}
                @yield('content')

            </div>
        </div>

        <footer class="footer">
            <div class="container-fluid d-flex justify-content-between">
                <nav class="pull-left">
                    <ul class="nav">
                        <li class="nav-item"><span class="nav-link text-muted">Sistema Hotel Lencería</span></li>
                    </ul>
                </nav>
                <div class="copyright text-muted small">
                    {{ now()->year }} — <i class="fa fa-heart text-danger"></i> Hotel Lencería
                </div>
            </div>
        </footer>
    </div>
{{-- Mobile sidebar overlay --}}
<div id="sidebar-overlay"></div>

</div>

{{-- Core JS --}}
<script src="{{ asset('assets/js/core/jquery-3.7.1.min.js') }}"></script>
<script src="{{ asset('assets/js/core/popper.min.js') }}"></script>
<script src="{{ asset('assets/js/core/bootstrap.min.js') }}"></script>
<script src="{{ asset('assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js') }}"></script>
<script src="{{ asset('assets/js/kaiadmin.min.js') }}"></script>
{{-- Alpine.js (defer so it initialises after DOM) --}}
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

@stack('scripts')
<script>
(function(){
    var overlay = document.getElementById('sidebar-overlay');
    var sidebar = document.querySelector('.sidebar');
    var isOpen  = false;

    function openSidebar() {
        if (!sidebar) return;
        sidebar.style.setProperty('transform',         'translate3d(0,0,0)', 'important');
        sidebar.style.setProperty('-webkit-transform', 'translate3d(0,0,0)', 'important');
        sidebar.style.setProperty('z-index', '1045', 'important');
        if (overlay) overlay.style.display = 'block';
        isOpen = true;
    }
    function closeSidebar() {
        if (!sidebar) return;
        sidebar.style.removeProperty('transform');
        sidebar.style.removeProperty('-webkit-transform');
        sidebar.style.removeProperty('z-index');
        if (overlay) overlay.style.display = 'none';
        isOpen = false;
    }
    function toggleSidebar(ev) {
        if (window.innerWidth >= 992) return;  // solo móvil
        if (ev) { ev.preventDefault(); ev.stopPropagation(); }
        if (isOpen) closeSidebar(); else openSidebar();
    }

    // Hookear a TODOS los botones hamburguesa de Kaiadmin
    document.querySelectorAll('.sidenav-toggler, .toggle-sidebar, #btn-open-sidebar')
        .forEach(function(b){ b.addEventListener('click', toggleSidebar); });

    // Drawer derecho (perfil) — se engancha DESPUÉS de que kaiadmin.js clona los botones
    var rdDrawer   = document.getElementById('right-drawer');
    var rdBackdrop = document.getElementById('right-drawer-backdrop');
    var rdOpen     = false;

    function openRightDrawer()  {
        if (!rdDrawer || !rdBackdrop) return;
        rdDrawer.style.setProperty('transform', 'translateX(0)', 'important');
        rdBackdrop.style.display = 'block';
        rdOpen = true;
    }
    function closeRightDrawer() {
        if (!rdDrawer || !rdBackdrop) return;
        rdDrawer.style.setProperty('transform', 'translateX(100%)', 'important');
        rdBackdrop.style.display = 'none';
        rdOpen = false;
    }
    function toggleRightDrawer(ev) {
        if (ev) { ev.preventDefault(); ev.stopImmediatePropagation(); }
        if (rdOpen) closeRightDrawer(); else openRightDrawer();
    }

    // Enganchar a TODOS los .topbar-toggler (incluyendo los clonados por kaiadmin.js)
    document.querySelectorAll('.topbar-toggler').forEach(function(b){
        b.addEventListener('click', toggleRightDrawer);
    });
    if (rdBackdrop) rdBackdrop.addEventListener('click', closeRightDrawer);
    var rdCloseBtn = document.getElementById('rd-close-btn');
    if (rdCloseBtn) rdCloseBtn.addEventListener('click', closeRightDrawer);

    if (overlay) overlay.addEventListener('click', closeSidebar);
    document.querySelectorAll('.sidebar .nav-item a').forEach(function(a){
        a.addEventListener('click', function(){ if (window.innerWidth < 992) closeSidebar(); });
    });
    window.addEventListener('resize', function(){ if (window.innerWidth >= 992 && isOpen) closeSidebar(); });
})();
</script>
</body>
</html>
