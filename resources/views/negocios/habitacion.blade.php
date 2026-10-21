@extends('layouts.main')

@section('contenido')
    <a href="{{ route('negocios.show', $negocio) }}#habitaciones" class="btn btn-link mb-3">&larr; Volver a {{ $negocio->nombre }}</a>

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

    <div class="row">
        <div class="col-md-7">
            @if ($tipoHabitacion->imagenes->isNotEmpty())
                <div id="carruselHabitacion" class="carousel slide mb-4">
                    @if ($tipoHabitacion->imagenes->count() > 1)
                        <div class="carousel-indicators">
                            @foreach ($tipoHabitacion->imagenes as $index => $imagen)
                                <button type="button" data-bs-target="#carruselHabitacion" data-bs-slide-to="{{ $index }}" class="{{ $index === 0 ? 'active' : '' }}"></button>
                            @endforeach
                        </div>
                    @endif

                    <div class="carousel-inner rounded bg-dark">
                        @foreach ($tipoHabitacion->imagenes as $index => $imagen)
                            <div class="carousel-item @if($index === 0) active @endif">
                                <img src="{{ asset('storage/' . $imagen->ruta) }}" class="d-block w-100" style="height: 420px; object-fit: contain;">
                            </div>
                        @endforeach
                    </div>

                    @if ($tipoHabitacion->imagenes->count() > 1)
                        <button class="carousel-control-prev" type="button" data-bs-target="#carruselHabitacion" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon"></span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#carruselHabitacion" data-bs-slide="next">
                            <span class="carousel-control-next-icon"></span>
                        </button>
                    @endif
                </div>
            @else
                <div class="bg-secondary text-white text-center py-5 mb-4">Sin imágenes</div>
            @endif
        </div>

        <div class="col-md-5">
            <span class="badge bg-dark mb-2">{{ $tipoHabitacion->categoria->nombre ?? 'Sin categoría' }}</span>
            <h1>{{ $tipoHabitacion->nombre }}</h1>
            <p class="text-muted">{{ $negocio->nombre }} · {{ ucfirst($negocio->tipo) }} · {{ $negocio->ubicacion }}</p>

            @if ($tipoHabitacion->especificaciones)
                <p>{{ $tipoHabitacion->especificaciones }}</p>
            @endif

            <ul class="list-group mb-3">
                @if ($tipoHabitacion->precio_hora)
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Por hora <small class="text-muted">(de 3 a 12 horas)</small></span>
                        <strong>${{ number_format($tipoHabitacion->precio_hora, 0, ',', '.') }}</strong>
                    </li>
                @endif
                @if ($tipoHabitacion->precio_noche)
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Por noche</span>
                        <strong>${{ number_format($tipoHabitacion->precio_noche, 0, ',', '.') }}</strong>
                    </li>
                @endif
                <li class="list-group-item">
                    {{ $tipoHabitacion->capacidad ? 'Hasta ' . $tipoHabitacion->capacidad . ' personas' : 'Sin máximo de personas' }}
                </li>
            </ul>

            <p><strong>Dirección:</strong> {{ $negocio->direccion }}</p>

            @if ($negocio->whatsapp)
                <a href="https://wa.me/{{ preg_replace('/\D/', '', $negocio->whatsapp) }}" target="_blank" class="btn btn-success mb-2">
                    WhatsApp del negocio
                </a>
                <p class="small text-muted">El WhatsApp es solo para comunicarte con el negocio por temas administrativos. No sirve para apartar reservas.</p>
            @endif

            @if ($negocio->medio_pago)
                <div class="alert alert-info">
                    <strong>Medio de pago:</strong> {{ $negocio->medio_pago }}
                </div>
            @endif
        </div>
    </div>

    <hr class="my-4">

    <h3 class="mb-3">Reservar esta habitación</h3>
    <div class="alert alert-secondary">El formulario de reserva se agrega en el siguiente paso.</div>
@endsection