<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tarifa extends Model
{
    public function negocio()
{
    return $this->belongsTo(Negocio::class);
}
    //
}
