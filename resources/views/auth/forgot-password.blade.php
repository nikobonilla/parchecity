<x-guest-layout>
    <p class="text-muted small mb-3">
        ¿Olvidaste tu contraseña? No hay problema. Déjanos tu correo y te enviaremos un link para crear una nueva.
    </p>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label">Correo electrónico</label>
            <input id="email" type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus>
            @error('email') <div class="text-danger small">{{ $message }}</div> @enderror
        </div>

        <button type="submit" class="btn btn-primary w-100">Enviar link de recuperación</button>
    </form>
</x-guest-layout>