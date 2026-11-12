@php
    $ventana = $negocio->ventanaDeReservas();
    $estado = $negocio->estadoDeAtencion();
    $hoyResumen = app(\App\Services\DisponibilidadService::class)
        ->resumen(collect([$negocio]), now()->toDateString())[$negocio->id] ?? ['habilitadas' => 0, 'libres' => 0];
    $unidadHoy = $negocio->tipo === 'restaurante' ? 'mesas' : 'habitaciones';
    $recibeReservas = $negocio->estado === 'aprobado' && ! $negocio->congelado;
@endphp

@if ($recibeReservas)
    <div class="alert alert-light border small">
        @if ($ventana && $ventana['ultima_entrada'] < $ventana['abre'])
            <strong>No disponible.</strong> Este negocio no recibe reservas con su horario actual.
        @else
            @if (! $estado['reservable'])
                <strong>No disponible hoy.</strong> {{ $estado['mensaje'] }}.
            @else
                @if ($estado['mensaje'])
                    {{ $estado['mensaje'] }}.
                @endif

                @if ($hoyResumen['libres'] > 0)
                    <strong>{{ $hoyResumen['libres'] }} de {{ $hoyResumen['habilitadas'] }} {{ $unidadHoy }} disponibles hoy.</strong>
                @else
                    <strong>Sin disponibilidad hoy.</strong>
                @endif
            @endif

            @if ($ventana)
                <div>
                    Horario: @hora($negocio->hora_apertura) a @hora($negocio->hora_cierre).
                    Recibe reservas con entrada hasta las @hora($negocio->ultimaEntrada())
                    ({{ \App\Models\Negocio::HORAS_ANTES_DEL_CIERRE }} horas antes del cierre).
                </div>
            @endif
        @endif
    </div>
@endif