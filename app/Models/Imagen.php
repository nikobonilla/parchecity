<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['negocio_id', 'ruta'])]
class Imagen extends Model
{
    public function negocio()
    {
        return $this->belongsTo(Negocio::class);
    }
}