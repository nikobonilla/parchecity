<x-guest-layout>
    <p class="text-muted small mb-3">
        Escribe tu correo y te enviaremos un código de 6 dígitos para recuperar tu contraseña.
    </p>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label">Correo electrónico</label>
            <input type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus>
            @error('email') <div class="text-danger small">{{ $message }}</div> @enderror
        </div>
        <button type="submit" class="btn btn-primary w-100">Enviar código</button>
    </form>
</x-guest-layout>