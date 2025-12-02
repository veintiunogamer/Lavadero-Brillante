<header class="main-header">
    
    <div class="logo">
        <span class="lavadero">LAVADERO</span> <span class="brillante">BRILLANTE</span>
    </div>
    
    <button id="nav-toggle" class="nav-toggle" aria-label="Abrir menú">
        <span class="hamburger"></span>
    </button>

    <nav class="main-nav" role="navigation">
        <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">Inicio</a>
        <a href="{{ route('agendamiento.index') }}" class="{{ request()->routeIs('agendamiento.*') ? 'active' : '' }}">Agenda</a>
        <a href="{{ route('clientes.index') }}" class="{{ request()->routeIs('clientes.*') ? 'active' : '' }}">Clientes</a>
        <a href="{{ route('servicios.index') }}" class="{{ request()->routeIs('servicios.*') ? 'active' : '' }}">Servicios</a>
        @if(Auth::check() && Auth::user()->role && Auth::user()->role->type == 1)
        <a href="{{ route('usuarios.index') }}" class="{{ request()->routeIs('usuarios.*') ? 'active' : '' }}">Usuarios</a>
        @endif
        <a href="{{ route('informes.index') }}" class="{{ request()->routeIs('informes.*') ? 'active' : '' }}">Informes</a>
    </nav>

    <div class="user-info">
        @auth
            <div class="user-dropdown">
                <button id="user-toggle" class="user-toggle" aria-expanded="false" aria-label="Usuario">
                        <i class="fa-solid fa-user"></i>
                    <span class="user-name">{{ Auth::user()->username }}</span>
                        <i class="fa-solid fa-caret-down caret"></i>
                </button>

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

</header>
