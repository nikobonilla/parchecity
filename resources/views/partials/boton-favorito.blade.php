@php $pequeno = $pequeno ?? false; @endphp

@guest
    <a href="{{ route('login') }}" class="btn {{ $pequeno ? 'btn-sm' : '' }} btn-outline-danger" title="Inicia sesión para guardar">♡{{ $pequeno ? '' : ' Guardar' }}</a>
@else
    @php
        $esFavorito = isset($favoritosIds)
            ? in_array($negocio->id, $favoritosIds)
            : auth()->user()->favoritos()->where('negocios.id', $negocio->id)->exists();
    @endphp

    @if ($esFavorito)
        <form method="POST" action="{{ route('favoritos.destroy', $negocio) }}" class="d-inline">
            @csrf
            @method('DELETE')
            <button class="btn {{ $pequeno ? 'btn-sm' : '' }} btn-danger" title="Quitar de favoritos">♥{{ $pequeno ? '' : ' Guardado' }}</button>
        </form>
    @else
        <form method="POST" action="{{ route('favoritos.store', $negocio) }}" class="d-inline">
            @csrf
            <button class="btn {{ $pequeno ? 'btn-sm' : '' }} btn-outline-danger" title="Guardar en favoritos">♡{{ $pequeno ? '' : ' Guardar' }}</button>
        </form>
    @endif
@endguest