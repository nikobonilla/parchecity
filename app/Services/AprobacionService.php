<?php

namespace App\Services;

use App\Models\Negocio;

class AprobacionService
{
    public function aprobar(Negocio $negocio): void
    {
        $negocio->update(['estado' => 'aprobado']);
    }

    public function rechazar(Negocio $negocio): void
    {
        $negocio->update(['estado' => 'rechazado']);
    }
}