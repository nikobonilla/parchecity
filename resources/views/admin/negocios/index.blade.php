@extends('layouts.main')

@section('contenido')
    <h1 class="mb-4">Todos los negocios</h1>

    @if (session('exito'))
        <div class="alert alert-success">{{ session('exito') }}</div>
    @endif

    <form method="GET" action="{{ route('admin.negocios.index') }}" class="row g-2 mb-4">
        <div class="col-md-3">
            <select name="tipo" class="form-select">
                <option value="">Todos los tipos</option>
                @foreach (['restaurante' => 'Restaurantes', 'hotel' => 'Hoteles', 'motel' => 'Moteles'] as $valor => $etiqueta)
                    <option value="{{ $valor }}" @selected(request('tipo') === $valor)>{{ $etiqueta }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-5">
            <input type="text" name="q" class="form-control" placeholder="Buscar por nombre o por ID (ej: NEG-0004)" value="{{ request('q') }}">
        </div>
        <div class="col-md-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary">Filtrar</button>
            <a href="{{ route('admin.negocios.index') }}" class="btn btn-outline-secondary">Limpiar</a>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Tipo</th>
                    <th>Dueño</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($negocios as $negocio)
                    <tr>
                        <td><span class="badge bg-light text-dark border">{{ $negocio->codigo() }}</span></td>
                        <td>{{ $negocio->nombre }}</td>
                        <td>{{ ucfirst($negocio->tipo) }}</td>
                        <td>{{ $negocio->duenio->name ?? '-' }}</td>
                        <td>
                            <span class="badge bg-{{ $negocio->estado === 'aprobado' ? 'success' : ($negocio->estado === 'rechazado' ? 'danger' : 'warning') }}">
                                {{ ucfirst($negocio->estado) }}
                            </span>
                            @if ($negocio->congelado)
                                <span class="badge bg-info text-dark">Congelado ({{ $negocio->congelado_por === 'admin' ? 'admin' : 'dueño' }})</span>
                            @endif
                        </td>
                        <td class="text-nowrap">
                            <a href="{{ route('negocios.show', $negocio) }}" class="btn btn-sm btn-outline-secondary">Ver</a>
                            <a href="{{ route('admin.negocios.editar', $negocio) }}" class="btn btn-sm btn-outline-primary">Editar</a>

                            @if (! $negocio->congelado || $negocio->congelado_por !== 'admin')
                                <form method="POST" action="{{ route('admin.negocios.congelar', $negocio) }}" class="d-inline" onsubmit="return confirm('¿Congelar este negocio? El dueño no podrá descongelarlo.')">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-info">{{ $negocio->congelado ? 'Bloquear (admin)' : 'Congelar' }}</button>
                                </form>
                            @endif

                            @if ($negocio->congelado)
                                <form method="POST" action="{{ route('admin.negocios.descongelar', $negocio) }}" class="d-inline">
                                    @csrf
                                    <button class="btn btn-sm btn-info">Descongelar</button>
                                </form>
                            @endif

                            <form method="POST" action="{{ route('admin.negocios.destroy', $negocio) }}" class="d-inline" onsubmit="return confirm('¿Eliminar este negocio? Se cancelarán sus reservas activas (las pagadas se reembolsan).')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6">No hay negocios con esos filtros.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $negocios->links() }}
@endsection