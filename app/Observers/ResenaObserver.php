<?php

namespace App\Observers;

use App\Models\Resena;
use App\Support\Avisos;

class ResenaObserver
{
    public function created(Resena $resena): void
    {
        $negocio = $resena->negocio;
        $estrellas = str_repeat('★', $resena->calificacion);

        Avisos::a($negocio?->duenio, 'resena', 'Nueva reseña',
            "{$resena->user->name} calificó {$negocio->nombre} con {$estrellas}.",
            route('negocios.show', $negocio));
    }
}