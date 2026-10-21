<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\CodigoRecuperacion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class PasswordRecoveryController extends Controller
{
    public function formSolicitar()
    {
        return view('auth.olvide-password');
    }

    public function enviarCodigo(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $usuario = User::where('email', $request->email)->first();

        if ($usuario) {
            $codigo = (string) random_int(100000, 999999);

            DB::table('password_reset_tokens')->where('email', $usuario->email)->delete();
            DB::table('password_reset_tokens')->insert([
                'email' => $usuario->email,
                'token' => Hash::make($codigo),
                'created_at' => now(),
            ]);

            Mail::to($usuario->email)->send(new CodigoRecuperacion($codigo));
        }

        return redirect()
            ->route('password.reset', ['email' => $request->email])
            ->with('status', 'Si el correo existe en nuestro sistema, te enviamos un código de 6 dígitos.');
    }

    public function formVerificar(Request $request)
    {
        return view('auth.verificar-codigo', ['email' => $request->query('email')]);
    }

    public function restablecer(Request $request)
    {
        $datos = $request->validate([
            'email' => 'required|email',
            'codigo' => 'required|digits:6',
            'password' => 'required|confirmed|min:8',
        ]);

        $registro = DB::table('password_reset_tokens')->where('email', $datos['email'])->first();

        if (! $registro || ! Hash::check($datos['codigo'], $registro->token)) {
            return back()->withInput()->withErrors(['codigo' => 'El código es incorrecto.']);
        }

        if (now()->diffInMinutes($registro->created_at) > 30) {
            return back()->withInput()->withErrors(['codigo' => 'El código expiró. Solicita uno nuevo.']);
        }

        User::where('email', $datos['email'])->first()->update(['password' => Hash::make($datos['password'])]);

        DB::table('password_reset_tokens')->where('email', $datos['email'])->delete();

        return redirect()->route('login')->with('status', 'Tu contraseña fue actualizada. Ya puedes iniciar sesión.');
    }
}