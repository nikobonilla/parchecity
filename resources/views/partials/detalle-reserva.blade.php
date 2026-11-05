@if ($reserva->tipo_habitacion_id)
    <strong>{{ $reserva->tipoHabitacion->nombre ?? 'Habitación' }}</strong><br>
    <small>{{ $reserva->cantidad }} {{ $reserva->modalidad === 'horas' ? 'horas' : 'noches' }}</small><br>
    <small>Entrada: {{ \Carbon\Carbon::parse($reserva->fecha)->format('d/m/Y') }}
        @hora($reserva->hora_entrada)</small><br>
    <small>Salida: {{ \Carbon\Carbon::parse($reserva->fecha_salida)->format('d/m/Y') }}
        @hora($reserva->hora_salida)</small>
@else
    {{ \Carbon\Carbon::parse($reserva->fecha)->format('d/m/Y') }}<br>
    <small>Entrada
        @hora($reserva->hora_entrada)
        @if ($reserva->hora_salida)
            · mesa hasta ~@hora($reserva->hora_salida)
        @endif
    </small>
@endif