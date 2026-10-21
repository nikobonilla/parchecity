<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['negocio_id', 'nombre'])]
class CategoriaHabitacion extends Model
{
    protected $table = 'categorias_habitacion';

    public function negocio() { return $this->belongsTo(Negocio::class); }
    public function tiposHabitacion() { return $this->hasMany(TipoHabitacion::class, 'categoria_id'); }
}