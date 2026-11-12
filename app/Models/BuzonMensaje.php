<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'user_id', 'tipo', 'negocios_texto', 'mensaje', 'estado', 'respuesta',
    'respondido_at', 'respondido_por', 'archivado_at', 'oculto_usuario_at',
])]
class BuzonMensaje extends Model
{
    protected $table = 'buzon_mensajes';

    protected function casts(): array
    {
        return [
            'respondido_at' => 'datetime',
            'archivado_at' => 'datetime',
            'oculto_usuario_at' => 'datetime',
        ];
    }

    public function user() { return $this->belongsTo(User::class); }
    public function respondidoPor() { return $this->belongsTo(User::class, 'respondido_por'); }

    public function negocios()
    {
        return $this->belongsToMany(Negocio::class, 'buzon_mensaje_negocio', 'buzon_mensaje_id', 'negocio_id')->withTrashed();
    }
}