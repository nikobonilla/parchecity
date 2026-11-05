@extends('layouts.main')

@section('contenido')
    <h1 class="mb-4">Registrar negocio</h1>

    <form method="POST" action="{{ route('dueno.negocios.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="mb-3">
            <label class="form-label">Nombre</label>
            <input type="text" name="nombre" class="form-control" value="{{ old('nombre') }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Tipo</label>
            <select name="tipo" class="form-select" required>
                <option value="">Selecciona...</option>
                <option value="restaurante">Restaurante</option>
                <option value="hotel">Hotel</option>
                <option value="motel">Motel</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Dirección</label>
            <input type="text" name="direccion" class="form-control" value="{{ old('direccion') }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Ubicación / Ciudad</label>
            <input type="text" name="ubicacion" class="form-control" value="{{ old('ubicacion') }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Descripción</label>
            <textarea name="descripcion" class="form-control" rows="4" required>{{ old('descripcion') }}</textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Contacto (teléfono/correo)</label>
            <input type="text" name="contacto" class="form-control" value="{{ old('contacto') }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">WhatsApp (solo números, con indicativo)</label>
            <input type="text" name="whatsapp" class="form-control" placeholder="57300xxxxxxx" value="{{ old('whatsapp') }}">
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Hora de apertura</label>
                <input type="time" name="hora_apertura" class="form-control">
            </div>
            <div class="col-md-6">
                <label class="form-label">Hora de cierre</label>
                <input type="time" name="hora_cierre" class="form-control">
            </div>
        </div>
</div>

        <div class="mb-3">
            <label class="form-label">Imágenes</label>
            <input type="file" name="imagenes[]" class="form-control" multiple accept="image/*">
        </div>

        <button type="submit" class="btn btn-primary">Guardar</button>
    </form>
@endsection