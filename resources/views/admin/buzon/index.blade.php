@extends('layouts.main')

@section('contenido')
    <div class="d-flex justify-content-between align-items-center mb-3"><h1 class="mb-0">Buzón</h1><a href="{{ route('admin.buzon.historial') }}" class="btn btn-outline-secondary">Ver historial completo</a></div>
    @if (session('exito'))
        <div class="alert alert-success">{{ session('exito') }}</div>
    @endif

    <form method="GET" action="{{ route('admin.buzon.index') }}" class="row g-2 mb-4">
        <div class="col-md-3">
            <select name="tipo_mensaje" class="form-select">
                <option value="">Todos los tipos</option>
                <option value="recomendacion" @selected(request('tipo_mensaje') === 'recomendacion')>Recomendaciones</option>
                <option value="mejora" @selected(request('tipo_mensaje') === 'mejora')>Mejoras</option>
                <option value="queja" @selected(request('tipo_mensaje') === 'queja')>Quejas</option>
            </select>
        </div>
        <div class="col-md-3">
            <select name="estado" class="form-select">
                <option value="">Todos los estados</option>
                <option value="nuevo" @selected(request('estado') === 'nuevo')>Nuevos</option>
                <option value="leido" @selected(request('estado') === 'leido')>Leídos</option>
                <option value="resuelto" @selected(request('estado') === 'resuelto')>Resueltos</option>
            </select>
        </div>
        <div class="col-md-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary">Filtrar</button>
            <a href="{{ route('admin.buzon.index') }}" class="btn btn-outline-secondary">Limpiar</a>
        </div>
    </form>

    <ul class="nav nav-tabs mb-3" role="tablist">
        @foreach ($secciones as $clave => $seccion)
            @php $nuevos = $seccion['mensajes']->where('estado', 'nuevo')->count(); @endphp
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $loop->first ? 'active' : '' }}" type="button" data-bs-toggle="tab" data-bs-target="#tab-{{ $clave }}">
                    {{ $seccion['titulo'] }}
                    <span class="badge bg-secondary">{{ $seccion['mensajes']->count() }}</span>
                    @if ($nuevos > 0)
                        <span class="badge bg-danger">{{ $nuevos }} {{ $nuevos === 1 ? 'nuevo' : 'nuevos' }}</span>
                    @endif
                </button>
            </li>
        @endforeach
    </ul>

    <div class="tab-content">
        @foreach ($secciones as $clave => $seccion)
            <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="tab-{{ $clave }}">
                @forelse ($seccion['mensajes'] as $m)
                    @php
                        $colorTipo = ['recomendacion' => 'success', 'mejora' => 'primary', 'queja' => 'danger'][$m->tipo];
                        $etiquetaTipo = ['recomendacion' => 'Recomendación', 'mejora' => 'Mejora', 'queja' => 'Queja'][$m->tipo];
                        $colorEstado = ['nuevo' => 'danger', 'leido' => 'warning', 'resuelto' => 'success'][$m->estado];
                    @endphp
                    <div class="card mb-3 {{ $m->estado === 'nuevo' ? 'border-danger' : '' }}">
                        <div class="card-body">
                            <div class="d-flex justify-content-between flex-wrap gap-2 mb-2">
                                <div>
                                    <span class="badge bg-{{ $colorTipo }}">{{ $etiquetaTipo }}</span>
                                    <span class="badge bg-{{ $colorEstado }}">{{ ucfirst($m->estado) }}</span>
                                </div>
                                <small class="text-muted">{{ $m->created_at->format('d/m/Y') }} @hora($m->created_at)</small>
                            </div>

                            <div class="small mb-2">
                                <strong>De:</strong> {{ $m->user->name ?? 'Usuario eliminado' }}
                                @if ($m->user)
                                    <span class="text-muted">({{ $m->user->email }})</span>
                                @endif
                            </div>

                            @if ($m->negocios->isNotEmpty() || $m->negocios_texto)
                                <div class="small mb-2">
                                    <strong>Negocios:</strong>
                                    @foreach ($m->negocios as $n)
                                        @if ($n->trashed())
                                            {{ $n->nombre }} <span class="text-muted">(eliminado)</span>
                                        @else
                                            <a href="{{ route('negocios.show', $n) }}">{{ $n->nombre }}</a>
                                        @endif
                                        <span class="badge bg-light text-dark border">{{ $n->codigo() }}</span>
                                    @endforeach
                                    @if ($m->negocios_texto)
                                        <span class="text-muted">· Escribió: "{{ $m->negocios_texto }}"</span>
                                    @endif
                                </div>
                            @endif

                            <p class="mb-3" style="white-space: pre-line;">{{ $m->mensaje }}</p>@include('partials.buzon-respuesta')

                            <div class="d-flex gap-2 flex-wrap">
                                @if ($m->estado !== 'leido')
                                    <form method="POST" action="{{ route('admin.buzon.estado', $m) }}">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="estado" value="leido">
                                        <button class="btn btn-sm btn-outline-warning">Marcar leído</button>
                                    </form>
                                @endif
                                @if ($m->estado !== 'resuelto')
                                    <form method="POST" action="{{ route('admin.buzon.estado', $m) }}">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="estado" value="resuelto">
                                        <button class="btn btn-sm btn-outline-success">Marcar resuelto</button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route('admin.buzon.destroy', $m) }}" onsubmit="return confirm('¿Quitar este mensaje del buzón? Seguirá en el historial.')">
                                    @csrf
                                    @method('DELETE')
                                   <button class="btn btn-sm btn-outline-danger">Quitar del buzón</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-muted">No hay mensajes en esta sección.</p>
                @endforelse
            </div>
        @endforeach
    </div>

    <script>
        // Recuerda la pestaña abierta cuando la página se recarga
        document.querySelectorAll('[data-bs-toggle="tab"]').forEach(function (boton) {
            boton.addEventListener('shown.bs.tab', function () {
                sessionStorage.setItem('buzonTab', boton.dataset.bsTarget);
            });
        });

        const guardada = sessionStorage.getItem('buzonTab');
        if (guardada) {
            const boton = document.querySelector('[data-bs-target="' + guardada + '"]');
            if (boton) { bootstrap.Tab.getOrCreateInstance(boton).show(); }
        }
    </script>
@endsection