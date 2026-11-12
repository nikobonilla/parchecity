<?php

namespace App\Services;

use App\Models\Cupo;
use App\Models\Negocio;
use App\Models\Reserva;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DisponibilidadService
{
    // Devuelve [negocio_id => ['habilitadas' => N, 'libres' => N]] para la fecha.
    // "Libres" es lo que sigue libre incluso en el momento más ocupado del día, descontando las reservas activas.
    public function resumen(Collection $negocios, string $fecha): array
    {
        $resultado = [];
        foreach ($negocios as $negocio) {
            $resultado[$negocio->id] = ['habilitadas' => 0, 'libres' => 0];
        }

        $desde = Carbon::parse($fecha)->startOfDay();
        $hasta = $desde->copy()->addDay();

        // Para hoy solo importa lo que falta del día
        if ($desde->isToday()) {
            $desde = now();
        }

        $restaurantes = $negocios->where('tipo', 'restaurante');
        $alojamientos = $negocios->where('tipo', '!=', 'restaurante');

        if ($restaurantes->isNotEmpty()) {
            $ids = $restaurantes->pluck('id');

            $cupos = Cupo::whereIn('negocio_id', $ids)
                ->where('fecha', $fecha)->where('activo', true)->where('cantidad', '>', 0)
                ->get()->groupBy('negocio_id');

            $reservas = Reserva::whereIn('negocio_id', $ids)
                ->whereNull('tipo_habitacion_id')
                ->whereIn('estado', ['pendiente', 'confirmada'])
                ->whereBetween('fecha', [$desde->copy()->subDay()->toDateString(), $hasta->toDateString()])
                ->get()->groupBy('negocio_id');

            foreach ($restaurantes as $negocio) {
                foreach ($cupos->get($negocio->id, collect()) as $cupo) {
                    $intervalos = $reservas->get($negocio->id, collect())
                        ->where('personas', $cupo->personas)
                        ->map(fn ($r) => $r->intervaloOcupado($negocio));

                    $resultado[$negocio->id]['habilitadas'] += $cupo->cantidad;
                    $resultado[$negocio->id]['libres'] += max(0, $cupo->cantidad - $this->pico($intervalos, $desde, $hasta));
                }
            }
        }

        if ($alojamientos->isNotEmpty()) {
            $ids = $alojamientos->pluck('id');

            $cupos = DB::table('cupos_habitacion as c')
                ->join('tipos_habitacion as t', 't.id', '=', 'c.tipo_habitacion_id')
                ->whereIn('t.negocio_id', $ids)
                ->where('c.fecha', $fecha)->where('c.activo', true)->where('c.cantidad', '>', 0)
                ->get(['c.tipo_habitacion_id', 'c.cantidad', 't.negocio_id'])
                ->groupBy('negocio_id');

            $reservas = Reserva::whereIn('negocio_id', $ids)
                ->whereNotNull('tipo_habitacion_id')
                ->whereIn('estado', ['pendiente', 'confirmada'])
                ->where('fecha', '<=', $hasta->toDateString())
                ->where('fecha_salida', '>=', $desde->copy()->subDays(2)->toDateString())
                ->get()->groupBy('negocio_id');

            foreach ($alojamientos as $negocio) {
                foreach ($cupos->get($negocio->id, collect()) as $cupo) {
                    $intervalos = $reservas->get($negocio->id, collect())
                        ->where('tipo_habitacion_id', $cupo->tipo_habitacion_id)
                        ->map(fn ($r) => $r->intervaloOcupado($negocio));

                    $resultado[$negocio->id]['habilitadas'] += $cupo->cantidad;
                    $resultado[$negocio->id]['libres'] += max(0, $cupo->cantidad - $this->pico($intervalos, $desde, $hasta));
                }
            }
        }

        return $resultado;
    }

    // Máximo de reservas ocupando al mismo tiempo dentro del rango
    private function pico(Collection $intervalos, Carbon $desde, Carbon $hasta): int
    {
        $puntos = [$desde->copy()];

        foreach ($intervalos as $i) {
            if ($i['desde']->gt($desde) && $i['desde']->lt($hasta)) {
                $puntos[] = $i['desde']->copy();
            }
        }

        $maximo = 0;

        foreach ($puntos as $punto) {
            $ocupadas = $intervalos
                ->filter(fn ($i) => $i['desde']->lte($punto) && $i['hasta']->gt($punto))
                ->count();

            $maximo = max($maximo, $ocupadas);
        }

        return $maximo;
    }
}