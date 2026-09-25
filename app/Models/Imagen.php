<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Imagen extends Model
{
    public function negocio()
{
    return $this->belongsTo(Negocio::class);
}
    //
}
