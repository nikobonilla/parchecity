<?php

namespace App\Http\Controllers;

use App\Mail\ReservaCreada;
use App\Models\Reserva;
use App\Rules\TarjetaValida;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PagoController extends Controller
{
    public function formulario(Reserva $reserva)
    {
        abort_unless($reserva->user_id === auth()->id(), 403);
        abort_if($reserva->pago_estado === 'pagado', 404);

        return view('cliente.reservas.pagar', compact('reserva'));
    }

    public function procesar(Request $request, Reserva $reserva)
    {
        abort_unless($reserva->user_id === auth()->id(), 403);
        abort_if($reserva->pago_estado === 'pagado', 404);

        $request->validate([
            'numero_tarjeta' => ['required', new TarjetaValida],
            'nombre_titular' => 'required|string|max:255',
            'mes_vencimiento' => 'required|integer|between:1,12',
            'ano_vencimiento' => 'required|integer|min:' . date('y'),
            'cvv' => 'required|digits_between:3,4',
        ]);

        $vencimiento = sprintf('20%02d-%02d-01', $request->ano_vencimiento, $request->mes_vencimiento);
        if ($vencimiento < now()->format('Y-m-01')) {
            return back()->withInput()->withErrors(['mes_vencimiento' => 'La tarjeta está vencida.']);
        }

        $codigo = $reserva->codigo ?? $this->generarCodigo();

        $reserva->update([
            'pago_estado' => 'pagado',
            'pagado_at' => now(),
            'estado' => 'confirmada',
            'codigo' => $codigo,
            'metodo_pago' => 'tarjeta',
        ]);

        Mail::to($reserva->correo_cliente)->send(new ReservaCreada($reserva->fresh()));

        return redirect()
            ->route('cliente.reservas.index')
            ->with('exito', "Pago aprobado. Tu código es {$codigo}. Te enviamos los detalles por correo.");
    }

    private function generarCodigo(): string
    {
        do {
            $codigo = 'PC-' . strtoupper(Str::random(6));
        } while (Reserva::where('codigo', $codigo)->exists());

        return $codigo;
    }
}