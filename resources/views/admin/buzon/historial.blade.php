@extends('layouts.main')

@section('contenido')
    <a href="{{ route('admin.buzon.index') }}" class="btn btn-link mb-3">&larr; Volver al buzón</a>

    <h1 class="mb-1">Historial del buzón</h1>
    <p class="text-muted">Todos los mensajes, incluso los resueltos y los que quitaste del buzón.</p>

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

    <form method="GET" action="{{ route('admin.buzon.historial') }}" class="row g-2 mb-4">
        <div class="col-md-2">
            <select name="tipo_mensaje" class="form-select">
                <option value="">Todos los tipos</option>
                <option value="recomendacion" @selected(request('tipo_mensaje') === 'recomendacion')>Recomendaciones</option>
                <option value="mejora" @selected(request('tipo_mensaje') === 'mejora')>Mejoras</option>
                <option value="queja" @selected(request('tipo_mensaje') === 'queja')>Quejas</option>
            </select>
        </div>
        <div class="col-md-2">
            <select name="estado" class="form-select">
                <option value="">Todos los estados</option>
                <option value="nuevo" @selected(request('estado') === 'nuevo')>Nuevos</option>
                <option value="leido" @selected(request('estado') === 'leido')>En revisión</option>
                <option value="resuelto" @selected(request('estado') === 'resuelto')>Resueltos</option>
            </select>
        </div>
        <div class="col-md-2"><input type="date" name="desde" class="form-control" value="{{ request('desde') }}"></div>
        <div class="col-md-2"><input type="date" name="hasta" class="form-control" value="{{ request('hasta') }}"></div>
        <div class="col-md-4 d-flex gap-2">
            <input type="text" name="q" class="form-control" placeholder="Texto, usuario o ID (NEG-0004)" value="{{ request('q') }}">
            <button class="btn btn-primary">Filtrar</button>
            <a href="{{ route('admin.buzon.historial') }}" class="btn btn-outline-secondary">Limpiar</a>
        </div>
    </form>

    @forelse ($mensajes as $m)
        @php
            $colorTipo = ['recomendacion' => 'success', 'mejora' => 'primary', 'queja' => 'danger'][$m->tipo];
            $etiquetaTipo = ['recomendacion' => 'Recomendación', 'mejora' => 'Mejora', 'queja' => 'Queja'][$m->tipo];
            $etiquetaEstado = ['nuevo' => 'Nuevo', 'leido' => 'En revisión', 'resuelto' => 'Resuelto'][$m->estado];
            $colorEstado = ['nuevo' => 'danger', 'leido' => 'warning', 'resuelto' => 'success'][$m->estado];
        @endphp

        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between flex-wrap gap-2 mb-2">
                    <div>
                        <span class="badge bg-{{ $colorTipo }}">{{ $etiquetaTipo }}</span>
                        <span class="badge bg-{{ $colorEstado }}">{{ $etiquetaEstado }}</span>
                        @if ($m->archivado_at)
                            <span class="badge bg-secondary">Quitado del buzón</span>
                        @endif
                    </div>
                    <small class="text-muted">{{ $m->created_at->format('d/m/Y') }} @hora($m->created_at)</small>
                </div>

                <div class="small mb-2">
                    <strong>De:</strong> {{ $m->user->name ?? 'Usuario eliminado' }}
                    @if ($m->user) <span class="text-muted">({{ $m->user->email }})</span> @endif
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

                @include('partials.buzon-respuesta')

                @if ($m->archivado_at)
                    <div class="d-flex align-items-center gap-2 small text-muted">
                        Quitado el {{ $m->archivado_at->format('d/m/Y') }} @hora($m->archivado_at)
                        <form method="POST" action="{{ route('admin.buzon.restaurar', $m) }}">
                            @csrf
                            <button class="btn btn-sm btn-outline-secondary">Devolver al buzón</button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    @empty
        <p class="text-muted">No hay mensajes con esos filtros.</p>
    @endforelse

    {{ $mensajes->links() }}
@endsection