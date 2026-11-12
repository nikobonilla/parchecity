<?php

namespace App\Observers;

use App\Models\Reserva;
use App\Support\Avisos;
use App\Support\Formato;
use Carbon\Carbon;

class ReservaObserver
{
    public function created(Reserva $reserva): void
    {
        $negocio = $reserva->negocio;

        Avisos::a($negocio?->duenio, 'reserva', 'Nueva reserva',
            "{$reserva->nombre_cliente} reservó en {$negocio->nombre}: {$this->detalle($reserva)}. Está pendiente de pago.",
            route('dueno.reservas.index'));
    }

    public function updated(Reserva $reserva): void
    {
        $negocio = $reserva->negocio;
        $actor = auth()->id();
        $detalle = $this->detalle($reserva);

        if ($reserva->wasChanged('estado') && $reserva->estado === 'cancelada') {
            $reembolso = $reserva->pago_estado === 'reembolsado' ? ' Se reembolsó el pago.' : '';

            if ($actor && $actor === $reserva->user_id) {
                Avisos::a($negocio?->duenio, 'cancelacion', 'Reserva cancelada por el cliente',
                    "{$reserva->nombre_cliente} canceló su reserva en {$negocio->nombre}: {$detalle}.{$reembolso}",
                    route('dueno.reservas.index'), 'warning');
            } else {
                Avisos::a($reserva->cliente, 'cancelacion', 'Tu reserva fue cancelada',
                    "Se canceló tu reserva en {$negocio->nombre}: {$detalle}.{$reembolso}",
                    route('cliente.reservas.index'), 'warning');
            }

            return;
        }

        if ($reserva->wasChanged('pago_estado') && $reserva->pago_estado === 'pagado') {
            Avisos::a($reserva->cliente, 'pago', 'Pago confirmado',
                "Tu reserva en {$negocio->nombre} quedó confirmada. Tu código es {$reserva->codigo}.",
                route('cliente.reservas.index'), 'success');

            Avisos::a($negocio?->duenio, 'pago', 'Reserva pagada',
                "{$reserva->nombre_cliente} pagó su reserva en {$negocio->nombre} ({$reserva->codigo}): {$detalle}.",
                route('dueno.reservas.index'), 'success');
        }
    }

    private function detalle(Reserva $r): string
    {
        $fecha = Carbon::parse($r->fecha)->format('d/m/Y');

        if ($r->tipo_habitacion_id) {
            $unidad = $r->modalidad === 'horas' ? 'horas' : 'noches';

            return ($r->tipoHabitacion->nombre ?? 'Habitación') . ", {$r->cantidad} {$unidad}, entrada {$fecha} " . Formato::hora($r->hora_entrada);
        }

        return "{$fecha} a las " . Formato::hora($r->hora_entrada) . ($r->personas ? ", {$r->personas} personas" : '');
    }
}