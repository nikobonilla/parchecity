<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable([
    'negocio_id', 'user_id', 'fecha', 'personas', 'habitaciones', 'estado',
    'codigo', 'hora_entrada', 'hora_salida', 'monto_pagado', 'pago_estado',
    'nombre_cliente', 'correo_cliente', 'telefono', 'tarifa_id', 'comentario',
    'valor_reserva', 'pago_reportado_at', 'pagado_at', 'reembolso_aplica', 'metodo_pago',
    'tipo_habitacion_id', 'modalidad', 'cantidad', 'fecha_salida',
    'oculta_cliente', 'oculta_dueno', 'liberada_at',
    'cuotas', 'tarjeta_ultimos4',
])]
class Reserva extends Model
{
    // Plazos de la plataforma: se cambian solo aquí
    public const MINUTOS_PARA_PAGAR = 30;
    public const MINUTOS_REEMBOLSO = 45;

    protected function casts(): array
    {
        return [
            'pago_reportado_at' => 'datetime',
            'pagado_at' => 'datetime',
            'liberada_at' => 'datetime',
            'reembolso_aplica' => 'boolean',
            'oculta_cliente' => 'boolean',
            'oculta_dueno' => 'boolean',
        ];
    }

    public function negocio() { return $this->belongsTo(Negocio::class)->withTrashed(); }
    public function cliente() { return $this->belongsTo(User::class, 'user_id'); }
    public function tarifa() { return $this->belongsTo(Tarifa::class); }
    public function tipoHabitacion() { return $this->belongsTo(TipoHabitacion::class); }

    public function estaActiva(): bool
    {
        return in_array($this->estado, ['pendiente', 'confirmada']);
    }

    // Hasta qué hora se puede pedir reembolso (solo si ya está pagada)
    public function reembolsoHasta(): ?Carbon
    {
        if ($this->pago_estado !== 'pagado' || ! $this->pagado_at) {
            return null;
        }

        return $this->pagado_at->copy()->addMinutes(self::MINUTOS_REEMBOLSO);
    }

    public function puedeReembolsar(): bool
    {
        $hasta = $this->reembolsoHasta();

        return $hasta !== null && now()->lt($hasta);
    }

    // Cuánto tiempo deja ocupada esta reserva: la mesa por el tiempo de permanencia del restaurante,
    // o la habitación hasta la salida más el margen de limpieza. Si el dueño la liberó a mano, termina ahí.
    public function intervaloOcupado(Negocio $negocio): array
    {
        $desde = Carbon::parse($this->fecha . ' ' . $this->hora_entrada);
        $margen = (int) ($negocio->minutos_liberacion ?? 90);

        if ($this->tipo_habitacion_id) {
            $cantidad = max(1, (int) $this->cantidad);
            $fin = $this->modalidad === 'horas'
                ? $desde->copy()->addHours($cantidad)
                : $desde->copy()->addDays($cantidad);
            $extra = $margen;
        } else {
            $fin = $desde->copy()->addMinutes($margen);
            $extra = 0;
        }

        if ($this->liberada_at && $this->liberada_at->lt($fin)) {
            $fin = $this->liberada_at->gt($desde) ? $this->liberada_at->copy() : $desde->copy();
        }

        return ['desde' => $desde, 'hasta' => $fin->copy()->addMinutes($extra)];
    }

    // Cancela las reservas que no se pagaron a tiempo, para que no bloqueen el cupo
    public static function expirarImpagas(int $minutos = self::MINUTOS_PARA_PAGAR): int
    {
        return static::where('estado', 'pendiente')
            ->where('pago_estado', 'pendiente')
            ->where('created_at', '<', now()->subMinutes($minutos))
            ->update(['estado' => 'cancelada']);
    }
}