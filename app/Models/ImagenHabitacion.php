<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['tipo_habitacion_id', 'ruta'])]
class ImagenHabitacion extends Model
{
    protected $table = 'imagenes_habitacion';

    public function tipoHabitacion() { return $this->belongsTo(TipoHabitacion::class); }
}