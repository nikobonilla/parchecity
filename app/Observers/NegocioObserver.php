<?php

namespace App\Observers;

use App\Models\Negocio;
use App\Support\Avisos;

class NegocioObserver
{
    // Esto sí le llega al admin: necesita aprobarlo
    public function created(Negocio $negocio): void
    {
        Avisos::aAdmins('negocio', 'Nuevo negocio por aprobar',
            "{$negocio->nombre} ({$negocio->codigo()}) espera aprobación.",
            route('admin.negocios.pendientes'), 'warning');
    }

    public function updated(Negocio $negocio): void
    {
        $esElDuenio = auth()->id() === $negocio->user_id;
        $duenio = $negocio->duenio;

        if ($negocio->wasChanged('estado')) {
            if ($negocio->estado === 'aprobado') {
                Avisos::a($duenio, 'negocio', '¡Tu negocio fue aprobado!',
                    "{$negocio->nombre} ya aparece en la página y puede recibir reservas.",
                    route('negocios.show', $negocio), 'success');
            } elseif ($negocio->estado === 'rechazado') {
                Avisos::a($duenio, 'negocio', 'Tu negocio fue rechazado',
                    "{$negocio->nombre} no fue aprobado. Revisa los datos o escribe a atención al cliente.",
                    route('dueno.negocios.edit', $negocio), 'danger');
            }
        }

        if ($negocio->wasChanged('congelado') && ! $esElDuenio) {
            $accion = $negocio->congelado ? 'congelado' : 'descongelado';

            Avisos::a($duenio, 'negocio', 'Negocio ' . $accion,
                "{$negocio->nombre} fue {$accion} por la administración.",
                route('dueno.negocios.index'), $negocio->congelado ? 'warning' : 'success');
        }
    }

    public function deleted(Negocio $negocio): void
    {
        if (auth()->id() !== $negocio->user_id) {
            Avisos::a($negocio->duenio, 'negocio', 'Tu negocio fue eliminado',
                "{$negocio->nombre} fue eliminado por la administración.", route('dueno.negocios.index'), 'danger');
        }
    }

    public function restored(Negocio $negocio): void
    {
        Avisos::a($negocio->duenio, 'negocio', 'Tu negocio fue restaurado',
            "{$negocio->nombre} volvió a estar activo.", route('dueno.negocios.index'), 'success');
    }
}