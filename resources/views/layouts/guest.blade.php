<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ParcheCity</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-light">
    <div class="d-flex align-items-center justify-content-center min-vh-100">
        <div class="card shadow-sm" style="max-width: 400px; width: 100%;">
            <div class="card-body p-4">
                <a href="{{ route('home') }}" class="text-decoration-none small text-muted">&larr; Volver al inicio</a>

                <h3 class="text-center my-3">ParcheCity</h3>

                <ul class="nav nav-pills nav-justified mb-4">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('login') ? 'active' : '' }}" href="{{ route('login') }}">Iniciar sesión</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('register') ? 'active' : '' }}" href="{{ route('register') }}">Registrarse</a>
                    </li>
                </ul>

                {{ $slot }}
            </div>
        </div>
    </div>
</body>
</html>