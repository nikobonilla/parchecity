@extends('layouts.main')

@section('contenido')
    <h1 class="mb-3">Estadísticas</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($negocios->isEmpty())
        <p class="text-muted">Todavía no tienes negocios. Cuando registres uno y reciba reservas, aquí verás tus estadísticas.</p>
    @else
        <form method="GET" action="{{ route('dueno.estadisticas') }}" class="card card-body mb-4">
            <div class="row g-2 mb-3">
                <div class="col-md-6">
                    <label class="form-label small">Negocio</label>
                    <select name="negocio_id" class="form-select">
                        <option value="">Todos mis negocios</option>
                        @foreach ($negocios as $negocio)
                            <option value="{{ $negocio->id }}" @selected((string) request('negocio_id') === (string) $negocio->id)>
                                {{ $negocio->nombre }} ({{ $negocio->codigo() }}){{ $negocio->trashed() ? ' · eliminado' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            @include('partials.estadisticas-fechas')

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Ver estadísticas</button>
                <a href="{{ route('dueno.estadisticas') }}" class="btn btn-outline-secondary">Limpiar</a>
            </div>
        </form>

        @include('partials.estadisticas-resultados')
    @endif
@endsection