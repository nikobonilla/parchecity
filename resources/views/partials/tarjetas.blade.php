<ul class="list-group">
    @forelse ($tarjetas as $tarjeta)
        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <strong>{{ $tarjeta->etiqueta() }}</strong>
                @if ($tarjeta->vencida())
                    <span class="badge bg-danger">Vencida</span>
                @endif
                <div class="small text-muted">
                    {{ $tarjeta->titular }} · vence {{ str_pad($tarjeta->mes, 2, '0', STR_PAD_LEFT) }}/{{ $tarjeta->ano }}
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('cliente.tarjetas.edit', $tarjeta) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                <form method="POST" action="{{ route('cliente.tarjetas.destroy', $tarjeta) }}" onsubmit="return confirm('¿Eliminar esta tarjeta?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                </form>
            </div>
        </li>
    @empty
        <li class="list-group-item text-muted">Todavía no tienes tarjetas guardadas.</li>
    @endforelse
</ul>