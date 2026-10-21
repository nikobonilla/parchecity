<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['tipo_habitacion_id', 'franja_horaria', 'hora_inicio', 'hora_fin', 'precio'])]
class TarifaHabitacion extends Model
{
    protected $table = 'tarifas_habitacion';

    public function tipoHabitacion() { return $this->belongsTo(TipoHabitacion::class); }
}