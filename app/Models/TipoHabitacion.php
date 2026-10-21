<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['negocio_id', 'categoria_id', 'nombre', 'capacidad', 'especificaciones', 'precio_hora', 'precio_noche'])]
class TipoHabitacion extends Model
{
    protected $table = 'tipos_habitacion';

    public function negocio() { return $this->belongsTo(Negocio::class); }
    public function categoria() { return $this->belongsTo(CategoriaHabitacion::class, 'categoria_id'); }
    public function imagenes() { return $this->hasMany(ImagenHabitacion::class); }
    public function cupos() { return $this->hasMany(CupoHabitacion::class); }
    public function reservas() { return $this->hasMany(Reserva::class); }
}