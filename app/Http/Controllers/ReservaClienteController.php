<?php

namespace App\Http\Controllers;

use App\Models\Reserva;
use Illuminate\Http\Request;

class ReservaClienteController extends Controller
{
    public function index(Request $request)
    {
        $reservas = Reserva::with('negocio')
            ->where('user_id', auth()->id())
            ->when(! $request->boolean('pasadas'), fn ($q) => $q->where('fecha', '>=', now()->toDateString()))
            ->orderBy('fecha')
            ->orderBy('hora_entrada')
            ->get();

        return view('cliente.reservas.index', compact('reservas'));
    }

    public function marcarPagado(Reserva $reserva)
    {
        abort_unless($reserva->user_id === auth()->id(), 403);

        if ($reserva->pago_estado === 'pendiente') {
            $reserva->update([
                'pago_estado' => 'reportado',
                'pago_reportado_at' => now(),
            ]);
        }

        return back()->with('exito', 'Marcado como pagado. El negocio debe confirmarlo para que quede la reserva lista.');
    }

    public function cancelar(Reserva $reserva)
    {
        abort_unless($reserva->user_id === auth()->id(), 403);

        if ($reserva->estado !== 'cancelada') {
            $reembolsoAplica = null;

            if ($reserva->pago_reportado_at) {
                $reembolsoAplica = $reserva->pago_reportado_at->diffInMinutes(now()) <= 30;
            }

            $reserva->update([
                'estado' => 'cancelada',
                'reembolso_aplica' => $reembolsoAplica,
            ]);

            $mensaje = match (true) {
                $reembolsoAplica === true => 'Reserva cancelada. Aplica reembolso (menos de 30 minutos desde el pago).',
                $reembolsoAplica === false => 'Reserva cancelada. No aplica reembolso (pasaron más de 30 minutos desde el pago).',
                default => 'Reserva cancelada.',
            };

            return back()->with('exito', $mensaje);
        }

        return back()->with('exito', 'Reserva cancelada.');
    }
}