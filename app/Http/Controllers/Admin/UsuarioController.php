<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UsuarioController extends Controller
{
    public function index()
    {
        $usuarios = User::orderBy('name')->get();
        return view('admin.usuarios.index', compact('usuarios'));
    }

    public function actualizarRol(Request $request, User $usuario)
    {
        abort_if($usuario->id === auth()->id(), 403, 'No puedes cambiar tu propio rol.');

        $request->validate([
            'rol' => 'required|in:cliente,dueño,administrador',
        ]);

        $usuario->update(['rol' => $request->rol]);

        return back()->with('exito', 'Rol actualizado.');
    }

    public function destroy(User $usuario)
    {
        abort_if($usuario->id === auth()->id(), 403, 'No puedes eliminar tu propia cuenta desde aquí.');

        $usuario->delete();

        return back()->with('exito', 'Usuario eliminado.');
    }
}