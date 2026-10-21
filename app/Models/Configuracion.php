<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['logo', 'imagen_fondo', 'imagen_home_1', 'imagen_home_2'])]
class Configuracion extends Model
{
    protected $table = 'configuracion';

    public static function obtener(): self
    {
        return self::first() ?? self::create([]);
    }
}