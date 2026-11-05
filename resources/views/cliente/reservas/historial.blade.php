@extends('layouts.main')

@section('contenido')
    <a href="{{ route('cliente.reservas.index') }}" class="btn btn-link mb-3">&larr; Volver a mis reservas</a>

    <h1 class="mb-1">Historial de reservas</h1>
    <p class="text-muted">Aquí quedan todas tus reservas, incluso las que eliminaste de tu lista. Este historial no se puede borrar.</p>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="GET" action="{{ route('cliente.reservas.historial') }}" class="row g-2 mb-4">
        <div class="col-md-3">
            <label class="form-label small">Desde</label>
            <input type="date" name="desde" class="form-control" value="{{ request('desde') }}">
        </div>
        <div class="col-md-3">
            <label class="form-label small">Hasta</label>
            <input type="date" name="hasta" class="form-control" value="{{ request('hasta') }}">
        </div>
        <div class="col-md-3">
            <label class="form-label small">Nombre del negocio</label>
            <input type="text" name="negocio" class="form-control" placeholder="Buscar..." value="{{ request('negocio') }}">
        </div>
        <div class="col-md-3 d-flex align-items-end gap-2">
            <button type="submit" class="btn btn-primary">Filtrar</button>
            <a href="{{ route('cliente.reservas.historial') }}" class="btn btn-outline-secondary">Limpiar</a>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Negocio</th>
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
                        $colorPago = ['pagado' => 'success', 'reembolsado' => 'secondary'][$reserva->pago_estado] ?? 'warning';
                    @endphp
                    <tr>
                        <td>{{ $reserva->codigo ?? '-' }}</td>
                        <td>{{ $reserva->negocio->nombre }}</td>
                        <td>@include('partials.detalle-reserva')</td>
                        <td>{{ $reserva->personas ?? '-' }}</td>
                        <td>@cop($reserva->valor_reserva)</td>
                        <td>
                            <span class="badge bg-{{ $reserva->estado === 'confirmada' ? 'success' : ($reserva->estado === 'cancelada' ? 'danger' : 'warning') }}">
                                {{ ucfirst($reserva->estado) }}
                            </span>
                        </td>
                        <td><span class="badge bg-{{ $colorPago }}">{{ ucfirst($reserva->pago_estado) }}</span></td>
                        <td>
                            @if ($reserva->oculta_cliente)
                                <small class="text-muted">Eliminada de tu lista</small>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8">No hay reservas con esos filtros.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $reservas->links() }}
@endsection