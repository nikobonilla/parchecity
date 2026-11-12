@extends('layouts.main')

@section('contenido')
    <a href="{{ route('admin.negocios.index') }}" class="btn btn-link mb-3">&larr; Volver a todos los negocios</a>

    <h1 class="mb-1">Negocios eliminados</h1>
    <p class="text-muted">Están ocultos de la página, pero guardados. Si los restauras, vuelven a aparecer.</p>

    @if (session('exito'))
        <div class="alert alert-success">{{ session('exito') }}</div>
    @endif

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Tipo</th>
                    <th>Dueño</th>
                    <th>Eliminado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($negocios as $negocio)
                    <tr>
                        <td><span class="badge bg-light text-dark border">{{ $negocio->codigo() }}</span></td>
                        <td>{{ $negocio->nombre }}</td>
                        <td>{{ ucfirst($negocio->tipo) }}</td>
                        <td>{{ $negocio->duenio->name ?? '-' }}</td>
                        <td>{{ $negocio->deleted_at->format('d/m/Y') }} @hora($negocio->deleted_at)</td>
                        <td>
                            <form method="POST" action="{{ route('admin.negocios.restaurar', $negocio->id) }}" onsubmit="return confirm('¿Restaurar este negocio?')">
                                @csrf
                                <button class="btn btn-sm btn-success">Restaurar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6">No hay negocios eliminados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $negocios->links() }}
@endsection