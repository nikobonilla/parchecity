@php
    $codigo = $exception->getStatusCode();

    $titulos = [
        401 => 'Necesitas iniciar sesión',
        403 => 'Acceso denegado',
        404 => 'Página no encontrada',
        419 => 'La página expiró',
        429 => 'Demasiadas solicitudes',
        500 => 'Error del servidor',
        503 => 'Estamos en mantenimiento',
    ];

    $mensajes = [
        401 => 'Inicia sesión para continuar.',
        403 => 'No tienes permiso para ver esta página.',
        404 => 'La página que buscas no existe o fue eliminada.',
        419 => 'Tu sesión expiró. Recarga la página e inténtalo de nuevo.',
        429 => 'Hiciste muchos intentos seguidos. Espera un momento e inténtalo de nuevo.',
        500 => 'Algo salió mal de nuestro lado. Inténtalo de nuevo en unos minutos.',
        503 => 'Volvemos en unos minutos.',
    ];

    $titulo = $titulos[$codigo] ?? 'Ocurrió un error';
    $mensaje = $mensajes[$codigo] ?? 'Algo salió mal. Inténtalo de nuevo.';

    // Los mensajes propios de la plataforma (ya en español) se muestran tal cual
    $propio = $exception->getMessage();
    if ($codigo === 403 && \Illuminate\Support\Str::startsWith($propio, ['No tienes', 'Solo puedes', 'No puedes'])) {
        $mensaje = $propio;
    }
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $titulo }} - ParcheCity</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .caja { background: #fff; border-radius: 10px; padding: 40px; max-width: 460px; text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,.08); }
        .codigo { font-size: 64px; font-weight: bold; color: #0d6efd; line-height: 1; }
        h1 { font-size: 22px; margin: 12px 0 8px; }
        p { color: #666; margin-bottom: 24px; }
        a { background: #0d6efd; color: #fff; padding: 10px 20px; border-radius: 6px; text-decoration: none; }
    </style>
</head>
<body>
    <div class="caja">
        <div class="codigo">{{ $codigo }}</div>
        <h1>{{ $titulo }}</h1>
        <p>{{ $mensaje }}</p>
        <a href="{{ url('/') }}">Volver al inicio</a>
    </div>
</body>
</html>