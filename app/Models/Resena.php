<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['negocio_id', 'user_id', 'calificacion', 'comentario'])]
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
}