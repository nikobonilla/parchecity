@extends('layouts.main')

@section('contenido')
    <a href="{{ route('admin.negocios.index') }}" class="btn btn-link mb-3">&larr; Volver a todos los negocios</a>

    <h1 class="mb-1">Editar negocio <span class="badge bg-light text-dark border fs-6">{{ $negocio->codigo() }}</span></h1>
    <p class="text-muted">Aquí editas los datos básicos. Las fotos, tarifas y habitaciones las gestiona el dueño.</p>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.negocios.actualizar', $negocio) }}">
        @csrf
        @method('PUT')

        <div class="row">
            <div class="col-md-8 mb-3">
                <label class="form-label">Nombre</label>
                <input type="text" name="nombre" class="form-control" value="{{ old('nombre', $negocio->nombre) }}" required>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Tipo</label>
                <select name="tipo" class="form-select" required>
                    <option value="restaurante" @selected(old('tipo', $negocio->tipo) === 'restaurante')>Restaurante</option>
                    <option value="hotel" @selected(old('tipo', $negocio->tipo) === 'hotel')>Hotel</option>
                    <option value="motel" @selected(old('tipo', $negocio->tipo) === 'motel')>Motel</option>
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Dirección</label>
                <input type="text" name="direccion" class="form-control" value="{{ old('direccion', $negocio->direccion) }}" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Ubicación / Ciudad</label>
                <input type="text" name="ubicacion" class="form-control" value="{{ old('ubicacion', $negocio->ubicacion) }}">
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Descripción</label>
            <textarea name="descripcion" class="form-control" rows="4" required>{{ old('descripcion', $negocio->descripcion) }}</textarea>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Contacto</label>
                <input type="text" name="contacto" class="form-control" value="{{ old('contacto', $negocio->contacto) }}" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">WhatsApp</label>
                <input type="text" name="whatsapp" class="form-control" value="{{ old('whatsapp', $negocio->whatsapp) }}">
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label">Hora de apertura</label>
                <input type="time" name="hora_apertura" class="form-control" value="{{ old('hora_apertura', $negocio->hora_apertura ? substr($negocio->hora_apertura, 0, 5) : '') }}">
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Hora de cierre</label>
                <input type="time" name="hora_cierre" class="form-control" value="{{ old('hora_cierre', $negocio->hora_cierre ? substr($negocio->hora_cierre, 0, 5) : '') }}">
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">{{ $negocio->tipo === 'restaurante' ? 'Tiempo que se ocupa una mesa (min)' : 'Margen de limpieza (min)' }}</label>
                <input type="number" name="minutos_liberacion" class="form-control" min="10" max="1440" value="{{ old('minutos_liberacion', $negocio->minutos_liberacion ?? 90) }}" required>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Guardar cambios</button>
    </form>
@endsection