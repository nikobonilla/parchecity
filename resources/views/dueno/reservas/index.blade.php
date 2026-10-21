@extends('layouts.main')

@section('contenido')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Reservas recibidas</h1>
        @if (request()->boolean('pasadas'))
            <a href="{{ route('dueno.reservas.index') }}" class="btn btn-outline-secondary">Ver solo próximas</a>
        @else
            <a href="{{ route('dueno.reservas.index', ['pasadas' => 1]) }}" class="btn btn-outline-secondary">Ver también pasadas</a>
        @endif
    </div>

    @if (session('exito'))
        <div class="alert alert-success">{{ session('exito') }}</div>
    @endif

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Negocio</th>
                    <th>Cliente</th>
                    <th>Fecha</th>
                    <th>Horario</th>
                    <th>Personas</th>
                    <th>Valor</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($reservas as $reserva)
                    <tr>
                        <td><strong>{{ $reserva->codigo }}</strong></td>
                        <td>{{ $reserva->negocio->nombre }}</td>
                        <td>
                            {{ $reserva->nombre_cliente }}<br>
                            <small class="text-muted">{{ $reserva->telefono }} · {{ $reserva->correo_cliente }}</small>
                            @if ($reserva->comentario)
                                <br><small>"{{ $reserva->comentario }}"</small>
                            @endif
                        </td>
                        <td>{{ \Carbon\Carbon::parse($reserva->fecha)->format('d/m/Y') }}</td>
                        <td>
                            {{ $reserva->hora_entrada ? substr($reserva->hora_entrada, 0, 5) : '-' }}
                            a
                            {{ $reserva->hora_salida ? substr($reserva->hora_salida, 0, 5) : '-' }}
                        </td>
                        <td>{{ $reserva->personas ?? '-' }}</td>
                        <td>
                            @if ($reserva->valor_reserva)
                                ${{ number_format($reserva->valor_reserva, 0, ',', '.') }}
                            @else
                                -
                            @endif
                        </td>
                       <td>
    <span class="badge bg-{{ ['pagado' => 'success', 'reportado' => 'info', 'reembolsado' => 'secondary'][$reserva->pago_estado] ?? 'warning' }}">
        {{ ucfirst($reserva->pago_estado) }}
    </span>
</td>
<td>
    @if ($reserva->pago_estado === 'reportado')
        <form method="POST" action="{{ route('dueno.reservas.confirmarPago', $reserva) }}" class="d-inline">
            @csrf
            <button class="btn btn-sm btn-success">Confirmar pago</button>
        </form>
    @endif
    @if (in_array($reserva->estado, ['pendiente', 'confirmada']))
        <form method="POST" action="{{ route('dueno.reservas.cancelar', $reserva) }}" class="d-inline" onsubmit="return confirm('¿Cancelar esta reserva?')">
            @csrf
            <button class="btn btn-sm btn-outline-danger">Cancelar</button>
        </form>
    @endif
</td>
                    </tr>
                @empty
                    <tr><td colspan="9">No hay reservas para mostrar.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection