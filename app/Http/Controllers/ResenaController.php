<?php

namespace App\Http\Controllers;

use App\Models\Negocio;
use App\Models\Resena;
use Illuminate\Http\Request;

class ResenaController extends Controller
{
    public function store(Request $request, Negocio $negocio)
    {
        $tieneReservaConfirmada = $negocio->reservas()
            ->where('user_id', auth()->id())
            ->where('estado', 'confirmada')
            ->exists();

        abort_unless($tieneReservaConfirmada, 403, 'Solo puedes dejar reseña si tuviste una reserva confirmada en este negocio.');

        $datos = $request->validate([
            'calificacion' => 'required|integer|between:1,5',
            'comentario' => 'nullable|string|max:500',
        ]);

        Resena::updateOrCreate(
            ['negocio_id' => $negocio->id, 'user_id' => auth()->id()],
            $datos
        );

        return back()->with('exito', '¡Gracias por tu reseña!');
    }

    public function destroy(Resena $resena)
    {
        abort_unless($resena->user_id === auth()->id(), 403);
        $resena->delete();

        return back()->with('exito', 'Reseña eliminada.');
    }
}