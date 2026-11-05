<?php

namespace App\Http\Controllers;

use App\Mail\ReservaCreada;
use App\Models\Reserva;
use App\Models\Tarjeta;
use App\Support\Correo;
use App\Support\Formato;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PagoController extends Controller
{
    public function formulario(Reserva $reserva)
    {
        abort_unless($reserva->user_id === auth()->id(), 403);

        if ($respuesta = $this->noSePuedePagar($reserva)) {
            return $respuesta;
        }

        $tarjetas = auth()->user()->tarjetas()->latest()->get();

        return view('cliente.reservas.pagar', compact('reserva', 'tarjetas'));
    }

    public function procesar(Request $request, Reserva $reserva)
    {
        abort_unless($reserva->user_id === auth()->id(), 403);

        if ($respuesta = $this->noSePuedePagar($reserva)) {
            return $respuesta;
        }

        $reglas = [
            'tarjeta_id' => 'required',
            'cvv' => 'required|digits_between:3,4',
            'cuotas' => 'nullable|integer|between:1,36',
        ];

        if ($request->tarjeta_id === 'nueva') {
            $reglas = array_merge($reglas, Tarjeta::reglas());
        }

        $datos = $request->validate($reglas, array_merge(Tarjeta::mensajes(), [
            'tarjeta_id.required' => 'Elige una tarjeta o agrega una nueva.',
            'cvv.required' => 'Escribe el CVV (los 3 o 4 números del reverso de la tarjeta).',
            'cvv.digits_between' => 'El CVV debe tener 3 o 4 números.',
        ]));

        if ($request->tarjeta_id === 'nueva') {
            if (Tarjeta::venceEn((int) $datos['mes'], (int) $datos['ano'])->lt(now())) {
                return back()->withInput()->withErrors(['mes' => 'La tarjeta está vencida.']);
            }

            $tipo = $datos['tipo'];
            $ultimos4 = substr(preg_replace('/\D/', '', $datos['numero']), -4);

            if ($request->boolean('guardar')) {
                Tarjeta::crearPara(auth()->user(), $datos);
            }
        } else {
            $tarjeta = auth()->user()->tarjetas()->find($request->tarjeta_id);

            if (! $tarjeta) {
                return back()->withInput()->withErrors(['tarjeta_id' => 'Esa tarjeta no existe.']);
            }

            if ($tarjeta->vencida()) {
                return back()->withInput()->withErrors(['tarjeta_id' => 'Esa tarjeta está vencida. Elimínala o usa otra.']);
            }

            $tipo = $tarjeta->tipo;
            $ultimos4 = $tarjeta->ultimos4;
        }

        // El CVV solo se valida y se descarta: nunca se guarda
        $cuotas = $tipo === 'credito' ? (int) ($datos['cuotas'] ?? 1) : null;
        $codigo = $reserva->codigo ?? $this->generarCodigo();

        $reserva->update([
            'pago_estado' => 'pagado',
            'pagado_at' => now(),
            'estado' => 'confirmada',
            'codigo' => $codigo,
            'metodo_pago' => 'tarjeta_' . $tipo,
            'cuotas' => $cuotas,
            'tarjeta_ultimos4' => $ultimos4,
        ]);

        $reserva->refresh();

        $enviado = Correo::enviar($reserva->correo_cliente, new ReservaCreada($reserva));

        $mensaje = 'Pago aprobado por ' . Formato::cop($reserva->valor_reserva);
        if ($cuotas && $cuotas > 1) {
            $mensaje .= " en {$cuotas} cuotas";
        }
        $mensaje .= ". Tu código es {$codigo}. Tienes hasta las " . Formato::hora($reserva->reembolsoHasta()) . ' para pedir reembolso.';
        if (! $enviado) {
            $mensaje .= ' No pudimos enviar el correo, guarda este código.';
        }

        return redirect()->route('cliente.reservas.index')->with('exito', $mensaje);
    }

    private function noSePuedePagar(Reserva $reserva)
    {
        Reserva::expirarImpagas();
        $reserva->refresh();

        if ($reserva->estado === 'cancelada') {
            return redirect()->route('cliente.reservas.index')
                ->withErrors(['reserva' => 'Esa reserva ya no está activa: se canceló o se venció el tiempo para pagar.']);
        }

        if ($reserva->pago_estado === 'pagado') {
            return redirect()->route('cliente.reservas.index')->with('exito', 'Esa reserva ya está pagada.');
        }

        return null;
    }

    private function generarCodigo(): string
    {
        do {
            $codigo = 'PC-' . strtoupper(Str::random(6));
        } while (Reserva::where('codigo', $codigo)->exists());

        return $codigo;
    }
}