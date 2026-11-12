@extends('layouts.main')

@section('contenido')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h1 class="mb-1">Buzón de sugerencias</h1><p><a href="{{ route('buzon.mis') }}">Ver mis solicitudes anteriores y sus respuestas</a></p>
            <p class="text-muted">
                Cuéntanos una recomendación, una mejora o una queja. Solo la ve la administración y nos ayuda a tomar mejores decisiones para cada negocio.
            </p>

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

            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('buzon.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">¿Qué quieres enviarnos?</label>
                            <select name="tipo" class="form-select" required>
                                <option value="recomendacion" @selected(old('tipo') === 'recomendacion')>Una recomendación</option>
                                <option value="mejora" @selected(old('tipo') === 'mejora')>Una mejora</option>
                                <option value="queja" @selected(old('tipo') === 'queja')>Una queja</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nombre(s) del negocio(s)</label>
                            <input type="text" name="negocios_texto" class="form-control" placeholder="Ej: Hotel Los Pinos" value="{{ old('negocios_texto') }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">ID(s) del negocio(s)</label>
                            <input type="text" name="negocios_ids" class="form-control" placeholder="Ej: NEG-0004 (si son varios, sepáralos con comas)" value="{{ old('negocios_ids') }}">
                            <div class="form-text">
                                El ID aparece junto al nombre en la página de cada negocio. Si tu mensaje es sobre la plataforma en general, deja el nombre y el ID vacíos.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tu mensaje</label>
                            <textarea name="mensaje" class="form-control" rows="5" required>{{ old('mensaje') }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">Enviar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection