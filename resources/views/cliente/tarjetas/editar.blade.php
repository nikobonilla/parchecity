@extends('layouts.main')

@section('contenido')
    <a href="{{ route('cliente.tarjetas.index') }}" class="btn btn-link mb-3">&larr; Volver a mis tarjetas</a>

    <div class="row justify-content-center">
        <div class="col-md-6">
            <h1 class="mb-1">Editar tarjeta</h1>
            <p class="text-muted">{{ $tarjeta->marca }} •••• {{ $tarjeta->ultimos4 }}</p>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('cliente.tarjetas.update', $tarjeta) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Tipo</label>
                            <select name="tipo" class="form-select" required>
                                <option value="credito" @selected(old('tipo', $tarjeta->tipo) === 'credito')>Crédito</option>
                                <option value="debito" @selected(old('tipo', $tarjeta->tipo) === 'debito')>Débito</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Titular</label>
                            <input type="text" name="titular" class="form-control" value="{{ old('titular', $tarjeta->titular) }}" required>
                        </div>

                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label">Mes</label>
                                <input type="number" name="mes" class="form-control" min="1" max="12" value="{{ old('mes', $tarjeta->mes) }}" required>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label">Año</label>
                                <input type="number" name="ano" class="form-control" value="{{ old('ano', $tarjeta->ano) }}" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Guardar cambios</button>
                    </form>
                </div>
            </div>

            <p class="text-muted small mt-2">
                El número de la tarjeta no se puede cambiar porque no lo guardamos. Si quieres usar otro número, agrega una tarjeta nueva.
            </p>
        </div>
    </div>
@endsection