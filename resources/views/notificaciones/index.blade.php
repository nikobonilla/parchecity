@extends('layouts.main')

@section('contenido')
    @php
        $categorias = [
            'reserva' => 'Reservas', 'pago' => 'Pagos', 'cancelacion' => 'Cancelaciones', 'negocio' => 'Negocios',
            'buzon' => 'Buzón', 'resena' => 'Reseñas',
        ];
        $colores = ['info' => 'primary', 'success' => 'success', 'warning' => 'warning', 'danger' => 'danger'];
    @endphp

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <h1 class="mb-0">Notificaciones</h1>
        <div class="d-flex gap-2">
            <form method="POST" action="{{ route('notificaciones.leerTodas') }}">
                @csrf
                <button class="btn btn-outline-primary">Marcar todas como leídas</button>
            </form>
            <form method="POST" action="{{ route('notificaciones.limpiar') }}" onsubmit="return confirm('¿Borrar las notificaciones ya leídas?')">
                @csrf
                @method('DELETE')
                <button class="btn btn-outline-secondary">Borrar las leídas</button>
            </form>
        </div>
    </div>

    @if (session('exito'))
        <div class="alert alert-success">{{ session('exito') }}</div>
    @endif

    <form method="GET" action="{{ route('notificaciones.index') }}" class="row g-2 mb-4">
        <div class="col-md-3">
            <select name="categoria" class="form-select">
                <option value="">Todas las categorías</option>
                @foreach ($categorias as $clave => $etiqueta)
                    <option value="{{ $clave }}" @selected(request('categoria') === $clave)>{{ $etiqueta }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 d-flex align-items-center">
            <div class="form-check">
                <input type="checkbox" name="no_leidas" value="1" class="form-check-input" id="soloNoLeidas" @checked(request()->boolean('no_leidas'))>
                <label class="form-check-label" for="soloNoLeidas">Solo las no leídas</label>
            </div>
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button class="btn btn-primary">Filtrar</button>
            <a href="{{ route('notificaciones.index') }}" class="btn btn-outline-secondary">Limpiar</a>
        </div>
    </form>

    <div class="list-group mb-3">
        @forelse ($notificaciones as $n)
            @php
                $nivel = $colores[$n->data['nivel'] ?? 'info'] ?? 'primary';
                $categoria = $categorias[$n->data['categoria'] ?? ''] ?? 'Aviso';
            @endphp
            <div class="list-group-item d-flex justify-content-between align-items-start gap-3 {{ $n->read_at ? '' : 'bg-light' }}">
                <div>
                    <div>
                        <span class="badge bg-{{ $nivel }}">{{ $categoria }}</span>
                        <strong>{{ $n->data['titulo'] ?? 'Aviso' }}</strong>
                        @unless ($n->read_at)
                            <span class="badge bg-danger">Nueva</span>
                        @endunless
                    </div>
                    <div>{{ $n->data['mensaje'] ?? '' }}</div>
                    <small class="text-muted">{{ $n->created_at->format('d/m/Y') }} @hora($n->created_at)</small>
                </div>
                <div class="d-flex gap-2 text-nowrap">
                    <a href="{{ route('notificaciones.abrir', $n->id) }}" class="btn btn-sm btn-outline-primary">Abrir</a>
                    <form method="POST" action="{{ route('notificaciones.destroy', $n->id) }}">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger">Borrar</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="list-group-item text-muted">No tienes notificaciones.</div>
        @endforelse
    </div>

    {{ $notificaciones->links() }}
@endsection