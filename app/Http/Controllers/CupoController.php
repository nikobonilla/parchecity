<?php

namespace App\Http\Controllers;

use App\Models\Cupo;
use App\Models\Negocio;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CupoController extends Controller
{
    public function index(Request $request, Negocio $negocio)
    {
        abort_unless($negocio->user_id === auth()->id(), 403);

        if ($negocio->tipo !== 'restaurante') {
            return redirect()->route('dueno.habitaciones.index', $negocio);
        }

        try {
            $fecha = Carbon::parse($request->query('fecha', now()->toDateString()))->toDateString();
        } catch (\Throwable $e) {
            $fecha = now()->toDateString();
        }

        $cupos = $negocio->cupos()->where('fecha', $fecha)->get()->keyBy('personas');

        $reservadas = $negocio->reservas()
            ->where('fecha', $fecha)
            ->whereIn('estado', ['pendiente', 'confirmada'])
            ->selectRaw('personas, count(*) as total')
            ->groupBy('personas')
            ->pluck('total', 'personas');

        return view('dueno.cupos.index', compact('negocio', 'fecha', 'cupos', 'reservadas'));
    }

    public function update(Request $request, Negocio $negocio)
    {
        abort_unless($negocio->user_id === auth()->id(), 403);
        abort_if($negocio->tipo !== 'restaurante', 404);

        $datos = $request->validate([
            'fecha' => 'required|date_format:Y-m-d|after_or_equal:today',
            'hasta' => 'nullable|date_format:Y-m-d|after_or_equal:fecha',
            'cantidad' => 'required|array',
            'cantidad.*' => 'nullable|integer|min:0|max:100',
        ]);

        $inicio = Carbon::parse($datos['fecha']);
        $fin = !empty($datos['hasta']) ? Carbon::parse($datos['hasta']) : $inicio->copy();

        if ($fin->gt($inicio->copy()->addDays(60))) {
            return back()->withInput()->withErrors(['hasta' => 'Máximo puedes aplicar 60 días de una vez.']);
        }

        for ($dia = $inicio->copy(); $dia->lte($fin); $dia->addDay()) {
            foreach (range(1, 6) as $personas) {
                Cupo::updateOrCreate(
                    [
                        'negocio_id' => $negocio->id,
                        'fecha' => $dia->toDateString(),
                        'personas' => $personas,
                    ],
                    [
                        'cantidad' => (int) ($datos['cantidad'][$personas] ?? 0),
                        'activo' => $request->boolean("activo.$personas"),
                    ]
                );
            }
        }

        return redirect()
            ->route('dueno.cupos.index', ['negocio' => $negocio, 'fecha' => $datos['fecha']])
            ->with('exito', 'Disponibilidad guardada.');
    }
}