<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resena extends Model
{
    public function negocio()
{
    return $this->belongsTo(Negocio::class);
}

public function user()
{
    return $this->belongsTo(User::class);
}
    //
}
