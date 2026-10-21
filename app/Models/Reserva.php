<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable([
    'negocio_id', 'user_id', 'fecha', 'personas', 'habitaciones', 'estado',
    'codigo', 'hora_entrada', 'hora_salida', 'monto_pagado', 'pago_estado',
    'nombre_cliente', 'correo_cliente', 'telefono', 'tarifa_id', 'comentario',
    'valor_reserva', 'pago_reportado_at', 'pagado_at', 'reembolso_aplica', 'metodo_pago',
    'tipo_habitacion_id',
])]
class Reserva extends Model
{
    protected function casts(): array
    {
        return [
            'pago_reportado_at' => 'datetime',
            'pagado_at' => 'datetime',
            'reembolso_aplica' => 'boolean',
        ];
    }

    public function negocio() { return $this->belongsTo(Negocio::class); }
    public function cliente() { return $this->belongsTo(User::class, 'user_id'); }
    public function tarifa() { return $this->belongsTo(Tarifa::class); }
    public function tipoHabitacion() { return $this->belongsTo(TipoHabitacion::class); }
}