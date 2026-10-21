@extends('layouts.main')

@section('contenido')
    <h1 class="mb-4">Panel de administración</h1>

    <div class="row">
        <div class="col-md-4 mb-3">
            <div class="card text-center p-3">
                <h2>{{ $pendientes }}</h2>
                <p class="text-muted mb-0">Negocios pendientes</p>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card text-center p-3">
                <h2>{{ $aprobados }}</h2>
                <p class="text-muted mb-0">Negocios aprobados</p>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card text-center p-3">
                <h2>{{ $totalUsuarios }}</h2>
                <p class="text-muted mb-0">Usuarios totales</p>
            </div>
        </div>
    </div>
@endsection