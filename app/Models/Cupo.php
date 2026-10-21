<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['negocio_id', 'fecha', 'personas', 'cantidad', 'activo'])]
class Cupo extends Model
{
    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    public function negocio()
    {
        return $this->belongsTo(Negocio::class);
    }
}