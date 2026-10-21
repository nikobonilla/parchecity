@extends('layouts.main')

@section('contenido')
    <h1 class="mb-4">Todos los negocios</h1>

    <table class="table">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Tipo</th>
                <th>Dueño</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($negocios as $negocio)
                <tr>
                    <td>{{ $negocio->nombre }}</td>
                    <td>{{ ucfirst($negocio->tipo) }}</td>
                    <td>{{ $negocio->duenio->name }}</td>
                    <td>
                        <span class="badge bg-{{ $negocio->estado === 'aprobado' ? 'success' : ($negocio->estado === 'rechazado' ? 'danger' : 'warning') }}">
                            {{ ucfirst($negocio->estado) }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4">No hay negocios registrados.</td></tr>
            @endforelse
        </tbody>
    </table>
@endsection