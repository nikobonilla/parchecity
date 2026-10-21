@extends('layouts.main')

@section('contenido')
    <a href="{{ route('dueno.negocios.edit', $negocio) }}" class="btn btn-link mb-3">&larr; Volver a editar negocio</a>

    <h1 class="mb-1">Habitaciones</h1>
    <p class="text-muted">{{ $negocio->nombre }}</p>

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

    <div class="card mb-4">
        <div class="card-body">
            <h5>Categorías</h5>

            @if ($categorias->isNotEmpty())
                <div class="d-flex flex-wrap gap-2 mb-3">
                    @foreach ($categorias as $categoria)
                        <span class="badge bg-secondary d-inline-flex align-items-center gap-2 fs-6 fw-normal">
                            {{ $categoria->nombre }} ({{ $categoria->tipos_habitacion_count }})
                            @if ($categoria->tipos_habitacion_count === 0)
                                <form method="POST" action="{{ route('dueno.categorias.destroy', $categoria) }}" class="d-inline" onsubmit="return confirm('¿Eliminar esta categoría?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm p-0 text-white border-0 bg-transparent" title="Eliminar categoría">×</button>
                                </form>
                            @endif
                        </span>
                    @endforeach
                </div>
            @else
                <p class="text-muted small">Todavía no tienes categorías. Crea la primera para poder agregar habitaciones.</p>
            @endif

            <form method="POST" action="{{ route('dueno.categorias.store', $negocio) }}" class="row g-2">
                @csrf
                <div class="col-md-5">
                    <input type="text" name="nombre_categoria" class="form-control" placeholder="Nueva categoría (ej: Sencilla, Suite, VIP, Temática)" required>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-outline-primary w-100">Guardar categoría</button>
                </div>
            </form>
        </div>
    </div>

    @forelse ($tipos as $nombreCategoria => $grupo)
        <h5 class="mt-4 mb-2">{{ $nombreCategoria }}</h5>

        @foreach ($grupo as $tipo)
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                        <div>
                            <h5 class="mb-1">{{ $tipo->nombre }}</h5>
                            <div class="mb-1">
                                @if ($tipo->precio_hora)
                                    <span class="badge bg-primary">Por hora: ${{ number_format($tipo->precio_hora, 0, ',', '.') }} (de 3 a 12 horas)</span>
                                @endif
                                @if ($tipo->precio_noche)
                                    <span class="badge bg-success">Por noche: ${{ number_format($tipo->precio_noche, 0, ',', '.') }}</span>
                                @endif
                            </div>
                            <small class="text-muted">
                                {{ $tipo->capacidad ? 'Máximo ' . $tipo->capacidad . ' personas' : 'Sin máximo de personas' }}
                            </small>
                            @if ($tipo->especificaciones)
                                <p class="mb-0 mt-1">{{ $tipo->especificaciones }}</p>
                            @endif
                        </div>

                        <div class="d-flex gap-2">
                            <a href="{{ route('dueno.habitaciones.cupos', $tipo) }}" class="btn btn-sm btn-outline-success">Disponibilidad</a>
                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#editar{{ $tipo->id }}">Editar</button>
                            <form method="POST" action="{{ route('dueno.habitaciones.destroy', $tipo) }}" onsubmit="return confirm('¿Eliminar esta habitación?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                            </form>
                        </div>
                    </div>

                    @if ($tipo->imagenes->isNotEmpty())
                        <div class="d-flex gap-2 flex-wrap mt-3">
                            @foreach ($tipo->imagenes as $imagen)
                                <div class="position-relative">
                                    <img src="{{ asset('storage/' . $imagen->ruta) }}" style="width: 80px; height: 80px; object-fit: cover;" class="rounded">
                                    <form method="POST" action="{{ route('dueno.imagenesHabitacion.destroy', $imagen) }}" class="position-absolute top-0 end-0" onsubmit="return confirm('¿Eliminar esta imagen?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger py-0 px-1">×</button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="collapse mt-3" id="editar{{ $tipo->id }}">
                        <form method="POST" action="{{ route('dueno.habitaciones.update', $tipo) }}" enctype="multipart/form-data" class="row g-2">
                            @csrf
                            @method('PUT')
                            <div class="col-md-4">
                                <label class="form-label small">Nombre de la habitación</label>
                                <input type="text" name="nombre" class="form-control form-control-sm" value="{{ $tipo->nombre }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small">Categoría</label>
                                <select name="categoria_id" class="form-select form-select-sm" required>
                                    <option value="">Elige una categoría...</option>
                                    @foreach ($categorias as $categoria)
                                        <option value="{{ $categoria->id }}" @selected($tipo->categoria_id === $categoria->id)>{{ $categoria->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small">Máx. personas</label>
                                <input type="number" name="capacidad" class="form-control form-control-sm" min="1" max="20" value="{{ $tipo->capacidad }}" placeholder="Sin máximo">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small">Precio por hora</label>
                                <input type="number" name="precio_hora" class="form-control form-control-sm" min="1" value="{{ $tipo->precio_hora ? (int) $tipo->precio_hora : '' }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small">Precio por noche</label>
                                <input type="number" name="precio_noche" class="form-control form-control-sm" min="1" value="{{ $tipo->precio_noche ? (int) $tipo->precio_noche : '' }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small">Especificaciones</label>
                                <input type="text" name="especificaciones" class="form-control form-control-sm" value="{{ $tipo->especificaciones }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label small">Agregar imágenes (máx. 5 MB cada una)</label>
                                <input type="file" name="imagenes[]" class="form-control form-control-sm" multiple accept="image/*">
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-sm btn-primary">Guardar cambios</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    @empty
        <p class="text-muted">Todavía no has creado habitaciones.</p>
    @endforelse

    @if ($categorias->isNotEmpty())
        <div class="card mt-4">
            <div class="card-body">
                <h5>Nueva habitación</h5>
                <p class="text-muted small mb-3">
                    Pon precio por hora, por noche, o los dos. Si dejas vacío el máximo de personas, no se le pregunta al cliente cuántos van.
                </p>
                <form method="POST" action="{{ route('dueno.habitaciones.store', $negocio) }}" enctype="multipart/form-data" class="row g-2">
                    @csrf
                    <div class="col-md-4">
                        <label class="form-label small">Nombre de la habitación</label>
                        <input type="text" name="nombre" class="form-control" placeholder="Ej: Suite Jacuzzi Rosa" value="{{ old('nombre') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">Categoría</label>
                        <select name="categoria_id" class="form-select" required>
                            <option value="">Elige una categoría...</option>
                            @foreach ($categorias as $categoria)
                                <option value="{{ $categoria->id }}" @selected(old('categoria_id') == $categoria->id)>{{ $categoria->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">Máx. personas</label>
                        <input type="number" name="capacidad" class="form-control" min="1" max="20" placeholder="Sin máximo" value="{{ old('capacidad') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">Precio por hora</label>
                        <input type="number" name="precio_hora" class="form-control" min="1" value="{{ old('precio_hora') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">Precio por noche</label>
                        <input type="number" name="precio_noche" class="form-control" min="1" value="{{ old('precio_noche') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">Especificaciones (opcional)</label>
                        <input type="text" name="especificaciones" class="form-control" placeholder="Ej: jacuzzi, cama king" value="{{ old('especificaciones') }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label small">Imágenes de la habitación (máx. 5 MB cada una)</label>
                        <input type="file" name="imagenes[]" class="form-control" multiple accept="image/*">
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Crear habitación</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endsection