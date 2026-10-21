@extends('layouts.main')

@section('contenido')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Mis reservas</h1>
        @if (request()->boolean('pasadas'))
            <a href="{{ route('cliente.reservas.index') }}" class="btn btn-outline-secondary">Ver solo próximas</a>
        @else
            <a href="{{ route('cliente.reservas.index', ['pasadas' => 1]) }}" class="btn btn-outline-secondary">Ver también pasadas</a>
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
                    <th>Fecha</th>
                    <th>Horario</th>
                    <th>Personas</th>
                    <th>Valor</th>
                    <th>Estado</th>
                    <th>Pago</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($reservas as $reserva)
                    <tr>
                        <td><strong>{{ $reserva->codigo }}</strong></td>
                        <td>
                            <a href="{{ route('negocios.show', $reserva->negocio) }}">{{ $reserva->negocio->nombre }}</a>
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
    @if ($reserva->pago_estado === 'pendiente' && $reserva->estado !== 'cancelada')
        <form method="POST" action="{{ route('cliente.reservas.pagado', $reserva) }}" class="d-inline">
            @csrf
            <button class="btn btn-sm btn-primary">Ya pagué</button>
            @if ($reserva->pago_estado === 'pendiente' && $reserva->estado !== 'cancelada')
    <a href="{{ route('cliente.reservas.pagar', $reserva) }}" class="btn btn-sm btn-primary">Pagar con tarjeta</a>
    <form method="POST" action="{{ route('cliente.reservas.pagado', $reserva) }}" class="d-inline">
        @csrf
        <button class="btn btn-sm btn-outline-primary">Ya pagué por transferencia</button>
    </form>
@endif
        </form>
    @endif
    @if (in_array($reserva->estado, ['pendiente', 'confirmada']))
        <form method="POST" action="{{ route('cliente.reservas.cancelar', $reserva) }}" class="d-inline" onsubmit="return confirm('¿Cancelar esta reserva?')">
            @csrf
            <button class="btn btn-sm btn-outline-danger">Cancelar</button>
        </form>
    @endif
</td>
                    </tr>
                @empty
                    <tr><td colspan="8">Todavía no tienes reservas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection