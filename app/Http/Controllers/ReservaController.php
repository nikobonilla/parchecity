<?php

namespace App\Http\Controllers;

use App\Models\Negocio;
use App\Services\ReservaService;
use Illuminate\Http\Request;

class ReservaController extends Controller
{
    public function __construct(private ReservaService $reservaService) {}

    public function store(Request $request, Negocio $negocio)
    {
        abort_unless($negocio->estado === 'aprobado', 404);

        
     if ($negocio->congelado) {
     return back()->withErrors(['negocio' => 'Este negocio está congelado y no recibe reservas por ahora.']);
}

        $datos = $request->validate([
            'nombre_cliente' => 'required|string|max:255',
            'correo_cliente' => 'required|email|max:255',
            'telefono' => 'required|string|max:20',
            'fecha' => 'required|date_format:Y-m-d|after_or_equal:today',
            'hora_entrada' => 'required|date_format:H:i',
            'personas' => 'required|integer|between:1,6',
            'comentario' => 'nullable|string|max:500',
            'acepta_politica' => 'accepted',
        ], [
            'acepta_politica.accepted' => 'Debes aceptar la política de reembolso para reservar.',
            'fecha.after_or_equal' => 'La fecha no puede ser anterior a hoy.',
        ]);

        unset($datos['acepta_politica']);

        $reserva = $this->reservaService->crear($datos, $negocio, auth()->id());

        return redirect()
            ->route('cliente.reservas.index')
            ->with('exito', 'Reserva creada. Total a pagar: $' . number_format($reserva->valor_reserva, 0, ',', '.') . '. Tienes 30 minutos para pagarla con tarjeta; si no, se cancela sola.');
    }
}