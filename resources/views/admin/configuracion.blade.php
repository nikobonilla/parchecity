@extends('layouts.main')

@section('contenido')
    <h1 class="mb-4">Configuración del sitio</h1>

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

    <form method="POST" action="{{ route('admin.configuracion.update') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label class="form-label d-block">Logo</label>
            @if ($configuracion->logo)
                <img src="{{ asset('storage/' . $configuracion->logo) }}" style="height: 60px;" class="mb-2 d-block">
            @endif
            <input type="file" name="logo" class="form-control" accept="image/*">
        </div>

        <div class="mb-4">
            <label class="form-label d-block">Imagen de fondo</label>
            @if ($configuracion->imagen_fondo)
                <img src="{{ asset('storage/' . $configuracion->imagen_fondo) }}" style="height: 100px; object-fit: cover;" class="mb-2 d-block rounded">
            @endif
            <input type="file" name="imagen_fondo" class="form-control" accept="image/*">
        </div>

        <div class="mb-4">
            <label class="form-label d-block">Imagen destacada 1 (home)</label>
            @if ($configuracion->imagen_home_1)
                <img src="{{ asset('storage/' . $configuracion->imagen_home_1) }}" style="height: 100px; object-fit: cover;" class="mb-2 d-block rounded">
            @endif
            <input type="file" name="imagen_home_1" class="form-control" accept="image/*">
        </div>

        <div class="mb-4">
            <label class="form-label d-block">Imagen destacada 2 (home)</label>
            @if ($configuracion->imagen_home_2)
                <img src="{{ asset('storage/' . $configuracion->imagen_home_2) }}" style="height: 100px; object-fit: cover;" class="mb-2 d-block rounded">
            @endif
            <input type="file" name="imagen_home_2" class="form-control" accept="image/*">
        </div>

        <button type="submit" class="btn btn-primary">Guardar configuración</button>
    </form>
@endsection