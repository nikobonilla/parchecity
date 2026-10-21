<?php

namespace App\Services;

use App\Models\Negocio;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class NegocioService
{
    public function registrar(array $datos, int $userId, array $imagenes = []): Negocio
    {
        $datos['user_id'] = $userId;
        $datos['estado'] = 'pendiente';

        $negocio = Negocio::create($datos);

        foreach ($imagenes as $imagen) {
            $ruta = $imagen->store('negocios', 'public');
            $negocio->imagenes()->create(['ruta' => $ruta]);
        }

        return $negocio;
    }
}