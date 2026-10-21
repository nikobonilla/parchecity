<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="{{ route('home') }}">ParcheCity</a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
               <li class="nav-item">
    @if (auth()->user()->rol === 'administrador')
        <a class="nav-link" href="{{ route('admin.dashboard') }}">Panel</a>
    @else
        <a class="nav-link" href="{{ route('dashboard') }}">Panel</a>
    @endif
</li>
               @auth
                   @if (auth()->user()->rol === 'dueño')
                       <li class="nav-item"><a class="nav-link" href="{{ route('dueno.negocios.index') }}">Mis negocios</a></li>
                   @endif
                   @if (auth()->user()->rol === 'administrador')
                       <li class="nav-item"><a class="nav-link" href="{{ route('admin.negocios.pendientes') }}">Pendientes</a></li>
                       <li class="nav-item"><a class="nav-link" href="{{ route('admin.negocios.index') }}">Todos los negocios</a></li>
                   @endif
               @endauth
            </ul>

            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        {{ Auth::user()->name }}
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Perfil</a></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item">Cerrar sesión</button>
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>