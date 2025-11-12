<header class="main-header">
    
    <div class="logo">
        <span class="lavadero">LAVADERO</span> <span class="brillante">BRILLANTE</span>
    </div>

    <nav class="main-nav">
        <a href="#">Agendamiento</a>
        <a href="#">Clientes</a>
        <a href="#">Servicios</a>
        <a href="#">Usuarios</a>
        <a href="#">Informes</a>
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
