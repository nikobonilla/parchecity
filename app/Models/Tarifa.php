<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['negocio_id', 'franja_horaria', 'hora_inicio', 'hora_fin', 'precio'])]
class Tarifa extends Model
{
    public function negocio()
    {
        return $this->belongsTo(Negocio::class);
    }
}