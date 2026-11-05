@extends('layouts.main')

@section('contenido')
    <div class="mb-4">
        <h1 class="mb-1">Hola, {{ $usuario->name }}</h1>
        <span class="badge bg-secondary">{{ ucfirst($usuario->rol) }}</span>
    </div>

    @if (session('exito'))
        <div class="alert alert-success">{{ session('exito') }}</div>
    @endif

    <div class="d-flex flex-wrap gap-2 mb-4">
        <a href="{{ route('cliente.reservas.index') }}" class="btn btn-primary">Mis reservas</a>
        <a href="{{ route('cliente.reservas.historial') }}" class="btn btn-outline-primary">Historial</a>
        <a href="{{ route('cliente.tarjetas.index') }}" class="btn btn-outline-primary">Mis tarjetas</a>
        <a href="{{ route('profile.edit') }}" class="btn btn-outline-secondary">Mi perfil</a><a href="{{ route('buzon.crear') }}" class="btn btn-outline-secondary">Buzón de sugerencias</a>

        @if ($usuario->rol === 'dueño')
            <a href="{{ route('dueno.negocios.index') }}" class="btn btn-success">Mis negocios</a>
            <a href="{{ route('dueno.reservas.index') }}" class="btn btn-outline-success">Reservas recibidas</a>@include('partials.boton-soporte')
        @endif
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <h4>Mis próximas reservas</h4>

            <div class="list-group mb-2">
                @forelse ($proximas as $reserva)
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between">
                            <strong>{{ $reserva->negocio->nombre }}</strong>
                            <span class="badge bg-{{ $reserva->estado === 'confirmada' ? 'success' : 'warning' }}">{{ ucfirst($reserva->estado) }}</span>
                        </div>
                        <div class="small">@include('partials.detalle-reserva')</div>
                        <div class="small text-muted">@cop($reserva->valor_reserva)</div>
                    </div>
                @empty
                    <div class="list-group-item text-muted">No tienes reservas próximas.</div>
                @endforelse
            </div>

            <a href="{{ route('cliente.reservas.index') }}">Ver todas mis reservas</a>
        </div>

        <div class="col-lg-5">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h4 class="mb-0">Mis tarjetas</h4>
                <a href="{{ route('cliente.tarjetas.index') }}" class="small">Agregar tarjeta</a>
            </div>

            @include('partials.tarjetas')
        </div>
    </div>
@endsection