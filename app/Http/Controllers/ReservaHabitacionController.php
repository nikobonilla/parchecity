<?php

namespace App\Http\Controllers;

use App\Models\Negocio;
use App\Models\TipoHabitacion;
use App\Services\ReservaService;
use Illuminate\Http\Request;

class ReservaHabitacionController extends Controller
{
    public function __construct(private ReservaService $reservaService) {}

    public function store(Request $request, Negocio $negocio, TipoHabitacion $tipoHabitacion)
    {
        abort_unless($negocio->estado === 'aprobado', 404);
        abort_unless($tipoHabitacion->negocio_id === $negocio->id, 404);

        
     if ($negocio->congelado) {
     return back()->withErrors(['negocio' => 'Este negocio está congelado y no recibe reservas por ahora.']);
}

        $datos = $request->validate([
            'nombre_cliente' => 'required|string|max:255',
            'correo_cliente' => 'required|email|max:255',
            'telefono' => 'required|string|max:20',
            'modalidad' => 'required|in:horas,noches',
            'fecha' => 'required|date_format:Y-m-d|after_or_equal:today',
            'hora_entrada' => 'required|date_format:H:i',
            'cantidad' => 'required|integer|min:1|max:30',
            'personas' => 'nullable|integer|min:1|max:20',
            'comentario' => 'nullable|string|max:500',
            'acepta_politica' => 'accepted',
        ], [
            'acepta_politica.accepted' => 'Debes aceptar la política de reembolso para reservar.',
            'fecha.after_or_equal' => 'La fecha no puede ser anterior a hoy.',
            'cantidad.required' => 'Indica cuántas horas o noches quieres.',
        ]);

        unset($datos['acepta_politica']);

        $reserva = $this->reservaService->crearHabitacion($datos, $negocio, $tipoHabitacion, auth()->id());

        return redirect()
            ->route('cliente.reservas.index')
            ->with('exito', 'Reserva creada. Total a pagar: $' . number_format($reserva->valor_reserva, 0, ',', '.') . '. Tienes 30 minutos para pagarla con tarjeta desde Mis reservas; si no, se cancela sola.');
    }
}