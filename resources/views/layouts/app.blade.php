<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ParcheCity</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    @include('layouts.navigation')

    @if (isset($header))
        <div class="bg-white border-bottom py-3 mb-4">
            <div class="container">
                {{ $header }}
            </div>
        </div>
    @endif

    <main class="container">
        {{ $slot }}
    </main>
</body>
</html>