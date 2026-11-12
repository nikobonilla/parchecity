@extends('layouts.main')

@section('contenido')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Panel de administración</h1>
        <a href="{{ route('admin.estadisticas') }}" class="btn btn-primary">Ver estadísticas</a>
    </div>

    <div class="row">
        <div class="col-md-4 mb-3">
            <a href="{{ route('admin.negocios.pendientes') }}" class="text-decoration-none text-dark">
                <div class="card text-center p-3">
                    <h2>{{ $pendientes }}</h2>
                    <p class="text-muted mb-0">Negocios pendientes</p>
                </div>
            </a>
        </div>
        <div class="col-md-4 mb-3">
            <a href="{{ route('admin.negocios.index') }}" class="text-decoration-none text-dark">
                <div class="card text-center p-3">
                    <h2>{{ $aprobados }}</h2>
                    <p class="text-muted mb-0">Negocios aprobados</p>
                </div>
            </a>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card text-center p-3">
                <h2>{{ $congelados }}</h2>
                <p class="text-muted mb-0">Negocios congelados</p>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <a href="{{ route('admin.usuarios.index') }}" class="text-decoration-none text-dark">
                <div class="card text-center p-3">
                    <h2>{{ $totalUsuarios }}</h2>
                    <p class="text-muted mb-0">Usuarios totales</p>
                </div>
            </a>
        </div>
        <div class="col-md-4 mb-3">
            <a href="{{ route('admin.buzon.index') }}" class="text-decoration-none text-dark">
                <div class="card text-center p-3 {{ $mensajesNuevos > 0 ? 'border-danger' : '' }}">
                    <h2 class="{{ $mensajesNuevos > 0 ? 'text-danger' : '' }}">{{ $mensajesNuevos }}</h2>
                    <p class="text-muted mb-0">Mensajes nuevos en el buzón</p>
                </div>
            </a>
        </div>
    </div>
@endsection