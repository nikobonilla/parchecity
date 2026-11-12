<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['negocio_id', 'titulo', 'ruta', 'tipo'])]
class Menu extends Model
{
    protected $table = 'menus';

    public function negocio() { return $this->belongsTo(Negocio::class)->withTrashed(); }
}