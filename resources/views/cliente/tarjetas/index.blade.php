@extends('layouts.main')

@section('contenido')
    <a href="{{ route('dashboard') }}" class="btn btn-link mb-3">&larr; Volver a mi cuenta</a>

    <h1 class="mb-1">Mis tarjetas</h1>
    <p class="text-muted">
        Solo guardamos la marca, los últimos 4 dígitos, el titular y el vencimiento. Nunca guardamos el número completo ni el CVV: el CVV se pide en cada pago.
    </p>

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

    <div class="mb-4">
        @include('partials.tarjetas')
    </div>

    <div class="card">
        <div class="card-body">
            <h5>Agregar tarjeta</h5>
            <form method="POST" action="{{ route('cliente.tarjetas.store') }}" class="row g-2">
                @csrf
                <div class="col-md-3">
                    <label class="form-label small">Tipo</label>
                    <select name="tipo" class="form-select" required>
                        <option value="credito" @selected(old('tipo') === 'credito')>Crédito</option>
                        <option value="debito" @selected(old('tipo') === 'debito')>Débito</option>
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label small">Número de tarjeta</label>
                    <input type="text" name="numero" class="form-control" placeholder="Cualquier número de 12 a 19 dígitos" value="{{ old('numero') }}" maxlength="23" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Titular</label>
                    <input type="text" name="titular" class="form-control" value="{{ old('titular', auth()->user()->name) }}" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Mes</label>
                    <input type="number" name="mes" class="form-control" placeholder="MM" min="1" max="12" value="{{ old('mes') }}" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Año</label>
                    <input type="number" name="ano" class="form-control" placeholder="AA o AAAA" value="{{ old('ano') }}" required>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Guardar tarjeta</button>
                </div>
            </form>
        </div>
    </div>
@endsection