<header class="main-header">
    
    <div class="logo">
        <span class="lavadero">LAVADERO</span> <span class="brillante">BRILLANTE</span>
    </div>

    <nav class="main-nav">
        <a href="{{ route('home') }}">Inicio</a>
        <a href="{{ route('agendamiento.index') }}">Agendamiento</a>
        <a href="{{ route('clientes.index') }}">Clientes</a>
        <a href="{{ route('servicios.index') }}">Servicios</a>
        <a href="{{ route('usuarios.index') }}">Usuarios</a>
        <a href="{{ route('informes.index') }}">Informes</a>
    </nav>

    <div class="user-info">
        @auth
            <span class="user-name">👤 {{ Auth::user()->username }}</span>
            <form id="logoutForm" action="{{ route('logout') }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" class="logout-btn">
                    <span class="icon">⎋</span> Cerrar Sesión
                </button>
            </form>
        @endauth
    </div>

</header>
