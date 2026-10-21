@extends('layouts.main')

@section('contenido')
    <a href="{{ route('dueno.negocios.edit', $negocio) }}" class="btn btn-link mb-3">&larr; Volver a editar negocio</a>

    <h1 class="mb-1">Disponibilidad por fecha</h1>
    <p class="text-muted">{{ $negocio->nombre }}</p>

    @if (session('exito'))
        <div class="alert alert-success">{{ session('exito') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="GET" action="{{ route('dueno.cupos.index', $negocio) }}" class="row g-2 mb-4">
        <div class="col-md-4">
            <input type="date" name="fecha" class="form-control" value="{{ $fecha }}">
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-outline-secondary w-100">Ver este día</button>
        </div>
    </form>

    <form method="POST" action="{{ route('dueno.cupos.update', $negocio) }}">
        @csrf
        @method('PUT')
        <input type="hidden" name="fecha" value="{{ $fecha }}">

        <h5>Mesas para el {{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}</h5>

        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Para cuántas personas</th>
                    <th>Mesas habilitadas</th>
                    <th>Habilitado</th>
                    <th>Reservas activas ese día</th>
                </tr>
            </thead>
            <tbody>
                @foreach (range(1, 6) as $personas)
                    @php
                        $cupo = $cupos->get($personas);
                        $cantidad = $cupo ? $cupo->cantidad : 0;
                        $activo = $cupo ? $cupo->activo : true;
                    @endphp
                    <tr>
                        <td>{{ $personas }} {{ $personas === 1 ? 'persona' : 'personas' }}</td>
                        <td style="max-width: 140px;">
                            <input type="number" name="cantidad[{{ $personas }}]" class="form-control" min="0" max="100" value="{{ old("cantidad.$personas", $cantidad) }}">
                        </td>
                        <td>
                            <div class="form-check form-switch">
                                <input type="checkbox" name="activo[{{ $personas }}]" value="1" class="form-check-input" @checked($activo)>
                            </div>
                        </td>
                        <td>{{ $reservadas[$personas] ?? 0 }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="row mb-3">
            <div class="col-md-5">
                <label class="form-label">Aplicar esta misma configuración hasta (opcional)</label>
                <input type="date" name="hasta" class="form-control" min="{{ $fecha }}" value="{{ old('hasta') }}">
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Guardar disponibilidad</button>
    </form>
@endsection