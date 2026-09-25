<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Negocio extends Model
{
    public function duenio()
{
    return $this->belongsTo(User::class, 'user_id');
}

public function imagenes()
{
    return $this->hasMany(Imagen::class);
}

public function reservas()
{
    return $this->hasMany(Reserva::class);
}
public function resenas()
{
    return $this->hasMany(Resena::class);
}

public function tarifas()
{
    return $this->hasMany(Tarifa::class);
}
    //
}
