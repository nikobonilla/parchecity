@if ($m->respuesta)
    <div class="border-start border-3 border-success ps-3 mb-3">
        <div class="small text-muted">
            Respuesta de {{ $m->respondidoPor->name ?? 'la administración' }} · {{ $m->respondido_at->format('d/m/Y') }} @hora($m->respondido_at)
        </div>
        <div style="white-space: pre-line;">{{ $m->respuesta }}</div>
    </div>
@endif

<details class="mb-3">
    <summary class="btn btn-sm btn-outline-primary">{{ $m->respuesta ? 'Editar respuesta' : 'Responder' }}</summary>

    <form method="POST" action="{{ route('admin.buzon.responder', $m) }}" class="mt-2">
        @csrf
        @method('PUT')
        <textarea name="respuesta" class="form-control mb-2" rows="3" required placeholder="Ej: Gracias por avisarnos. Vamos a tomar medidas con el negocio.">{{ $m->respuesta }}</textarea>
        <div class="form-check mb-2">
            <input type="checkbox" name="resuelto" value="1" class="form-check-input" id="resuelto{{ $m->id }}" checked>
            <label class="form-check-label" for="resuelto{{ $m->id }}">Marcar como resuelta</label>
        </div>
        <button class="btn btn-sm btn-primary">Enviar respuesta</button>
    </form>
</details>