<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificacionController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'categoria' => 'nullable|in:reserva,pago,cancelacion,negocio,buzon,resena,usuario',
            'no_leidas' => 'nullable|boolean',
        ]);

        $notificaciones = auth()->user()->notifications()
            ->when($request->filled('categoria'), fn ($q) => $q->where('data->categoria', $request->categoria))
            ->when($request->boolean('no_leidas'), fn ($q) => $q->whereNull('read_at'))
            ->paginate(20)
            ->withQueryString();

        return view('notificaciones.index', compact('notificaciones'));
    }

    // La marca como leída y lleva a donde ocurrió lo que avisa
    public function abrir(string $id)
    {
        $notificacion = auth()->user()->notifications()->findOrFail($id);
        $notificacion->markAsRead();

        $destino = $notificacion->data['url'] ?? null;

        return $destino ? redirect($destino) : redirect()->route('notificaciones.index');
    }

    public function leerTodas()
    {
        auth()->user()->unreadNotifications->markAsRead();

        return back()->with('exito', 'Marcaste todas las notificaciones como leídas.');
    }

    public function destroy(string $id)
    {
        auth()->user()->notifications()->findOrFail($id)->delete();

        return back();
    }

    public function limpiar()
    {
        auth()->user()->notifications()->whereNotNull('read_at')->delete();

        return back()->with('exito', 'Se borraron las notificaciones leídas.');
    }
}