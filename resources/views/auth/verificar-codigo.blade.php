<x-guest-layout>
    <p class="text-muted small mb-3">
        Ingresa el código de 6 dígitos que te llegó por correo y tu nueva contraseña.
    </p>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        <div class="mb-3">
            <label class="form-label">Correo electrónico</label>
            <input type="email" name="email" class="form-control" value="{{ old('email', $email) }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Código de 6 dígitos</label>
            <input type="text" name="codigo" class="form-control" maxlength="6" required autofocus>
        </div>

        <div class="mb-3">
            <label class="form-label">Nueva contraseña</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Confirmar contraseña</label>
            <input type="password" name="password_confirmation" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary w-100">Restablecer contraseña</button>
    </form>

    <p class="text-center small mt-3">
        <a href="{{ route('password.request') }}">¿No te llegó el código? Solicitar de nuevo</a>
    </p>
</x-guest-layout>