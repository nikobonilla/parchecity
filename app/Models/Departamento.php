<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Departamento extends Model
{
    protected $table = 'departamentos';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;
}