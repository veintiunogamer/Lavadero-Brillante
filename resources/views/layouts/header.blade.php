<header class="main-header">
    
    <div class="logo">
        <img src="{{ asset('/images/logo.png') }}" alt="Logo" width="25%">
    </div>
    
    <button id="nav-toggle" class="nav-toggle" aria-label="Abrir menú">
        <span class="hamburger"></span>
    </button>

    <nav class="main-nav" role="navigation">

        <!-- Home - Inicio -->
        <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">Inicio</a>
        
        <!-- Orders - Agenda -->
        <a href="{{ route('orders.index') }}" class="{{ request()->routeIs('orders.*') ? 'active' : '' }}">Agenda</a>
        
        <!-- Settings - Configuración -->
        <a href="{{ route('settings.index') }}">Configuración</a>
        
        <!-- Users - Usuarios (Solo Administradores) -->
        @if(Auth::check() && Auth::user()->role && Auth::user()->role->type == 1)
            <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'active' : '' }}">Usuarios</a>
        @endif

        <!-- Reports - Informes -->
        <a href="{{ route('reports.index') }}" class="{{ request()->routeIs('reports.*') ? 'active' : '' }}">Informes</a>
    
        <div class="user-info">
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
                        <form id="logoutForm" action="{{ route('logout') }}" method="POST" style="display:block; margin:0;">
                            @csrf
                            <button type="submit" class="logout-btn logout-small" style="width:100%;">
                                    <i class="fa-solid fa-sign-out-alt"></i>&nbsp; Salir
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

    </nav>

</header>
