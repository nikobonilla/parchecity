<x-guest-layout>
    <p class="text-muted small mb-3">
        Esta es una zona segura. Confirma tu contraseña para continuar.
    </p>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <div class="mb-3">
            <label for="password" class="form-label">Contraseña</label>
            <input id="password" type="password" name="password" class="form-control" required autocomplete="current-password">
            @error('password') <div class="text-danger small">{{ $message }}</div> @enderror
        </div>

        <button type="submit" class="btn btn-primary w-100">Confirmar</button>
    </form>
</x-guest-layout>