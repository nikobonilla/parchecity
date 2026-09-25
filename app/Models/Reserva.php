<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reserva extends Model
{
    public function negocio()
{
    return $this->belongsTo(Negocio::class);
}

public function cliente()
{
    return $this->belongsTo(User::class, 'user_id');
}
    //
}
