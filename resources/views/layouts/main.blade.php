@php $config = \App\Models\Configuracion::obtener(); @endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ParcheCity</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}">
                @if ($config->logo)
                    <img src="{{ asset('storage/' . $config->logo) }}" style="height: 32px;">
                @else
                    ParcheCity
                @endif
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuPrincipal">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="menuPrincipal">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="{{ route('home') }}">Inicio</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('restaurantes') }}">Restaurantes</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('hoteles') }}">Hoteles</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('moteles') }}">Moteles</a></li>

                    @auth
                        <li class="nav-item"><a class="nav-link" href="{{ route('cliente.reservas.index') }}">Mis reservas</a></li>

                        @if (auth()->user()->rol === 'dueño')
                            <li class="nav-item"><a class="nav-link" href="{{ route('dueno.negocios.index') }}">Mis negocios</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('dueno.reservas.index') }}">Reservas recibidas</a></li>
                        @endif

                        @if (auth()->user()->rol === 'administrador')
                            <li class="nav-item"><a class="nav-link" href="{{ route('admin.dashboard') }}">Panel admin</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('admin.negocios.pendientes') }}">Pendientes</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('admin.negocios.index') }}">Todos los negocios</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('admin.buzon.index') }}">Buzón</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('admin.usuarios.index') }}">Usuarios</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('admin.configuracion.edit') }}">Configuración</a></li>
                        @endif
                    @endauth
                </ul>

                <ul class="navbar-nav">
                    @guest
                        <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Iniciar sesión</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('register') }}">Registrarse</a></li>
                    @else
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">{{ auth()->user()->name }}</a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="{{ route('dashboard') }}">Mi cuenta</a></li>
                                <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Perfil</a></li>
                                @if (auth()->user()->rol !== 'administrador')
                                    <li><a class="dropdown-item" href="{{ route('cliente.tarjetas.index') }}">Mis tarjetas</a></li>
                                    <li><a class="dropdown-item" href="{{ route('buzon.crear') }}">Buzón de sugerencias</a></li>
                                @endif
                                @if (auth()->user()->rol === 'dueño' && $config->whatsapp_soporte)
                                    <li>
                                        <a class="dropdown-item" target="_blank"
                                           href="https://wa.me/{{ preg_replace('/\D/', '', $config->whatsapp_soporte) }}?text={{ urlencode('Hola, soy dueño de un negocio en ParcheCity y necesito ayuda.') }}">
                                            Atención al cliente (WhatsApp)
                                        </a>
                                    </li>
                                @endif
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item">Cerrar sesión</button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @endguest
                </ul>
            </div>
        </div>
    </nav>

    <main class="container mt-4" style="min-height: 70vh;">
        @yield('contenido')
    </main>

    <footer class="bg-dark text-light text-center py-3 mt-5">
        <div class="container small">
            &copy; {{ date('Y') }} ParcheCity ·
            <a href="{{ route('terminos') }}" class="text-light">Términos y condiciones</a>
            @auth
                · <a href="{{ route('buzon.crear') }}" class="text-light">Buzón de sugerencias</a>
            @endauth
            · Bogotá, Colombia
        </div>
    </footer>
</body>
</html>