@extends('layouts.main')

@section('contenido')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <h1 class="mb-0">Mis solicitudes</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('buzon.historial') }}" class="btn btn-outline-dark">Historial</a>
            <a href="{{ route('buzon.crear') }}" class="btn btn-primary">Enviar un mensaje nuevo</a>
        </div>
    </div>

    <p class="text-muted">Aquí ves tus mensajes al buzón y las respuestas de la administración. Si eliminas uno de esta lista, sigue guardado en tu historial.</p>

    @if (session('exito'))
        <div class="alert alert-success">{{ session('exito') }}</div>
    @endif

    <div class="d-flex gap-2 mb-4">
        <a href="{{ route('buzon.mis') }}" class="btn btn-sm {{ request('estado') ? 'btn-outline-dark' : 'btn-dark' }}">Todas</a>
        <a href="{{ route('buzon.mis', ['estado' => 'pendientes']) }}" class="btn btn-sm {{ request('estado') === 'pendientes' ? 'btn-dark' : 'btn-outline-dark' }}">Pendientes</a>
        <a href="{{ route('buzon.mis', ['estado' => 'resueltas']) }}" class="btn btn-sm {{ request('estado') === 'resueltas' ? 'btn-dark' : 'btn-outline-dark' }}">Resueltas</a>
    </div>

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
                    </div>
                    <small class="text-muted">{{ $m->created_at->format('d/m/Y') }} @hora($m->created_at)</small>
                </div>

                @if ($m->negocios->isNotEmpty())
                    <div class="small mb-2">
                        <strong>Negocios:</strong>
                        @foreach ($m->negocios as $n)
                            {{ $n->nombre }} <span class="badge bg-light text-dark border">{{ $n->codigo() }}</span>
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
                @else
                    <div class="small text-muted mb-3">Todavía no hay respuesta. Te avisaremos con una notificación cuando la haya.</div>
                @endif

                <form method="POST" action="{{ route('buzon.eliminar', $m) }}" onsubmit="return confirm('¿Eliminar de tu lista? Seguirá guardado en tu historial.')">
                    @csrf
                    <button class="btn btn-sm btn-outline-danger">Eliminar de mi lista</button>
                </form>
            </div>
        </div>
    @empty
        <p class="text-muted">No tienes solicitudes {{ request('estado') ? 'en esta sección' : 'todavía' }}.</p>
    @endforelse

    {{ $mensajes->links() }}
@endsection