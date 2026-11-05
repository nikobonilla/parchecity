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

        $datos = $this->validar($request);

        if ($this->seCruza($negocio, $datos)) {
            return back()->withInput()->withErrors(['hora_inicio' => 'Ese rango de horas se cruza con otra tarifa que ya tienes.']);
        }

        $negocio->tarifas()->create($datos);

        return back()->with('exito', 'Tarifa agregada.');
    }

    public function update(Request $request, Tarifa $tarifa)
    {
        abort_unless($tarifa->negocio->user_id === auth()->id(), 403);

        $datos = $this->validar($request);

        if ($this->seCruza($tarifa->negocio, $datos, $tarifa->id)) {
            return back()->withInput()->withErrors(['hora_inicio' => 'Ese rango de horas se cruza con otra tarifa que ya tienes.']);
        }

        $tarifa->update($datos);

        return back()->with('exito', 'Tarifa actualizada.');
    }

    public function destroy(Tarifa $tarifa)
    {
        abort_unless($tarifa->negocio->user_id === auth()->id(), 403);
        $tarifa->delete();

        return back()->with('exito', 'Tarifa eliminada.');
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'franja_horaria' => 'required|string|max:255',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
            'precio' => 'required|numeric|min:1000|max:100000000',
        ], [
            'precio.min' => 'Escribe el valor completo en pesos, por ejemplo 20000 (mínimo 1.000).',
        ]);
    }

    private function seCruza(Negocio $negocio, array $datos, ?int $ignorarId = null): bool
    {
        return $negocio->tarifas()
            ->when($ignorarId, fn ($q) => $q->where('id', '!=', $ignorarId))
            ->where('hora_inicio', '<=', $datos['hora_fin'])
            ->where('hora_fin', '>=', $datos['hora_inicio'])
            ->exists();
    }
}