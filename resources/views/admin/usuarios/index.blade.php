@extends('layouts.main')

@section('contenido')
    <h1 class="mb-4">Usuarios</h1>

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

    <table class="table align-middle">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Correo</th>
                <th>Rol</th>
                <th>Registrado</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($usuarios as $usuario)
                <tr>
                    <td>{{ $usuario->name }}</td>
                    <td>{{ $usuario->email }}</td>
                    <td>
                        @if ($usuario->id === auth()->id())
                            <span class="badge bg-secondary">{{ ucfirst($usuario->rol) }} (tú)</span>
                        @else
                            <form method="POST" action="{{ route('admin.usuarios.rol', $usuario) }}" class="d-flex gap-2">
                                @csrf
                                @method('PUT')
                                <select name="rol" class="form-select form-select-sm" style="width: auto;">
                                    <option value="cliente" @selected($usuario->rol === 'cliente')>Cliente</option>
                                    <option value="dueño" @selected($usuario->rol === 'dueño')>Dueño</option>
                                    <option value="administrador" @selected($usuario->rol === 'administrador')>Administrador</option>
                                </select>
                                <button class="btn btn-sm btn-outline-primary">Cambiar</button>
                            </form>
                        @endif
                    </td>
                    <td>{{ $usuario->created_at->format('d/m/Y') }}</td>
                    <td>
                        @if ($usuario->id !== auth()->id())
                            <form method="POST" action="{{ route('admin.usuarios.destroy', $usuario) }}" onsubmit="return confirm('¿Eliminar este usuario? Esta acción no se puede deshacer.')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection