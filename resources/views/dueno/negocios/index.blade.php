@extends('layouts.main')

@section('contenido')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <h1>Mis negocios</h1>
        <div class="d-flex gap-2 flex-wrap">
            @include('partials.boton-soporte')
            <a href="{{ route('dueno.negocios.create') }}" class="btn btn-primary">+ Registrar nuevo negocio</a>
        </div>
    </div>

    @if (session('exito'))
        <div class="alert alert-success">{{ session('exito') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Tipo</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($negocios as $negocio)
                    <tr>
                        <td><span class="badge bg-light text-dark border">{{ $negocio->codigo() }}</span></td>
                        <td>{{ $negocio->nombre }}</td>
                        <td>{{ ucfirst($negocio->tipo) }}</td>
                        <td>
                            <span class="badge bg-{{ $negocio->estado === 'aprobado' ? 'success' : ($negocio->estado === 'rechazado' ? 'danger' : 'warning') }}">
                                {{ ucfirst($negocio->estado) }}
                            </span>
                            @if ($negocio->congelado)
                                <span class="badge bg-info text-dark">
                                    {{ $negocio->congelado_por === 'admin' ? 'Congelado por la administración' : 'Congelado' }}
                                </span>
                            @endif
                        </td>
                        <td class="text-nowrap">
                            <a href="{{ route('negocios.show', $negocio) }}" class="btn btn-sm btn-outline-secondary">Ver perfil</a>
                            <a href="{{ route('dueno.negocios.edit', $negocio) }}" class="btn btn-sm btn-outline-primary">Editar</a>

                            @if ($negocio->tipo === 'restaurante')
                                <a href="{{ route('dueno.cupos.index', $negocio) }}" class="btn btn-sm btn-outline-success">Disponibilidad</a>
                            @else
                                <a href="{{ route('dueno.habitaciones.index', $negocio) }}" class="btn btn-sm btn-outline-success">Habitaciones</a>
                            @endif

                            @if (! $negocio->congelado)
                                <form method="POST" action="{{ route('dueno.negocios.congelar', $negocio) }}" class="d-inline" onsubmit="return confirm('¿Congelar este negocio? Seguirá visible pero no recibirá reservas nuevas.')">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-info">Congelar</button>
                                </form>
                            @elseif ($negocio->congelado_por === 'dueno')
                                <form method="POST" action="{{ route('dueno.negocios.descongelar', $negocio) }}" class="d-inline">
                                    @csrf
                                    <button class="btn btn-sm btn-info">Descongelar</button>
                                </form>
                            @endif

                            <form method="POST" action="{{ route('dueno.negocios.destroy', $negocio) }}" class="d-inline" onsubmit="return confirm('¿Eliminar este negocio? Se cancelarán sus reservas activas (las pagadas se reembolsan). Esta acción no se puede deshacer desde la página.')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5">Todavía no has registrado ningún negocio.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection