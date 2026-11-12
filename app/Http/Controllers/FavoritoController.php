<?php

namespace App\Http\Controllers;

use App\Models\Negocio;

class FavoritoController extends Controller
{
    public function index()
    {
        $negocios = auth()->user()->favoritos()
            ->with('imagenes')
            ->orderByDesc('favoritos.created_at')
            ->paginate(12);

        return view('favoritos.index', compact('negocios'));
    }

    public function store(Negocio $negocio)
    {
        abort_unless($negocio->estado === 'aprobado', 404);

        auth()->user()->favoritos()->syncWithoutDetaching([$negocio->id]);

        return back()->with('exito', "Guardaste {$negocio->nombre} en tus favoritos.");
    }

    public function destroy(Negocio $negocio)
    {
        auth()->user()->favoritos()->detach($negocio->id);

        return back()->with('exito', "Quitaste {$negocio->nombre} de tus favoritos.");
    }
}