@if ($negocio->tipo === 'restaurante' && $negocio->menus->isNotEmpty())
    <hr class="my-4">

    <h3 class="mb-3">Menú</h3>

    <div class="row g-3">
        @foreach ($negocio->menus as $menu)
            <div class="col-md-4">
                <div class="card h-100">
                    @if ($menu->tipo === 'imagen')
                        <a href="{{ asset('storage/' . $menu->ruta) }}" target="_blank">
                            <img src="{{ asset('storage/' . $menu->ruta) }}" class="card-img-top" style="height: 200px; object-fit: cover;">
                        </a>
                    @else
                        <div class="card-body text-center py-5"><div class="display-4">PDF</div></div>
                    @endif
                    <div class="card-body">
                        <h6>{{ $menu->titulo ?: ($menu->tipo === 'pdf' ? 'Carta (PDF)' : 'Carta') }}</h6>
                        <a href="{{ asset('storage/' . $menu->ruta) }}" target="_blank" class="btn btn-sm btn-outline-primary">Ver</a>
                        <a href="{{ asset('storage/' . $menu->ruta) }}" download class="btn btn-sm btn-primary">Descargar</a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif