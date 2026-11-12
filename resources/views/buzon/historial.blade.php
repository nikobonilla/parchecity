@extends('layouts.main')

@section('contenido')
    <a href="{{ route('buzon.mis') }}" class="btn btn-link mb-3">&larr; Volver a mis solicitudes</a>

    <h1 class="mb-1">Historial de solicitudes</h1>
    <p class="text-muted">Todos tus mensajes, incluso los que eliminaste de tu lista. Este historial no se puede borrar.</p>

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

    <form method="GET" action="{{ route('buzon.historial') }}" class="row g-2 mb-4">
        <div class="col-md-2">
            <select name="tipo" class="form-select">
                <option value="">Todos los tipos</option>
                <option value="recomendacion" @selected(request('tipo') === 'recomendacion')>Recomendaciones</option>
                <option value="mejora" @selected(request('tipo') === 'mejora')>Mejoras</option>
                <option value="queja" @selected(request('tipo') === 'queja')>Quejas</option>
            </select>
        </div>
        <div class="col-md-2">
            <select name="estado" class="form-select">
                <option value="">Todos los estados</option>
                <option value="nuevo" @selected(request('estado') === 'nuevo')>Recibidas</option>
                <option value="leido" @selected(request('estado') === 'leido')>En revisión</option>
                <option value="resuelto" @selected(request('estado') === 'resuelto')>Resueltas</option>
            </select>
        </div>
        <div class="col-md-2"><input type="date" name="desde" class="form-control" value="{{ request('desde') }}"></div>
        <div class="col-md-2"><input type="date" name="hasta" class="form-control" value="{{ request('hasta') }}"></div>
        <div class="col-md-4 d-flex gap-2">
            <input type="text" name="q" class="form-control" placeholder="Texto, negocio o ID (NEG-0004)" value="{{ request('q') }}">
            <button class="btn btn-primary">Filtrar</button>
            <a href="{{ route('buzon.historial') }}" class="btn btn-outline-secondary">Limpiar</a>
        </div>
    </form>

    @forelse ($mensajes as $m)
        @php
            $colorTipo = ['recomendacion' => 'success', 'mejora' => 'primary', 'queja' => 'danger'][$m->tipo];
            $etiquetaTipo = ['recomendacion' => 'Recomendación', 'mejora' => 'Mejora', 'queja' => 'Queja'][$m->tipo];
            $etiquetaEstado = ['nuevo' => 'Recibida', 'leido' => 'En revisión', 'resuelto' => 'Resuelta'][$m->estado];
            $colorEstado = ['nuevo' => 'secondary', 'leido' => 'warning', 'resuelto' => 'success'][$m->estado];
        @endphp

        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between flex-wrap gap-2 mb-2">
                    <div>
                        <span class="badge bg-{{ $colorTipo }}">{{ $etiquetaTipo }}</span>
                        <span class="badge bg-{{ $colorEstado }}">{{ $etiquetaEstado }}</span>
                        @if ($m->oculto_usuario_at)
                            <span class="badge bg-secondary">Eliminada de tu lista</span>
                        @endif
                    </div>
                    <small class="text-muted">{{ $m->created_at->format('d/m/Y') }} @hora($m->created_at)</small>
                </div>

                @if ($m->negocios->isNotEmpty())
                    <div class="small mb-2">
                        <strong>Negocios:</strong>
                        @foreach ($m->negocios as $n)
                            {{ $n->nombre }}@if ($n->trashed()) <span class="text-muted">(eliminado)</span>@endif
                            <span class="badge bg-light text-dark border">{{ $n->codigo() }}</span>
                        @endforeach
                    </div>
                @endif

                <p class="mb-3" style="white-space: pre-line;">{{ $m->mensaje }}</p>

                @if ($m->respuesta)
                    <div class="border-start border-3 border-success ps-3 mb-3">
                        <div class="small text-muted">
                            Respuesta de la administración · {{ $m->respondido_at->format('d/m/Y') }} @hora($m->respondido_at)
                        </div>
                        <div style="white-space: pre-line;">{{ $m->respuesta }}</div>
                    </div>
                @endif

                @if ($m->oculto_usuario_at)
                    <form method="POST" action="{{ route('buzon.restaurar', $m) }}">
                        @csrf
                        <button class="btn btn-sm btn-outline-secondary">Devolver a mis solicitudes</button>
                    </form>
                @endif
            </div>
        </div>
    @empty
        <p class="text-muted">No hay mensajes con esos filtros.</p>
    @endforelse

    {{ $mensajes->links() }}
@endsection