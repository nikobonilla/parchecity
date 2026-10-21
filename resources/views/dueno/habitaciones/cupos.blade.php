@extends('layouts.main')

@section('contenido')
    <a href="{{ route('dueno.habitaciones.index', $tipoHabitacion->negocio) }}" class="btn btn-link mb-3">&larr; Volver a tipos de habitación</a>

    <h1 class="mb-1">Disponibilidad — {{ $tipoHabitacion->nombre }}</h1>
    <p class="text-muted">{{ $tipoHabitacion->negocio->nombre }}</p>

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

    <form method="GET" action="{{ route('dueno.habitaciones.cupos', $tipoHabitacion) }}" class="row g-2 mb-4">
        <div class="col-md-4">
            <input type="date" name="fecha" class="form-control" value="{{ $fecha }}">
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-outline-secondary w-100">Ver este día</button>
        </div>
    </form>

    <p>Reservas activas ese día: <strong>{{ $reservadas }}</strong></p>

    <form method="POST" action="{{ route('dueno.habitaciones.cupos.update', $tipoHabitacion) }}">
        @csrf
        @method('PUT')
        <input type="hidden" name="fecha" value="{{ $fecha }}">

        <div class="row mb-3">
            <div class="col-md-3">
                <label class="form-label">Habitaciones habilitadas</label>
                <input type="number" name="cantidad" class="form-control" min="0" max="100" value="{{ old('cantidad', $cupo->cantidad ?? 0) }}">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <div class="form-check form-switch">
                    <input type="checkbox" name="activo" value="1" class="form-check-input" @checked($cupo->activo ?? true)>
                    <label class="form-check-label">Habilitado</label>
                </div>
            </div>
            <div class="col-md-4">
                <label class="form-label">Aplicar hasta (opcional)</label>
                <input type="date" name="hasta" class="form-control" min="{{ $fecha }}">
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Guardar disponibilidad</button>
    </form>
@endsection