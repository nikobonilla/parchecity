@extends('layouts.main')

@section('contenido')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Mis negocios</h1>
        <a href="{{ route('dueno.negocios.create') }}" class="btn btn-primary">+ Registrar nuevo negocio</a>
    </div>

    @if (session('exito'))
        <div class="alert alert-success">{{ session('exito') }}</div>
    @endif

    <table class="table">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Tipo</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($negocios as $negocio)
                <tr>
                    <td>{{ $negocio->nombre }}</td>
                    <td>{{ ucfirst($negocio->tipo) }}</td>
                    <td>
                        <span class="badge bg-{{ $negocio->estado === 'aprobado' ? 'success' : ($negocio->estado === 'rechazado' ? 'danger' : 'warning') }}">
                            {{ ucfirst($negocio->estado) }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('negocios.show', $negocio) }}" class="btn btn-sm btn-outline-secondary">Ver perfil</a>
                        <a href="{{ route('dueno.negocios.edit', $negocio) }}" class="btn btn-sm btn-outline-primary">Editar</a>

                        @if ($negocio->tipo === 'restaurante')
                            <a href="{{ route('dueno.cupos.index', $negocio) }}" class="btn btn-sm btn-outline-success">Disponibilidad</a>
                        @else
                            <a href="{{ route('dueno.habitaciones.index', $negocio) }}" class="btn btn-sm btn-outline-success">Habitaciones</a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="4">Todavía no has registrado ningún negocio.</td></tr>
            @endforelse
        </tbody>
    </table>
@endsection