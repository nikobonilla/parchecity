<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable([
    'user_id', 'nombre', 'tipo', 'direccion', 'ubicacion', 'descripcion', 'contacto',
    'estado', 'whatsapp', 'hora_apertura', 'hora_cierre', 'cupo_sugerido', 'cupo_manual',
    'minutos_liberacion', 'medio_pago',
])]
class Negocio extends Model
{
    public function duenio() { return $this->belongsTo(User::class, 'user_id'); }
    public function imagenes() { return $this->hasMany(Imagen::class); }
    public function reservas() { return $this->hasMany(Reserva::class); }
    public function resenas() { return $this->hasMany(Resena::class); }
    public function tarifas() { return $this->hasMany(Tarifa::class); }
    public function cupos() { return $this->hasMany(Cupo::class); }
    public function tiposHabitacion(){ return $this->hasMany(TipoHabitacion::class); }
    public function categoriasHabitacion(){return $this->hasMany(CategoriaHabitacion::class);}
}