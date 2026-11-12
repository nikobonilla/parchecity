@extends('layouts.main')

@section('contenido')
    @php
        $configuracion = \App\Models\Configuracion::obtener();
        $hoy = now()->toDateString();
        $favoritosIds = auth()->check() ? auth()->user()->favoritos()->pluck('negocios.id')->all() : [];
        $etiquetaFecha = $fecha === $hoy ? 'hoy' : 'el ' . \Carbon\Carbon::parse($fecha)->format('d/m/Y');
    @endphp

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
            @foreach ([$configuracion->imagen_home_1, $configuracion->imagen_home_2] as $imagenDestacada)
                @if ($imagenDestacada)
                    <div class="col-md-6 mb-3 position-relative rounded overflow-hidden" style="height: 180px;">
                        <div class="position-absolute top-0 start-0 w-100 h-100" style="background-image: url('{{ asset('storage/' . $imagenDestacada) }}'); background-size: cover; background-position: center; filter: blur(12px) brightness(0.7); transform: scale(1.1);"></div>
                        <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center">
                            <img src="{{ asset('storage/' . $imagenDestacada) }}" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    @endif

    <h2 class="mb-3">
        @switch($tipoFijo)
            @case('restaurante') Restaurantes @break
            @case('hotel') Hoteles @break
            @case('motel') Moteles @break
            @default Negocios
        @endswitch
    </h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="GET" action="{{ url()->current() }}" class="card card-body mb-4">
        <div class="row g-2 mb-2">
            @unless ($tipoFijo)
                <div class="col-md-3">
                    <label class="form-label small">Tipo de establecimiento</label>
                    <select name="tipo" class="form-select">
                        <option value="">Todos</option>
                        <option value="restaurante" @selected(request('tipo') === 'restaurante')>Restaurantes</option>
                        <option value="hotel" @selected(request('tipo') === 'hotel')>Hoteles</option>
                        <option value="motel" @selected(request('tipo') === 'motel')>Moteles</option>
                    </select>
                </div>
            @endunless

            <div class="col-md-{{ $tipoFijo ? 5 : 4 }}">
                <label class="form-label small">Nombre o ID del negocio</label>
                <input type="text" name="q" class="form-control" placeholder="Ej: Los Pinos o NEG-0004" value="{{ request('q') }}">
            </div>

            <div class="col-md-3">
                <label class="form-label small">Fecha</label>
                <input type="date" name="fecha" class="form-control" min="{{ $hoy }}" value="{{ $fecha }}">
            </div>

            <div class="col-md-{{ $tipoFijo ? 4 : 2 }} d-flex align-items-end">
                <div class="form-check">
                    <input type="checkbox" name="disponibilidad" value="1" class="form-check-input" id="soloDisponibles" @checked(request()->boolean('disponibilidad'))>
                    <label class="form-check-label" for="soloDisponibles">Solo con disponibilidad</label>
                </div>
            </div>
        </div>

        @include('partials.selector-ubicacion', ['filtro' => true])

        <div class="mt-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary">Buscar</button>
            <a href="{{ url()->current() }}" class="btn btn-outline-secondary">Limpiar</a>
        </div>
    </form>

    <p class="text-muted small">
        La disponibilidad es cuántas mesas o habitaciones siguen libres para esa fecha, descontando las reservas ya hechas.
        Si un negocio está cerrado o ya no recibe reservas, aparece como no disponible.
    </p>

    <div class="row">
        @forelse ($negocios as $negocio)
            @php
                $esRestaurante = $negocio->tipo === 'restaurante';
                $disp = $resumen[$negocio->id] ?? ['habilitadas' => 0, 'libres' => 0];
                $libres = $disp['libres'];
                $habilitadas = $disp['habilitadas'];
                $unidad = $esRestaurante
                    ? ($habilitadas === 1 ? 'mesa' : 'mesas')
                    : ($habilitadas === 1 ? 'habitación' : 'habitaciones');
                $estado = $fecha === $hoy
                    ? $negocio->estadoDeAtencion()
                    : ['abierto' => true, 'reservable' => true, 'mensaje' => null];
            @endphp
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
                        <p class="card-text text-muted mb-1">
                            {{ ucfirst($negocio->tipo) }}
                            @if ($negocio->ubicacion) · {{ $negocio->ubicacion }} @endif
                        </p>
                        @if ($negocio->barrio)
                            <p class="card-text small text-muted mb-1">Barrio {{ $negocio->barrio }}</p>
                        @endif

                        <div class="mb-2">
                            @if ($negocio->congelado)
                                <span class="badge bg-secondary">No disponible: no recibe reservas por ahora</span>
                            @elseif (! $estado['reservable'])
                                <span class="badge bg-secondary">No disponible {{ $etiquetaFecha }}</span>
                                <div class="small text-muted">{{ $estado['mensaje'] }}</div>
                            @else
                                @if ($estado['mensaje'])
                                    <div class="small text-muted">{{ $estado['mensaje'] }}</div>
                                @endif

                                @if ($libres > 0)
                                    <span class="badge bg-success">{{ $libres }} de {{ $habilitadas }} {{ $unidad }} disponibles {{ $etiquetaFecha }}</span>
                                @else
                                    <span class="badge bg-secondary">Sin disponibilidad {{ $etiquetaFecha }}</span>
                                @endif
                            @endif
                        </div>

                        <p class="card-text">{{ \Illuminate\Support\Str::limit($negocio->descripcion, 80) }}</p>

                        <div class="d-flex gap-2 mt-auto">
                            <a href="{{ route('negocios.show', $negocio) }}" class="btn btn-outline-primary flex-grow-1">Ver más</a>
                            @include('partials.boton-favorito', ['pequeno' => true])
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <p>No hay negocios con esos filtros.</p>
        @endforelse
    </div>

    {{ $negocios->links() }}
@endsection