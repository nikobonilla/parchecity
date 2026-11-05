<?php

namespace App\Services;

use App\Models\Negocio;

class NegocioAccionesService
{
    public function __construct(private CancelacionService $cancelacion) {}

    // Cancela las reservas activas (las pagadas se reembolsan) y oculta el negocio
    public function eliminar(Negocio $negocio): int
    {
        $canceladas = 0;

        $negocio->reservas()
            ->whereIn('estado', ['pendiente', 'confirmada'])
            ->get()
            ->each(function ($reserva) use (&$canceladas) {
                $this->cancelacion->cancelar($reserva, 'negocio');
                $canceladas++;
            });

        $negocio->delete();

        return $canceladas;
    }

    // $quien: 'dueno' o 'admin'. Si el admin congela uno que congeló el dueño, queda bloqueado por el admin.
    public function congelar(Negocio $negocio, string $quien): void
    {
        if ($negocio->congelado) {
            if ($quien === 'admin' && $negocio->congelado_por !== 'admin') {
                $negocio->update(['congelado_por' => 'admin']);
            }

            return;
        }

        $negocio->update([
            'congelado' => true,
            'congelado_por' => $quien,
            'congelado_at' => now(),
        ]);
    }

    // Devuelve un mensaje de error si no se puede descongelar, o null si salió bien
    public function descongelar(Negocio $negocio, string $quien): ?string
    {
        if (! $negocio->congelado) {
            return null;
        }

        if ($quien === 'dueno' && $negocio->congelado_por === 'admin') {
            return 'Este negocio fue congelado por la administración. Solo un administrador puede descongelarlo.';
        }

        $negocio->update([
            'congelado' => false,
            'congelado_por' => null,
            'congelado_at' => null,
        ]);

        return null;
    }
}