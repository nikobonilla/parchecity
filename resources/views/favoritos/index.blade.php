@extends('layouts.main')

@section('contenido')
    <h1 class="mb-1">Mis favoritos</h1>
    <p class="text-muted">Los establecimientos que guardaste.</p>

    @if (session('exito'))
        <div class="alert alert-success">{{ session('exito') }}</div>
    @endif

    <div class="row">
        @forelse ($negocios as $negocio)
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    @if ($negocio->imagenes->first())
                        <img src="{{ asset('storage/' . $negocio->imagenes->first()->ruta) }}" class="card-img-top" style="height: 200px; object-fit: cover;">
                    @else
                        <div class="bg-secondary text-white text-center py-5">Sin imagen</div>
                    @endif
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">
                            {{ $negocio->nombre }}
                            @if ($negocio->congelado)
                                <span class="badge bg-warning text-dark">Congelado</span>
                            @endif
                        </h5>
                        <p class="card-text text-muted mb-2">
                            {{ ucfirst($negocio->tipo) }}@if ($negocio->ubicacion) · {{ $negocio->ubicacion }}@endif
                        </p>
                        <div class="d-flex gap-2 mt-auto">
                            <a href="{{ route('negocios.show', $negocio) }}" class="btn btn-outline-primary flex-grow-1">Ver más</a>
                            <form method="POST" action="{{ route('favoritos.destroy', $negocio) }}" onsubmit="return confirm('¿Quitar de tus favoritos?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-outline-danger">Quitar</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <p class="text-muted">Todavía no tienes favoritos. Toca el corazón ♡ en cualquier negocio para guardarlo.</p>
        @endforelse
    </div>

    {{ $negocios->links() }}
@endsection