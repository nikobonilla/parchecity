@extends('layouts.main')

@section('contenido')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <h1>Reservas recibidas</h1>
        <div class="d-flex gap-2">
            @if (request()->boolean('pasadas'))
                <a href="{{ route('dueno.reservas.index') }}" class="btn btn-outline-secondary">Ver solo próximas</a>
            @else
                <a href="{{ route('dueno.reservas.index', ['pasadas' => 1]) }}" class="btn btn-outline-secondary">Ver también pasadas</a>
            @endif
            <a href="{{ route('dueno.reservas.historial') }}" class="btn btn-outline-dark">Historial</a>
        </div>
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
                    <th>Detalle</th>
                    <th>Personas</th>
                    <th>Valor</th>
                    <th>Estado</th>
                    <th>Pago</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($reservas as $reserva)
                    @php
                        $activa = $reserva->estaActiva();
                        $colorPago = ['pagado' => 'success', 'reembolsado' => 'secondary'][$reserva->pago_estado] ?? 'warning';
                    @endphp
                    <tr>
                        <td>
                            @if ($reserva->codigo)
                                <strong>{{ $reserva->codigo }}</strong>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>{{ $reserva->negocio->nombre }}</td>
                        <td>
                            {{ $reserva->nombre_cliente }}<br>
                            <small class="text-muted">{{ $reserva->telefono }} · {{ $reserva->correo_cliente }}</small>
                            @if ($reserva->comentario)
                                <br><small>"{{ $reserva->comentario }}"</small>
                            @endif
                        </td>
                        <td>@include('partials.detalle-reserva')</td>
                        <td>{{ $reserva->personas ?? '-' }}</td>
                        <td>@cop($reserva->valor_reserva)</td>
                        <td>
                            <span class="badge bg-{{ $reserva->estado === 'confirmada' ? 'success' : ($reserva->estado === 'cancelada' ? 'danger' : 'warning') }}">
                                {{ ucfirst($reserva->estado) }}
                            </span>
                            @if ($reserva->liberada_at)
                                <span class="badge bg-info">Liberada</span>
                            @endif
                        </td>
                        <td><span class="badge bg-{{ $colorPago }}">{{ ucfirst($reserva->pago_estado) }}</span></td>
                        <td class="text-nowrap">
                            @if ($reserva->estado === 'confirmada' && ! $reserva->liberada_at)
                                <form method="POST" action="{{ route('dueno.reservas.liberar', $reserva) }}" class="d-inline" onsubmit="return confirm('¿Liberar ahora? El cupo vuelve a estar disponible.')">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-success">Liberar ahora</button>
                                </form>
                            @endif
                            @if ($activa)
                                <form method="POST" action="{{ route('dueno.reservas.cancelar', $reserva) }}" class="d-inline" onsubmit="return confirm('¿Cancelar esta reserva?')">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-danger">Cancelar</button>
                                </form>
                            @endif
                            <form method="POST" action="{{ route('dueno.reservas.eliminar', $reserva) }}" class="d-inline" onsubmit="return confirm('¿Eliminar de tu lista? Si la reserva está activa, también se cancelará. Seguirá apareciendo en tu historial.')">
                                @csrf
                                <button class="btn btn-sm btn-outline-secondary">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9">No hay reservas para mostrar.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection