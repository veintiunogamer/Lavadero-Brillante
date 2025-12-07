<header class="main-header">
    
    <div class="logo">
        <img src="{{ asset('/images/logo.png') }}" alt="Logo" class="logo-img">
    </div>
    
    <button id="nav-toggle" class="nav-toggle" aria-label="Abrir menú">
        <span class="hamburger"></span>
    </button>

    <nav class="main-nav" role="navigation">

        <!-- Home - Inicio -->
        <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}"><i class="fa-solid fa-home me-1"></i><span class="nav-text">Inicio</span></a>
        
        <!-- Orders - Agenda -->
        <a href="{{ route('orders.index') }}" class="{{ request()->routeIs('orders.*') ? 'active' : '' }}"><i class="fa-solid fa-calendar-check me-1"></i><span class="nav-text">Agenda</span></a>
        
        <!-- Settings - Configuración -->
        <a href="{{ route('settings.index') }}" class="{{ request()->routeIs('settings.*') ? 'active' : '' }}"><i class="fa-solid fa-cog me-1"></i><span class="nav-text">Configuración</span></a>
        
        <!-- Users - Usuarios (Solo Administradores) -->
        @if(Auth::check() && Auth::user()->role && Auth::user()->role->type == 1)
            <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'active' : '' }}"><i class="fa-solid fa-users me-1"></i><span class="nav-text">Usuarios</span></a>
        @endif

        <!-- Reports - Informes -->
        <a href="{{ route('reports.index') }}" class="{{ request()->routeIs('reports.*') ? 'active' : '' }}"><i class="fa-solid fa-chart-line me-1"></i><span class="nav-text">Informes</span></a>

    </nav>

    <!-- Usuario/Admin al extremo derecho -->
    <div class="user-info header-user">
        @auth
            <div class="user-dropdown">
                <!-- User Toggle Button -->
                <button id="user-toggle" class="user-toggle" aria-expanded="false" aria-label="Usuario">
                    <i class="fa-solid fa-user"></i>
                    <span class="user-name">{{ Auth::user()->username }}</span>
                    <i class="fa-solid fa-caret-down caret"></i>
                </button>

                <!-- User Menu -->
                <div class="user-menu" aria-hidden="true">

                    <a href="#">Perfil</a>

                    <form id="logoutForm" action="{{ route('logout') }}" method="POST" style="display:block; margin:0;">
                        @csrf
                        <button type="submit" class="logout-btn logout-small" style="width:100%;">
                            <i class="fa-solid fa-sign-out-alt"></i>&nbsp; Cerrar Sesión
                        </button>
                    </form>

                </div>

            </div>

            <!-- Inline logout for small screens -->
            <form id="logoutFormMobile" action="{{ route('logout') }}" method="POST" style="display:none;">
                @csrf
                <button type="submit" class="logout-btn logout-inline logout-small">
                        <i class="fa-solid fa-sign-out-alt"></i>&nbsp; Salir
                </button>
            </form>
        @endauth
    </div>

</header>
