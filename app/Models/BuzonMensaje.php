<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['user_id', 'tipo', 'negocios_texto', 'mensaje', 'estado'])]
class BuzonMensaje extends Model
{
    protected $table = 'buzon_mensajes';

    public function user() { return $this->belongsTo(User::class); }

    public function negocios()
    {
        return $this->belongsToMany(Negocio::class, 'buzon_mensaje_negocio', 'buzon_mensaje_id', 'negocio_id')->withTrashed();
    }
}