@extends('layouts.main')

@section('contenido')
    <a href="{{ route('cliente.reservas.index') }}" class="btn btn-link mb-3">&larr; Volver a mis reservas</a>

    <div class="row justify-content-center">
        <div class="col-md-7">
            <h1 class="mb-3">Editar reserva</h1>

            <div class="card mb-3">
                <div class="card-body">
                    <strong>{{ $reserva->negocio->nombre }}</strong>
                    <div>@include('partials.detalle-reserva')</div>
                    <div class="text-muted small mt-1">
                        Para cambiar la fecha, la hora o las personas, cancela esta reserva y haz una nueva (así se revisa la disponibilidad).
                    </div>
                </div>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('cliente.reservas.actualizar', $reserva) }}">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label">Nombre completo</label>
                    <input type="text" name="nombre_cliente" class="form-control" value="{{ old('nombre_cliente', $reserva->nombre_cliente) }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Correo electrónico</label>
                    <input type="email" name="correo_cliente" class="form-control" value="{{ old('correo_cliente', $reserva->correo_cliente) }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Teléfono de contacto</label>
                    <input type="text" name="telefono" class="form-control" value="{{ old('telefono', $reserva->telefono) }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Comentario o petición especial</label>
                    <textarea name="comentario" class="form-control" rows="2">{{ old('comentario', $reserva->comentario) }}</textarea>
                </div>

                <button type="submit" class="btn btn-primary">Guardar cambios</button>
            </form>
        </div>
    </div>
@endsection