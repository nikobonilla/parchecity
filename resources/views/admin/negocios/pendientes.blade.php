@extends('layouts.main')

@section('contenido')
    <h1 class="mb-4">Negocios pendientes</h1>

    @if (session('exito'))
        <div class="alert alert-success">{{ session('exito') }}</div>
    @endif

    <table class="table">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Tipo</th>
                <th>Dueño</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($negocios as $negocio)
                <tr>
                    <td>{{ $negocio->nombre }}</td>
                    <td>{{ ucfirst($negocio->tipo) }}</td>
                    <td>{{ $negocio->duenio->name }}</td>
                    <td>
                        <form method="POST" action="{{ route('admin.negocios.aprobar', $negocio) }}" class="d-inline">
                            @csrf
                            <button class="btn btn-sm btn-success">Aprobar</button>
                        </form>
                        <form method="POST" action="{{ route('admin.negocios.rechazar', $negocio) }}" class="d-inline">
                            @csrf
                            <button class="btn btn-sm btn-danger">Rechazar</button>
                        </form>
                        <a href="{{ route('negocios.show', $negocio) }}" class="btn btn-sm btn-outline-secondary">Ver</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4">No hay negocios pendientes.</td></tr>
            @endforelse
        </tbody>
    </table>
@endsection