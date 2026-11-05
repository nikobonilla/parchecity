@extends('layouts.main')

@section('contenido')
    <a href="{{ url()->previous() }}" class="btn btn-link mb-3">&larr; Volver</a>@include('partials.aviso-congelado')

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
            @if ($negocio->imagenes->isNotEmpty())
                <div id="carruselImagenes" class="carousel slide mb-4">
                    @if ($negocio->imagenes->count() > 1)
                        <div class="carousel-indicators">
                            @foreach ($negocio->imagenes as $index => $imagen)
                                <button type="button" data-bs-target="#carruselImagenes" data-bs-slide-to="{{ $index }}" class="{{ $index === 0 ? 'active' : '' }}"></button>
                            @endforeach
                        </div>
                    @endif

                    <div class="carousel-inner rounded bg-dark">
                        @foreach ($negocio->imagenes as $index => $imagen)
                            <div class="carousel-item @if($index === 0) active @endif">
                                <img src="{{ asset('storage/' . $imagen->ruta) }}" class="d-block w-100" style="height: 380px; object-fit: contain;">
                            </div>
                        @endforeach
                    </div>

                    @if ($negocio->imagenes->count() > 1)
                        <button class="carousel-control-prev" type="button" data-bs-target="#carruselImagenes" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon"></span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#carruselImagenes" data-bs-slide="next">
                            <span class="carousel-control-next-icon"></span>
                        </button>
                    @endif
                </div>
            @else
                <div class="bg-secondary text-white text-center py-5 mb-4">Sin imágenes</div>
            @endif
        </div>

        <div class="col-md-5">
            <h1>{{ $negocio->nombre }} <small class="badge bg-light text-dark border fs-6 align-middle">{{ $negocio->codigo() }}</small></h1>
            <p class="text-muted">{{ ucfirst($negocio->tipo) }} · {{ $negocio->ubicacion }}</p>
            <p>{{ $negocio->descripcion }}</p>
            <p><strong>Dirección:</strong> {{ $negocio->direccion }}</p>

            @if ($negocio->hora_apertura && $negocio->hora_cierre)
                <p><strong>Horario de reservas:</strong> @hora($negocio->hora_apertura) a @hora($negocio->hora_cierre)</p>
            @endif

            @if ($negocio->whatsapp)
                <a href="https://wa.me/{{ preg_replace('/\D/', '', $negocio->whatsapp) }}" target="_blank" class="btn btn-success mb-2">
                    WhatsApp del negocio
                </a>
                <p class="small text-muted">El WhatsApp es solo para comunicarte con el negocio por temas administrativos. No sirve para apartar reservas.</p>
            @endif

            <div class="alert alert-info">
                <strong>Pago:</strong> con tarjeta débito o crédito, desde "Mis reservas", después de reservar.
            </div>

            @if ($negocio->tipo === 'restaurante' && $negocio->tarifas->isNotEmpty())
                <h5 class="mt-3">Tarifas para apartar</h5>
                <ul class="list-group mb-3">
                    @foreach ($negocio->tarifas->sortBy('hora_inicio') as $tarifa)
                        <li class="list-group-item d-flex justify-content-between">
                            <span>
                                {{ $tarifa->franja_horaria }}
                                @if ($tarifa->hora_inicio && $tarifa->hora_fin)
                                    <small class="text-muted">(@hora($tarifa->hora_inicio) a @hora($tarifa->hora_fin))</small>
                                @endif
                            </span>
                            <strong>@cop($tarifa->precio)</strong>
                        </li>
                    @endforeach
                </ul>
                <p class="small text-muted">La tarifa depende de la franja en la que sea tu hora de entrada. Es un valor fijo: no se suma por horas.</p>
            @endif
        </div>
    </div>

    @if ($negocio->tipo !== 'restaurante')
        <hr class="my-4">

        <div id="habitaciones">
            <h3 class="mb-3">Habitaciones</h3>

            @if ($categorias->isNotEmpty())
                <div class="d-flex flex-wrap gap-2 mb-4">
                    <a href="{{ route('negocios.show', $negocio) }}#habitaciones"
                       class="btn btn-sm {{ $categoriaActiva ? 'btn-outline-dark' : 'btn-dark' }}">Todas</a>
                    @foreach ($categorias as $categoria)
                        <a href="{{ route('negocios.show', ['negocio' => $negocio, 'categoria' => $categoria->id]) }}#habitaciones"
                           class="btn btn-sm {{ $categoriaActiva == $categoria->id ? 'btn-dark' : 'btn-outline-dark' }}">{{ $categoria->nombre }}</a>
                    @endforeach
                </div>
            @endif

            <div class="row">
                @forelse ($habitaciones as $habitacion)
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            @if ($habitacion->imagenes->first())
                                <img src="{{ asset('storage/' . $habitacion->imagenes->first()->ruta) }}" class="card-img-top" style="height: 200px; object-fit: cover;">
                            @else
                                <div class="bg-secondary text-white text-center py-5">Sin imagen</div>
                            @endif
                            <div class="card-body d-flex flex-column">
                                <div>
                                    <span class="badge bg-dark mb-2">{{ $habitacion->categoria->nombre ?? 'Sin categoría' }}</span>
                                    <h5 class="card-title">{{ $habitacion->nombre }}</h5>
                                    <p class="card-text mb-1">
                                        @if ($habitacion->precio_hora)
                                            <span class="d-block">Por hora: <strong>@cop($habitacion->precio_hora)</strong></span>
                                        @endif
                                        @if ($habitacion->precio_noche)
                                            <span class="d-block">Por noche: <strong>@cop($habitacion->precio_noche)</strong></span>
                                        @endif
                                    </p>
                                    <small class="text-muted">
                                        {{ $habitacion->capacidad ? 'Hasta ' . $habitacion->capacidad . ' personas' : 'Sin máximo de personas' }}
                                    </small>
                                </div>
                                <a href="{{ route('habitaciones.show', [$negocio, $habitacion]) }}" class="btn btn-outline-primary mt-3">Ver habitación</a>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-muted">Este negocio todavía no tiene habitaciones publicadas{{ $categoriaActiva ? ' en esta categoría' : '' }}.</p>
                @endforelse
            </div>
        </div>
    @endif

    <hr class="my-4">

    <h3 class="mb-3">
        Reseñas
        @if ($negocio->resenas->isNotEmpty())
            <span class="text-warning">
                {{ str_repeat('★', round($negocio->resenas->avg('calificacion'))) }}{{ str_repeat('☆', 5 - round($negocio->resenas->avg('calificacion'))) }}
            </span>
            <small class="text-muted">({{ number_format($negocio->resenas->avg('calificacion'), 1) }} de {{ $negocio->resenas->count() }} {{ $negocio->resenas->count() === 1 ? 'reseña' : 'reseñas' }})</small>
        @endif
    </h3>

    @forelse ($negocio->resenas as $resena)
        <div class="border-bottom pb-2 mb-2">
            <div>
                <strong>{{ $resena->user->name }}</strong>
                <span class="text-warning">{{ str_repeat('★', $resena->calificacion) }}{{ str_repeat('☆', 5 - $resena->calificacion) }}</span>
            </div>
            @if ($resena->comentario)
                <p class="mb-0">{{ $resena->comentario }}</p>
            @endif
            @auth
                @if ($resena->user_id === auth()->id() || auth()->user()->rol === 'administrador')
                    <form method="POST" action="{{ route('resenas.destroy', $resena) }}" onsubmit="return confirm('¿Eliminar esta reseña?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-link text-danger p-0">
                            {{ $resena->user_id === auth()->id() ? 'Eliminar' : 'Eliminar (administrador)' }}
                        </button>
                    </form>
                @endif
            @endauth
        </div>
    @empty
        <p class="text-muted">Este negocio todavía no tiene reseñas.</p>
    @endforelse

    @auth
        @php
            $puedeResenar = $negocio->reservas()->where('user_id', auth()->id())->where('estado', 'confirmada')->exists();
            $miResena = $negocio->resenas->firstWhere('user_id', auth()->id());
        @endphp

        @if ($puedeResenar)
            <form method="POST" action="{{ route('resenas.store', $negocio) }}" class="mt-3">
                @csrf
                <div class="mb-2">
                    <label class="form-label">{{ $miResena ? 'Edita tu reseña' : 'Deja tu reseña' }}</label>
                    <select name="calificacion" class="form-select" style="max-width: 150px;" required>
                        @foreach ([5,4,3,2,1] as $n)
                            <option value="{{ $n }}" @selected(optional($miResena)->calificacion == $n)>{{ $n }} {{ $n === 1 ? 'estrella' : 'estrellas' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-2">
                    <textarea name="comentario" class="form-control" rows="2" placeholder="Comentario (opcional)">{{ old('comentario', optional($miResena)->comentario) }}</textarea>
                </div>
                <button type="submit" class="btn btn-sm btn-primary">{{ $miResena ? 'Actualizar reseña' : 'Enviar reseña' }}</button>
            </form>
        @endif
    @endauth

    @if ($negocio->tipo === 'restaurante')
        <hr class="my-4">

        <h3 class="mb-3">Reservar</h3>

        @auth
            @if ($negocio->estado === 'aprobado' && ! $negocio->congelado)
                <form method="POST" action="{{ route('reservas.store', $negocio) }}">
                    @csrf

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Nombre completo</label>
                            <input type="text" name="nombre_cliente" class="form-control" value="{{ old('nombre_cliente', auth()->user()->name) }}" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Correo electrónico</label>
                            <input type="email" name="correo_cliente" class="form-control" value="{{ old('correo_cliente', auth()->user()->email) }}" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Teléfono de contacto</label>
                            <input type="text" name="telefono" class="form-control" value="{{ old('telefono') }}" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Fecha</label>
                            <input type="date" name="fecha" class="form-control" value="{{ old('fecha') }}" min="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Hora de entrada</label>
                            <input type="time" name="hora_entrada" class="form-control" value="{{ old('hora_entrada') }}" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Número de personas</label>
                            <select name="personas" class="form-select" required>
                                <option value="">Selecciona...</option>
                                @foreach (range(1, 6) as $n)
                                    <option value="{{ $n }}" @selected(old('personas') == $n)>{{ $n }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12 mb-3" id="valorEstimado" style="display:none;">
                            <div class="alert alert-primary mb-0">
                                Valor según la hora: <strong id="valorEstimadoTexto"></strong>
                            </div>
                        </div>
                    </div>

                    <p class="text-muted small">
                        La mesa se mantiene {{ $negocio->minutos_liberacion ?? 90 }} minutos desde tu hora de entrada y después se libera sola.
                        El valor para apartar es fijo según la franja de tu hora de entrada.
                    </p>

                    <div class="mb-3">
                        <label class="form-label">Comentario o petición especial (opcional)</label>
                        <textarea name="comentario" class="form-control" rows="2">{{ old('comentario') }}</textarea>
                    </div>

                    <div class="form-check mb-3">
                        <input type="checkbox" name="acepta_politica" class="form-check-input" id="acepta_politica" required>
                        <label class="form-check-label" for="acepta_politica">
                            Entiendo que tengo 30 minutos para pagar con tarjeta, y que el pago solo es reembolsable dentro de los {{ \App\Models\Reserva::MINUTOS_REEMBOLSO }} minutos posteriores a hacerlo.
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary">Reservar</button>
                </form>
            @else
                <p class="text-muted">Este negocio no recibe reservas por ahora (está pendiente de aprobación o congelado).</p>
            @endif
        @else
            <a href="{{ route('login') }}" class="btn btn-primary">Inicia sesión para reservar</a>
        @endauth

        @php
            $tarifasJs = $negocio->tarifas->map(fn ($t) => [
                'inicio' => $t->hora_inicio,
                'fin' => $t->hora_fin,
                'precio' => $t->precio,
                'nombre' => $t->franja_horaria,
            ])->values();
        @endphp

        <script>
            const tarifas = @json($tarifasJs);

            const inputHora = document.querySelector('input[name="hora_entrada"]');
            const cajaValor = document.getElementById('valorEstimado');
            const textoValor = document.getElementById('valorEstimadoTexto');

            if (inputHora && cajaValor) {
                inputHora.addEventListener('change', function () {
                    const hora = this.value + ':00';
                    const tarifa = tarifas.find(t => t.inicio && t.fin && hora >= t.inicio && hora <= t.fin);

                    if (tarifa) {
                        textoValor.textContent = tarifa.nombre + ': $' + Number(tarifa.precio).toLocaleString('es-CO');
                        cajaValor.style.display = 'block';
                    } else {
                        cajaValor.style.display = 'none';
                    }
                });
            }
        </script>
    @endif
@endsection