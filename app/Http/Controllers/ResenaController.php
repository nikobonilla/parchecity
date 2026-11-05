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

    // La puede borrar quien la escribió o el administrador. El dueño del negocio no.
    public function destroy(Resena $resena)
    {
        $esAutor = $resena->user_id === auth()->id();
        $esAdmin = auth()->user()->rol === 'administrador';

        abort_unless($esAutor || $esAdmin, 403);

        $resena->delete();

        return back()->with('exito', 'Reseña eliminada.');
    }
}