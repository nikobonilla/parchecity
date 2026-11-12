  @extends('layouts.main')
  
  @section('contenido')
     <h1 class="h3 mb-4">Mi perfil</h1>

    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Información de la cuenta</h5>
                    <p class="text-muted small">Actualiza tu nombre y correo electrónico.</p>

                    @if (session('status') === 'profile-updated')
                        <div class="alert alert-success">Perfil actualizado.</div>
                    @endif

                    <form method="POST" action="{{ route('profile.update') }}">
                        @csrf
                        @method('PATCH')

                        <div class="mb-3">
                            <label class="form-label">Nombre</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                            @error('name') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Correo electrónico</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                            @error('email') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>

                        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                            <p class="small">
                                Tu correo no está verificado.
                                <button form="send-verification" class="btn btn-link btn-sm p-0">Reenviar verificación</button>
                            </p>
                        @endif

                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </form>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Cambiar contraseña</h5>
                    <p class="text-muted small">Usa una contraseña larga y segura.</p>

                    <form method="POST" action="{{ route('password.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Contraseña actual</label>
                            <input type="password" name="current_password" class="form-control">
                            @error('current_password', 'updatePassword') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nueva contraseña</label>
                            <input type="password" name="password" class="form-control">
                            <div class="form-text">Mínimo 12 caracteres, con mayúscula, minúscula, número y símbolo.</div>
                            @error('password', 'updatePassword') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Confirmar nueva contraseña</label>
                            <input type="password" name="password_confirmation" class="form-control">
                        </div>

                        <button type="submit" class="btn btn-primary">Actualizar contraseña</button>
                    </form>
                </div>
            </div>

            <div class="card border-danger">
                <div class="card-body">
                    <h5 class="card-title text-danger">Eliminar cuenta</h5>
                    <p class="text-muted small">Una vez eliminada tu cuenta, todos tus datos se perderán permanentemente.</p>

                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#eliminarCuentaModal">
                        Eliminar cuenta
                    </button>

                    <div class="modal fade" id="eliminarCuentaModal" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="POST" action="{{ route('profile.destroy') }}">
                                    @csrf
                                    @method('DELETE')
                                    <div class="modal-header">
                                        <h5 class="modal-title">¿Eliminar tu cuenta?</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p>Esta acción no se puede deshacer. Ingresa tu contraseña para confirmar.</p>
                                        <input type="password" name="password" class="form-control" placeholder="Contraseña" required>
                                        @error('password', 'userDeletion') <div class="text-danger small">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                        <button type="submit" class="btn btn-danger">Eliminar cuenta</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
    <form id="send-verification" method="POST" action="{{ route('verification.send') }}" class="d-none">
        @csrf
    </form>
@endif
@endsection