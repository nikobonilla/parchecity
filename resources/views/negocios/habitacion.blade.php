@extends('layouts.main')

@section('contenido')
    <a href="{{ route('negocios.show', $negocio) }}#habitaciones" class="btn btn-link mb-3">&larr; Volver a {{ $negocio->nombre }}</a>@include('partials.aviso-congelado')@include('partials.estado-atencion')

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
                        <strong>@cop($tipoHabitacion->precio_hora)</strong>
                    </li>
                @endif
                @if ($tipoHabitacion->precio_noche)
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Por noche</span>
                        <strong>@cop($tipoHabitacion->precio_noche)</strong>
                    </li>
                @endif
                <li class="list-group-item">
                    {{ $tipoHabitacion->capacidad ? 'Hasta ' . $tipoHabitacion->capacidad . ' personas' : 'Sin máximo de personas' }}
                </li>
            </ul>

            <p><strong>Dirección:</strong> {{ $negocio->direccion }}@if ($negocio->barrio) · Barrio {{ $negocio->barrio }}@endif @if ($negocio->localidad) · {{ $negocio->localidad->nombre }}@endif @if ($negocio->indicaciones)<br><small class="text-muted">{{ $negocio->indicaciones }}</small>@endif</p>

            @if ($negocio->whatsapp)
                <a href="https://wa.me/{{ preg_replace('/\D/', '', $negocio->whatsapp) }}" target="_blank" class="btn btn-success mb-2">
                    WhatsApp del negocio
                </a>
                <p class="small text-muted">El WhatsApp es solo para comunicarte con el negocio por temas administrativos. No sirve para apartar reservas.</p>
            @endif

                <div class="alert alert-info">
                  <strong>Pago:</strong> con tarjeta débito o crédito, desde "Mis reservas", después de reservar.
              </div>
        </div>
    </div>

    <hr class="my-4">

    <h3 class="mb-3">Reservar esta habitación</h3>

    @auth
        @php
            $modalidades = [];
            if ($tipoHabitacion->precio_hora) { $modalidades['horas'] = 'Por horas'; }
            if ($tipoHabitacion->precio_noche) { $modalidades['noches'] = 'Por noches'; }
            $modalidadInicial = old('modalidad', array_key_first($modalidades));
        @endphp

        @if ($negocio->estado === 'aprobado' && ! $negocio->congelado && count($modalidades) > 0)
            <form method="POST" action="{{ route('habitaciones.reservar', [$negocio, $tipoHabitacion]) }}">
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
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Fecha de entrada</label>
                        <input type="date" name="fecha" class="form-control" value="{{ old('fecha') }}" min="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Hora de entrada</label>
                        <input type="time" name="hora_entrada" class="form-control" value="{{ old('hora_entrada') }}" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Modalidad</label>
                        <select name="modalidad" id="modalidad" class="form-select" required>
                            @foreach ($modalidades as $clave => $etiqueta)
                                <option value="{{ $clave }}" @selected($modalidadInicial === $clave)>{{ $etiqueta }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label" id="etiquetaCantidad">Cantidad</label>
                        <input type="number" name="cantidad" id="cantidad" class="form-control" value="{{ old('cantidad', $modalidadInicial === 'horas' ? 3 : 1) }}" required>
                    </div>
                </div>

                @if ($tipoHabitacion->capacidad)
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Número de personas</label>
                            <select name="personas" class="form-select" required>
                                <option value="">Selecciona...</option>
                                @foreach (range(1, $tipoHabitacion->capacidad) as $n)
                                    <option value="{{ $n }}" @selected(old('personas') == $n)>{{ $n }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                @endif

                <div class="alert alert-primary">
                    Total: <strong id="total">-</strong>
                    &nbsp;·&nbsp; Salida estimada: <strong id="salida">-</strong>
                </div>

                <div class="mb-3">
                    <label class="form-label">Comentario o petición especial (opcional)</label>
                    <textarea name="comentario" class="form-control" rows="2">{{ old('comentario') }}</textarea>
                </div>

                <div class="form-check mb-3">
                    <input type="checkbox" name="acepta_politica" class="form-check-input" id="acepta_politica" required>
                    <label class="form-check-label" for="acepta_politica">
                        Entiendo que se cobra el valor de la reserva para apartarla, y que solo es reembolsable dentro de los {{ \App\Models\Reserva::MINUTOS_REEMBOLSO }} minutos posteriores al pago.
                    </label>
                </div>

                <button type="submit" class="btn btn-primary">Reservar</button>
            </form>

            <script>
                // Los precios se entregan desde el servidor; el total de la pantalla es solo visual
                const precios = @json(['horas' => $tipoHabitacion->precio_hora, 'noches' => $tipoHabitacion->precio_noche]);

                const modalidad = document.getElementById('modalidad');
                const cantidad = document.getElementById('cantidad');
                const etiquetaCantidad = document.getElementById('etiquetaCantidad');
                const fecha = document.querySelector('input[name="fecha"]');
                const hora = document.querySelector('input[name="hora_entrada"]');
                const totalEl = document.getElementById('total');
                const salidaEl = document.getElementById('salida');

                function ajustarLimites() {
                    if (modalidad.value === 'horas') {
                        cantidad.min = 3;
                        cantidad.max = 12;
                        etiquetaCantidad.textContent = 'Horas (de 3 a 12)';
                    } else {
                        cantidad.min = 1;
                        cantidad.max = 30;
                        etiquetaCantidad.textContent = 'Noches (de 1 a 30)';
                    }

                    let n = Number(cantidad.value) || Number(cantidad.min);
                    n = Math.min(Math.max(n, Number(cantidad.min)), Number(cantidad.max));
                    cantidad.value = n;
                }

                function actualizar() {
                    const n = Number(cantidad.value);
                    const unitario = Number(precios[modalidad.value]);

                    totalEl.textContent = (n && unitario)
                        ? '$' + (unitario * n).toLocaleString('es-CO')
                        : '-';

                    if (fecha.value && hora.value && n) {
                        const fin = new Date(fecha.value + 'T' + hora.value);
                        if (modalidad.value === 'horas') {
                            fin.setHours(fin.getHours() + n);
                        } else {
                            fin.setDate(fin.getDate() + n);
                        }
                        salidaEl.textContent = fin.toLocaleString('es-CO', { dateStyle: 'short', timeStyle: 'short' });
                    } else {
                        salidaEl.textContent = '-';
                    }
                }

                modalidad.addEventListener('change', () => { ajustarLimites(); actualizar(); });
                [cantidad, fecha, hora].forEach(el => el.addEventListener('input', actualizar));

                ajustarLimites();
                actualizar();
            </script>
        @else
            <p class="text-muted">Esta habitación todavía no se puede reservar.</p>
        @endif
    @else
        <a href="{{ route('login') }}" class="btn btn-primary">Inicia sesión para reservar</a>
    @endauth
@endsection