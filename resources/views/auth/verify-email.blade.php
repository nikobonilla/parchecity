<x-guest-layout>
    <p class="text-muted small mb-3">
        Gracias por registrarte. Antes de continuar, verifica tu correo con el enlace que te enviamos. Si no te llegó, podemos enviarte otro.
    </p>

    @if (session('status') == 'verification-link-sent')
        <div class="alert alert-success">Te enviamos un nuevo enlace de verificación.</div>
    @endif

    <div class="d-flex justify-content-between align-items-center">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="btn btn-primary">Reenviar correo de verificación</button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-link">Cerrar sesión</button>
        </form>
    </div>
</x-guest-layout>