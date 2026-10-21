<?php

namespace App\Http\Controllers;

use App\Models\Negocio;
use App\Models\Tarifa;
use Illuminate\Http\Request;

class TarifaController extends Controller
{
    public function store(Request $request, Negocio $negocio)
    {
        abort_unless($negocio->user_id === auth()->id(), 403);

        $datos = $request->validate([
            'franja_horaria' => 'required|string|max:255',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
            'precio' => 'required|numeric|min:0',
        ]);

        $seCruza = $negocio->tarifas()
            ->where('hora_inicio', '<=', $datos['hora_fin'])
            ->where('hora_fin', '>=', $datos['hora_inicio'])
            ->exists();

        if ($seCruza) {
            return back()
                ->withInput()
                ->withErrors(['hora_inicio' => 'Ese rango de horas se cruza con otra tarifa que ya tienes.']);
        }

        $negocio->tarifas()->create($datos);

        return back()->with('exito', 'Tarifa agregada.');
    }

    public function destroy(Tarifa $tarifa)
    {
        abort_unless($tarifa->negocio->user_id === auth()->id(), 403);
        $tarifa->delete();

        return back()->with('exito', 'Tarifa eliminada.');
    }
}