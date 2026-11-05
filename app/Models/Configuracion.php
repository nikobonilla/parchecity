<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['logo', 'imagen_fondo', 'imagen_home_1', 'imagen_home_2', 'whatsapp_soporte'])]
class Configuracion extends Model
{
    protected $table = 'configuracion';

    public static function obtener(): self
    {
        return self::first() ?? self::create([]);
    }
}