@extends('layouts.main')

@section('contenido')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Editar negocio</h1>
        @if ($negocio->tipo === 'restaurante')
            <a href="{{ route('dueno.cupos.index', $negocio) }}" class="btn btn-outline-primary">Disponibilidad por fecha</a>
        @else
            <a href="{{ route('dueno.habitaciones.index', $negocio) }}" class="btn btn-outline-primary">Habitaciones</a>
        @endif
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('exito'))
        <div class="alert alert-success">{{ session('exito') }}</div>
    @endif

    <div class="mb-4">
        <label class="form-label">Imágenes actuales</label>
        <div class="d-flex gap-2 flex-wrap">
            @foreach ($negocio->imagenes as $imagen)
                <div class="position-relative">
                    <img src="{{ asset('storage/' . $imagen->ruta) }}" style="width: 100px; height: 100px; object-fit: cover;" class="rounded">
                    <form method="POST" action="{{ route('dueno.imagenes.destroy', $imagen) }}" class="position-absolute top-0 end-0" onsubmit="return confirm('¿Eliminar esta imagen?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-danger py-0 px-1">×</button>
                    </form>
                </div>
            @endforeach
        </div>
    </div>

    <form method="POST" action="{{ route('dueno.negocios.update', $negocio) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">Nombre</label>
            <input type="text" name="nombre" class="form-control" value="{{ old('nombre', $negocio->nombre) }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Tipo</label>
            <select name="tipo" class="form-select" required>
                <option value="restaurante" @selected($negocio->tipo === 'restaurante')>Restaurante</option>
                <option value="hotel" @selected($negocio->tipo === 'hotel')>Hotel</option>
                <option value="motel" @selected($negocio->tipo === 'motel')>Motel</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Dirección</label>
            <input type="text" name="direccion" class="form-control" value="{{ old('direccion', $negocio->direccion) }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Ubicación / Ciudad</label>
            <input type="text" name="ubicacion" class="form-control" value="{{ old('ubicacion', $negocio->ubicacion) }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Descripción</label>
            <textarea name="descripcion" class="form-control" rows="4" required>{{ old('descripcion', $negocio->descripcion) }}</textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Contacto</label>
            <input type="text" name="contacto" class="form-control" value="{{ old('contacto', $negocio->contacto) }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">WhatsApp</label>
            <input type="text" name="whatsapp" class="form-control" value="{{ old('whatsapp', $negocio->whatsapp) }}">
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label">Hora de apertura</label>
                <input type="time" name="hora_apertura" class="form-control" value="{{ old('hora_apertura', $negocio->hora_apertura ? substr($negocio->hora_apertura, 0, 5) : '') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Hora de cierre</label>
                <input type="time" name="hora_cierre" class="form-control" value="{{ old('hora_cierre', $negocio->hora_cierre ? substr($negocio->hora_cierre, 0, 5) : '') }}">
            </div>
            <div class="col-md-4">
                @if ($negocio->tipo === 'restaurante')
                    <label class="form-label">Tiempo que se ocupa una mesa (minutos)</label>
                @else
                    <label class="form-label">Margen de limpieza entre huéspedes (minutos)</label>
                @endif
                <input type="number" name="minutos_liberacion" class="form-control" min="10" max="1440" value="{{ old('minutos_liberacion', $negocio->minutos_liberacion ?? 90) }}" required>
                <div class="form-text">
                    @if ($negocio->tipo === 'restaurante')
                        Ej: 90 o 120. Pasado ese tiempo desde la hora de entrada, la mesa se libera sola.
                    @else
                        Ej: 40. Cada habitación debe quedar libre este tiempo antes de la siguiente entrada para poder prepararla.
                    @endif
                </div>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Agregar más imágenes</label>
            <input type="file" name="imagenes[]" class="form-control" multiple accept="image/*">
        </div>

        <button type="submit" class="btn btn-primary">Guardar cambios</button>
    </form>

    @if ($negocio->tipo === 'restaurante')
        <hr class="my-4">

        <h3>Tarifas por horario</h3>
        <p class="text-muted">El sistema aplica automáticamente la tarifa que corresponde a la hora de entrada de cada reserva. Es un valor fijo para esa franja: no se suma por horas.</p>

        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Desde</th>
                    <th>Hasta</th>
                    <th>Valor</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($negocio->tarifas->sortBy('hora_inicio') as $tarifa)
                    <tr>
                        <td>{{ $tarifa->franja_horaria }}</td>
                        <td>@hora($tarifa->hora_inicio)</td>
                        <td>@hora($tarifa->hora_fin)</td>
                        <td>@cop($tarifa->precio)</td>
                        <td class="text-nowrap">
                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#editarTarifa{{ $tarifa->id }}">Editar</button>
                            <form method="POST" action="{{ route('dueno.tarifas.destroy', $tarifa) }}" class="d-inline" onsubmit="return confirm('¿Eliminar esta tarifa?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                    <tr class="collapse" id="editarTarifa{{ $tarifa->id }}">
                        <td colspan="5" class="bg-light">
                            <form method="POST" action="{{ route('dueno.tarifas.update', $tarifa) }}" class="row g-2">
                                @csrf
                                @method('PUT')
                                <div class="col-md-3">
                                    <input type="text" name="franja_horaria" class="form-control form-control-sm" value="{{ $tarifa->franja_horaria }}" required>
                                </div>
                                <div class="col-md-2">
                                    <input type="time" name="hora_inicio" class="form-control form-control-sm" value="{{ substr($tarifa->hora_inicio, 0, 5) }}" required>
                                </div>
                                <div class="col-md-2">
                                    <input type="time" name="hora_fin" class="form-control form-control-sm" value="{{ substr($tarifa->hora_fin, 0, 5) }}" required>
                                </div>
                                <div class="col-md-3">
                                    <input type="number" name="precio" class="form-control form-control-sm" min="1000" value="{{ (int) $tarifa->precio }}" required>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-sm btn-primary w-100">Guardar</button>
                                </div>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5">Todavía no has agregado tarifas.</td></tr>
                @endforelse
            </tbody>
        </table>

        <h6>Agregar tarifa</h6>
        <form method="POST" action="{{ route('dueno.tarifas.store', $negocio) }}" class="row g-2">
            @csrf
            <div class="col-md-3">
                <input type="text" name="franja_horaria" class="form-control" placeholder="Nombre (ej: Mañana)" value="{{ old('franja_horaria') }}" required>
            </div>
            <div class="col-md-2">
                <input type="time" name="hora_inicio" class="form-control" value="{{ old('hora_inicio') }}" required>
            </div>
            <div class="col-md-2">
                <input type="time" name="hora_fin" class="form-control" value="{{ old('hora_fin') }}" required>
            </div>
            <div class="col-md-3">
                <input type="number" name="precio" class="form-control" placeholder="Valor en pesos (ej: 20000)" min="1000" value="{{ old('precio') }}" required>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Agregar</button>
            </div>
        </form>
    @endif
@endsection