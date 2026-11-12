@extends('layouts.main')

@section('contenido')
    <h1 class="mb-3">Estadísticas de la plataforma</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="GET" action="{{ route('admin.estadisticas') }}" class="card card-body mb-4">
        <div class="row g-2 mb-2">
            <div class="col-md-3">
                <label class="form-label small">Tipo de establecimiento</label>
                <select name="tipo" class="form-select">
                    <option value="">Todos</option>
                    <option value="restaurante" @selected(request('tipo') === 'restaurante')>Restaurantes</option>
                    <option value="hotel" @selected(request('tipo') === 'hotel')>Hoteles</option>
                    <option value="motel" @selected(request('tipo') === 'motel')>Moteles</option>
                </select>
            </div>
            <div class="col-md-5">
                <label class="form-label small">Negocio (nombre o ID)</label>
                <input type="text" name="q" class="form-control" placeholder="Ej: Los Pinos o NEG-0004" value="{{ request('q') }}">
            </div>
        </div>

        @include('partials.selector-ubicacion', ['filtro' => true])

        <div class="mt-3">
            @include('partials.estadisticas-fechas')
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Ver estadísticas</button>
            <a href="{{ route('admin.estadisticas') }}" class="btn btn-outline-secondary">Limpiar</a>
        </div>
    </form>

    @if ($hayFiltroNegocio)
        <div class="alert alert-info small">Las reservas e ingresos de abajo están filtrados por negocio. Los datos de "Toda la plataforma" no se filtran por negocio.</div>
    @endif

    @include('partials.estadisticas-resultados')

    <hr class="my-4">

    <h3 class="mb-3">Toda la plataforma</h3>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card h-100"><div class="card-body">
                <h6>Usuarios</h6>
                <table class="table table-sm mb-2">
                    <tbody>
                        @foreach (['cliente' => 'Clientes', 'dueño' => 'Dueños', 'administrador' => 'Administradores'] as $rol => $etiqueta)
                            <tr><td>{{ $etiqueta }}</td><td class="text-end"><strong>{{ $plataforma['usuarios_por_rol'][$rol] ?? 0 }}</strong></td></tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="small text-muted">Nuevos en el rango: <strong>{{ $plataforma['usuarios_nuevos'] }}</strong></div>
            </div></div>
        </div>

        <div class="col-md-4">
            <div class="card h-100"><div class="card-body">
                <h6>Negocios</h6>
                <table class="table table-sm mb-2">
                    <tbody>
                        @foreach (['aprobado' => 'Aprobados', 'pendiente' => 'Pendientes', 'rechazado' => 'Rechazados'] as $estado => $etiqueta)
                            <tr><td>{{ $etiqueta }}</td><td class="text-end"><strong>{{ $plataforma['negocios_por_estado'][$estado] ?? 0 }}</strong></td></tr>
                        @endforeach
                        @foreach (['restaurante' => 'Restaurantes', 'hotel' => 'Hoteles', 'motel' => 'Moteles'] as $tipo => $etiqueta)
                            <tr class="text-muted"><td>{{ $etiqueta }}</td><td class="text-end">{{ $plataforma['negocios_por_tipo'][$tipo] ?? 0 }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="small text-muted">
                    Congelados: <strong>{{ $plataforma['negocios_congelados'] }}</strong> ·
                    Eliminados (ocultos): <strong>{{ $plataforma['negocios_eliminados'] }}</strong>
                </div>
            </div></div>
        </div>

        <div class="col-md-4">
            <div class="card h-100"><div class="card-body">
                <h6>Buzón y reseñas</h6>
                <table class="table table-sm mb-2">
                    <tbody>
                        <tr><td>Mensajes nuevos</td><td class="text-end"><strong>{{ $plataforma['buzon_por_estado']['nuevo'] ?? 0 }}</strong></td></tr>
                        <tr><td>En revisión</td><td class="text-end"><strong>{{ $plataforma['buzon_por_estado']['leido'] ?? 0 }}</strong></td></tr>
                        <tr><td>Resueltos</td><td class="text-end"><strong>{{ $plataforma['buzon_por_estado']['resuelto'] ?? 0 }}</strong></td></tr>
                        <tr><td>Sin respuesta</td><td class="text-end"><strong>{{ $plataforma['buzon_sin_responder'] }}</strong></td></tr>
                    </tbody>
                </table>
                <div class="small text-muted">
                    Reseñas: <strong>{{ $plataforma['resenas']->total }}</strong>
                    @if ($plataforma['resenas']->total > 0)
                        · promedio <strong>{{ number_format($plataforma['resenas']->promedio, 1) }}</strong> de 5
                    @endif
                </div>
            </div></div>
        </div>

        <div class="col-md-6">
            <div class="card h-100"><div class="card-body">
                <h6>Reservas e ingresos por tipo de negocio</h6>
                <table class="table table-sm mb-0">
                    <tbody>
                        @php $porTipo = $plataforma['por_tipo']->keyBy('tipo'); @endphp
                        @foreach (['restaurante' => 'Restaurantes', 'hotel' => 'Hoteles', 'motel' => 'Moteles'] as $tipo => $etiqueta)
                            <tr>
                                <td>{{ $etiqueta }}</td>
                                <td class="text-end"><strong>{{ $porTipo[$tipo]->total ?? 0 }}</strong></td>
                                <td class="text-end text-muted">@cop($porTipo[$tipo]->ingresos ?? 0)</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div></div>
        </div>

        <div class="col-md-6">
            <div class="card h-100"><div class="card-body">
                <h6>Municipios con más reservas</h6>
                <table class="table table-sm mb-0">
                    <tbody>
                        @forelse ($plataforma['por_municipio'] as $m)
                            <tr>
                                <td>{{ \App\Support\Formato::lugar($m->nombre) }}</td>
                                <td class="text-end"><strong>{{ $m->total }}</strong></td>
                                <td class="text-end text-muted">@cop($m->ingresos)</td>
                            </tr>
                        @empty
                            <tr><td class="text-muted">Sin datos en el rango (los negocios necesitan tener municipio).</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div></div>
        </div>

        <div class="col-md-6">
            <div class="card h-100"><div class="card-body">
                <h6>Negocios más guardados en favoritos</h6>
                <table class="table table-sm mb-0">
                    <tbody>
                        @forelse ($plataforma['favoritos_top'] as $f)
                            <tr>
                                <td>{{ $f->nombre }}</td>
                                <td class="text-end"><strong>{{ $f->total }}</strong></td>
                            </tr>
                        @empty
                            <tr><td class="text-muted">Todavía no hay favoritos.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div></div>
        </div>
    </div>
@endsection