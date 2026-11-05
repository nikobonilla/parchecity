<?php

namespace App\Http\Controllers;

use App\Models\BuzonMensaje;
use App\Models\Negocio;
use Illuminate\Http\Request;

class BuzonController extends Controller
{
    public function crear()
    {
        return view('buzon.crear');
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'tipo' => 'required|in:recomendacion,mejora,queja',
            'negocios_texto' => 'nullable|string|max:255',
            'negocios_ids' => 'nullable|string|max:255',
            'mensaje' => 'required|string|min:10|max:2000',
        ], [
            'mensaje.min' => 'Cuéntanos un poco más (mínimo 10 caracteres).',
        ]);

        // Los IDs pueden venir varios, separados por comas o espacios
        $ids = [];
        $noEncontrados = [];

        foreach (preg_split('/[\s,;]+/', (string) ($datos['negocios_ids'] ?? ''), -1, PREG_SPLIT_NO_EMPTY) as $codigo) {
            $id = Negocio::idDesdeCodigo($codigo);

            if ($id && Negocio::whereKey($id)->exists()) {
                $ids[] = $id;
            } else {
                $noEncontrados[] = $codigo;
            }
        }

        if ($noEncontrados) {
            return back()->withInput()->withErrors([
                'negocios_ids' => 'No encontramos estos IDs: ' . implode(', ', $noEncontrados) . '. Revisa que estén bien escritos (ej: NEG-0004).',
            ]);
        }

        $mensaje = BuzonMensaje::create([
            'user_id' => auth()->id(),
            'tipo' => $datos['tipo'],
            'negocios_texto' => $datos['negocios_texto'] ?? null,
            'mensaje' => $datos['mensaje'],
        ]);

        $mensaje->negocios()->sync(array_unique($ids));

        return redirect()->route('buzon.crear')->with('exito', '¡Gracias! Tu mensaje llegó a la administración.');
    }
}