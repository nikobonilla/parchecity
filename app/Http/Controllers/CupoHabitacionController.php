<?php

namespace App\Http\Controllers;

use App\Models\CupoHabitacion;
use App\Models\TipoHabitacion;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CupoHabitacionController extends Controller
{
    public function index(Request $request, TipoHabitacion $tipoHabitacion)
    {
        abort_unless($tipoHabitacion->negocio->user_id === auth()->id(), 403);

        try {
            $fecha = Carbon::parse($request->query('fecha', now()->toDateString()))->toDateString();
        } catch (\Throwable $e) {
            $fecha = now()->toDateString();
        }

        $cupo = $tipoHabitacion->cupos()->where('fecha', $fecha)->first();

        $reservadas = $tipoHabitacion->reservas()
            ->where('fecha', $fecha)
            ->whereIn('estado', ['pendiente', 'confirmada'])
            ->count();

        return view('dueno.habitaciones.cupos', compact('tipoHabitacion', 'fecha', 'cupo', 'reservadas'));
    }

    public function update(Request $request, TipoHabitacion $tipoHabitacion)
    {
        abort_unless($tipoHabitacion->negocio->user_id === auth()->id(), 403);

        $datos = $request->validate([
            'fecha' => 'required|date_format:Y-m-d|after_or_equal:today',
            'hasta' => 'nullable|date_format:Y-m-d|after_or_equal:fecha',
            'cantidad' => 'required|integer|min:0|max:100',
        ]);

        $inicio = Carbon::parse($datos['fecha']);
        $fin = !empty($datos['hasta']) ? Carbon::parse($datos['hasta']) : $inicio->copy();

        if ($fin->gt($inicio->copy()->addDays(60))) {
            return back()->withInput()->withErrors(['hasta' => 'Máximo 60 días de una vez.']);
        }

        for ($dia = $inicio->copy(); $dia->lte($fin); $dia->addDay()) {
            CupoHabitacion::updateOrCreate(
                ['tipo_habitacion_id' => $tipoHabitacion->id, 'fecha' => $dia->toDateString()],
                ['cantidad' => $datos['cantidad'], 'activo' => $request->boolean('activo')]
            );
        }

        return redirect()
            ->route('dueno.habitaciones.cupos', ['tipoHabitacion' => $tipoHabitacion, 'fecha' => $datos['fecha']])
            ->with('exito', 'Disponibilidad guardada.');
    }
}