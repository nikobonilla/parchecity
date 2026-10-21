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

        $datos = $request->validate([
            'nombre_cliente' => 'required|string|max:255',
            'correo_cliente' => 'required|email|max:255',
            'telefono' => 'required|string|max:20',
            'fecha' => 'required|date_format:Y-m-d|after_or_equal:today',
            'hora_entrada' => 'required|date_format:H:i',
            'hora_salida' => 'required|date_format:H:i|after:hora_entrada',
            'personas' => 'required|integer|between:1,6',
            'comentario' => 'nullable|string|max:500',
            'acepta_politica' => 'accepted',
        ]);

        unset($datos['acepta_politica']);

        $reserva = $this->reservaService->crear($datos, $negocio, auth()->id());

        return redirect()
            ->route('negocios.show', $negocio)
            ->with('exito', "Reserva creada. Valor a pagar: $" . number_format($reserva->valor_reserva, 0, ',', '.') . '. Paga usando el medio del negocio y márcala como pagada desde "Mis reservas".');
    }
}