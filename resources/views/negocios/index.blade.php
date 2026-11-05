@extends('layouts.main')

@section('contenido')
    @php $configuracion = \App\Models\Configuracion::obtener(); @endphp

    @if ($configuracion->imagen_fondo)
        <div class="mb-4 rounded position-relative overflow-hidden" style="width: 100%; aspect-ratio: 4 / 1;">
            <div class="position-absolute top-0 start-0 w-100 h-100"
                 style="background-image: url('{{ asset('storage/' . $configuracion->imagen_fondo) }}'); background-size: cover; background-position: center; filter: blur(15px) brightness(0.6); transform: scale(1.1);"></div>

            <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center">
                <img src="{{ asset('storage/' . $configuracion->imagen_fondo) }}" style="max-width: 100%; max-height: 100%; object-fit: contain;">
            </div>

            <div class="position-absolute bottom-0 start-0 w-100 text-center text-white pb-3" style="background: linear-gradient(transparent, rgba(0,0,0,0.6));">
                <h1 class="fw-bold mb-0">ParcheCity</h1>
                <p class="mb-0">Encuentra y reserva restaurantes, hoteles y moteles</p>
            </div>
        </div>
    @endif

    @if ($configuracion->imagen_home_1 || $configuracion->imagen_home_2)
        <div class="row mb-4">
            @if ($configuracion->imagen_home_1)
                <div class="col-md-6 mb-3 position-relative rounded overflow-hidden" style="height: 180px;">
                    <div class="position-absolute top-0 start-0 w-100 h-100" style="background-image: url('{{ asset('storage/' . $configuracion->imagen_home_1) }}'); background-size: cover; background-position: center; filter: blur(12px) brightness(0.7); transform: scale(1.1);"></div>
                    <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center">
                        <img src="{{ asset('storage/' . $configuracion->imagen_home_1) }}" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                    </div>
                </div>
            @endif
            @if ($configuracion->imagen_home_2)
                <div class="col-md-6 mb-3 position-relative rounded overflow-hidden" style="height: 180px;">
                    <div class="position-absolute top-0 start-0 w-100 h-100" style="background-image: url('{{ asset('storage/' . $configuracion->imagen_home_2) }}'); background-size: cover; background-position: center; filter: blur(12px) brightness(0.7); transform: scale(1.1);"></div>
                    <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center">
                        <img src="{{ asset('storage/' . $configuracion->imagen_home_2) }}" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                    </div>
                </div>
            @endif
        </div>
    @endif

    <h2 class="mb-4">Negocios</h2>

    <form method="GET" class="row mb-4">
        <div class="col-md-8">
            <input type="text" name="ubicacion" class="form-control" placeholder="Buscar por ubicación..." value="{{ request('ubicacion') }}">
        </div>
        <div class="col-md-4">
            <button type="submit" class="btn btn-primary w-100">Buscar</button>
        </div>
    </form>

    <div class="row">
        @forelse ($negocios as $negocio)
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    @if ($negocio->imagenes->first())
                        <img src="{{ asset('storage/' . $negocio->imagenes->first()->ruta) }}" class="card-img-top" style="height: 200px; object-fit: cover;">
                    @else
                        <div class="bg-secondary text-white text-center py-5">Sin imagen</div>
                    @endif
                    <div class="card-body">
                        <h5 class="card-title">{{ $negocio->nombre }} @if ($negocio->congelado)<span class="badge bg-warning text-dark">Congelado</span>@endif</h5>
                        <p class="card-text text-muted">{{ ucfirst($negocio->tipo) }} · {{ $negocio->ubicacion }}</p>
                        <p class="card-text">{{ Str::limit($negocio->descripcion, 80) }}</p>
                        <a href="{{ route('negocios.show', $negocio) }}" class="btn btn-outline-primary">Ver más</a>
                    </div>
                </div>
            </div>
        @empty
            <p>No hay negocios disponibles por ahora.</p>
        @endforelse
    </div>

    {{ $negocios->links() }}
@endsection