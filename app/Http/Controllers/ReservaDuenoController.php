<?php

namespace App\Http\Controllers;

use App\Mail\ReservaCreada;
use App\Models\Negocio;
use App\Models\Reserva;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ReservaDuenoController extends Controller
{
    public function index(Request $request)
    {
        $negociosIds = Negocio::where('user_id', auth()->id())->pluck('id');

        $reservas = Reserva::with('negocio')
            ->whereIn('negocio_id', $negociosIds)
            ->when(! $request->boolean('pasadas'), fn ($q) => $q->where('fecha', '>=', now()->toDateString()))
            ->orderBy('fecha')
            ->orderBy('hora_entrada')
            ->get();

        return view('dueno.reservas.index', compact('reservas'));
    }

    public function confirmarPago(Reserva $reserva)
    {
        $this->autorizar($reserva);

        if ($reserva->pago_estado !== 'pagado') {
            $reserva->update([
                'pago_estado' => 'pagado',
                'pagado_at' => now(),
                'estado' => 'confirmada',
                'codigo' => $reserva->codigo ?? $this->generarCodigo(),
            ]);

            Mail::to($reserva->correo_cliente)->send(new ReservaCreada($reserva->fresh()));
        }

        return back()->with('exito', 'Pago confirmado. Reserva confirmada y código enviado por correo.');
    }

    public function cancelar(Reserva $reserva)
    {
        $this->autorizar($reserva);

        if ($reserva->estado !== 'cancelada') {
            $reserva->update(['estado' => 'cancelada']);
        }

        return back()->with('exito', 'Reserva cancelada. La mesa quedó liberada.');
    }

    private function generarCodigo(): string
    {
        do {
            $codigo = 'PC-' . strtoupper(Str::random(6));
        } while (Reserva::where('codigo', $codigo)->exists());

        return $codigo;
    }

    private function autorizar(Reserva $reserva): void
    {
        abort_unless($reserva->negocio->user_id === auth()->id(), 403);
    }
}