<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['tipo_habitacion_id', 'fecha', 'cantidad', 'activo'])]
class CupoHabitacion extends Model
{
    protected $table = 'cupos_habitacion';

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    public function tipoHabitacion() { return $this->belongsTo(TipoHabitacion::class); }
}