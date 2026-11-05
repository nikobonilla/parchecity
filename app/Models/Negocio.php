<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'user_id', 'nombre', 'tipo', 'direccion', 'ubicacion', 'descripcion', 'contacto',
    'estado', 'whatsapp', 'hora_apertura', 'hora_cierre', 'cupo_sugerido', 'cupo_manual',
    'minutos_liberacion', 'medio_pago', 'congelado', 'congelado_por', 'congelado_at',
])]
class Negocio extends Model
{
    // "Eliminar" oculta el negocio pero no borra su historial de la base de datos
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'congelado' => 'boolean',
            'congelado_at' => 'datetime',
        ];
    }

    // ID visible del negocio, por ejemplo NEG-0004
    public function codigo(): string
    {
        return 'NEG-' . str_pad((string) $this->id, 4, '0', STR_PAD_LEFT);
    }

    public static function idDesdeCodigo(string $codigo): ?int
    {
        $numero = preg_replace('/\D/', '', $codigo);

        return $numero === '' ? null : (int) $numero;
    }

    public function duenio() { return $this->belongsTo(User::class, 'user_id'); }
    public function imagenes() { return $this->hasMany(Imagen::class); }
    public function reservas() { return $this->hasMany(Reserva::class); }
    public function resenas() { return $this->hasMany(Resena::class); }
    public function tarifas() { return $this->hasMany(Tarifa::class); }
    public function cupos() { return $this->hasMany(Cupo::class); }
    public function tiposHabitacion() { return $this->hasMany(TipoHabitacion::class); }
    public function categoriasHabitacion() { return $this->hasMany(CategoriaHabitacion::class); }
}