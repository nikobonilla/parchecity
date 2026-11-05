<?php

namespace App\Services;

use App\Mail\ReembolsoProcesado;
use App\Mail\ReservaCancelada;
use App\Models\Reserva;
use App\Support\Correo;

class CancelacionService
{
    // $quien: 'cliente' o 'negocio'. Devuelve el mensaje para mostrar, o null si ya no estaba activa.
    public function cancelar(Reserva $reserva, string $quien): ?string
    {
        if (! $reserva->estaActiva()) {
            return null;
        }

        $minutos = Reserva::MINUTOS_REEMBOLSO;
        $cambios = ['estado' => 'cancelada'];
        $mensaje = 'Reserva cancelada.';
        $reembolsa = false;

        if ($reserva->pago_estado === 'pagado') {
            if ($quien === 'negocio') {
                // Si cancela el negocio, siempre se devuelve el pago
                $reembolsa = true;
                $mensaje = 'Reserva cancelada. Como ya estaba pagada, se reembolsó al cliente.';
            } elseif ($reserva->puedeReembolsar()) {
                $reembolsa = true;
                $mensaje = "Reserva cancelada. Aplica reembolso: cancelaste dentro de los {$minutos} minutos desde el pago.";
            } else {
                $cambios['reembolso_aplica'] = false;
                $mensaje = "Reserva cancelada. No aplica reembolso: pasaron más de {$minutos} minutos desde el pago.";
            }
        }

        if ($reembolsa) {
            $cambios['pago_estado'] = 'reembolsado';
            $cambios['reembolso_aplica'] = true;
        }

        $reserva->update($cambios);
        $reserva->refresh();

        Correo::enviar($reserva->correo_cliente, new ReservaCancelada($reserva, $quien));

        if ($reembolsa) {
            Correo::enviar($reserva->correo_cliente, new ReembolsoProcesado($reserva));
        }

        return $mensaje;
    }
}