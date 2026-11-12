<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="mb-3">
            <label for="name" class="form-label">Nombre</label>
            <input id="name" type="text" name="name" class="form-control" value="{{ old('name') }}" required autofocus>
            @error('name') <div class="text-danger small">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Correo electrónico</label>
            <input id="email" type="email" name="email" class="form-control" value="{{ old('email') }}" required>
            @error('email') <div class="text-danger small">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Contraseña</label>
            <input id="password" type="password" name="password" class="form-control" required>
            <div class="form-text">Mínimo 12 caracteres, con mayúscula, minúscula, número y símbolo.</div>
            @error('password') <div class="text-danger small">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label for="password_confirmation" class="form-label">Confirmar contraseña</label>
            <input id="password_confirmation" type="password" name="password_confirmation" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="rol" class="form-label">Tipo de cuenta</label>
            <select id="rol" name="rol" class="form-select" required>
                <option value="cliente">Cliente</option>
                <option value="dueño">Dueño de negocio</option>
            </select>
            @error('rol') <div class="text-danger small">{{ $message }}</div> @enderror
        </div>
        <div class="form-check mb-3">
    <input type="checkbox" name="acepta_terminos" class="form-check-input" id="acepta_terminos" required>
    <label class="form-check-label small" for="acepta_terminos">
        He leído y acepto los <a href="{{ route('terminos') }}" target="_blank">términos y condiciones</a> de ParcheCity.
    </label>
</div>
        <div class="d-flex justify-content-between align-items-center">
            <a href="{{ route('login') }}" class="small">¿Ya tienes cuenta?</a>
            <button type="submit" class="btn btn-primary">Registrarse</button>
        </div>
    </form>
</x-guest-layout>