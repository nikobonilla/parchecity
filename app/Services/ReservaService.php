<?php

namespace App\Services;

use App\Models\Cupo;
use App\Models\Negocio;
use App\Models\Reserva;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ReservaService
{
    public function crear(array $datos, Negocio $negocio, int $userId): Reserva
    {
        $this->validarHorario($datos, $negocio);

        $tarifa = $negocio->tarifas()
            ->whereTime('hora_inicio', '<=', $datos['hora_entrada'])
            ->whereTime('hora_fin', '>=', $datos['hora_entrada'])
            ->first();

        if (! $tarifa) {
            $this->fallar('hora_entrada', 'A esa hora el negocio no tiene una tarifa configurada. Elige otra hora.');
        }

        return DB::transaction(function () use ($datos, $negocio, $userId, $tarifa) {
            $cupo = Cupo::where('negocio_id', $negocio->id)
                ->where('fecha', $datos['fecha'])
                ->where('personas', $datos['personas'])
                ->lockForUpdate()
                ->first();

            if (! $cupo || ! $cupo->activo || $cupo->cantidad < 1) {
                $this->fallar('personas', 'No hay disponibilidad para esa cantidad de personas en esa fecha.');
            }

            if (! $this->hayEspacio($negocio, $cupo, $datos)) {
                $this->fallar('hora_entrada', 'Ya no hay mesas disponibles a esa hora para esa cantidad de personas. Prueba con otra hora.');
            }

            $datos['negocio_id'] = $negocio->id;
            $datos['user_id'] = $userId;
            $datos['estado'] = 'pendiente';
            $datos['pago_estado'] = 'pendiente';
            $datos['tarifa_id'] = $tarifa->id;
            $datos['valor_reserva'] = $tarifa->precio;

            return Reserva::create($datos);
        });
    }

   
    private function validarHorario(array $datos, Negocio $negocio): void
    {
        // 1. Unimos fecha y hora, forzando la zona horaria de Bogotá para evitar cualquier desfase
        $fechaHoraReserva = Carbon::parse($datos['fecha'] . ' ' . $datos['hora_entrada'], 'America/Bogota');
        $ahora = Carbon::now('America/Bogota');

        // 2. Validación infalible: la fecha y hora deben ser estrictamente posteriores al momento actual
        if ($fechaHoraReserva->lte($ahora)) {
            $this->fallar('hora_entrada', 'Esa hora ya pasó. Elige una hora posterior.');
        }

        // 3. Validar que esté dentro del horario de atención del negocio (si está configurado)
        if ($negocio->hora_apertura && $negocio->hora_cierre) {
            $horaReserva = $fechaHoraReserva->format('H:i');
            if ($horaReserva < $negocio->hora_apertura || $horaReserva > $negocio->hora_cierre) {
                $this->fallar('hora_entrada', 'La hora de entrada está fuera del horario de reservas del negocio.');
            }
        }
    }

    
    private function hayEspacio(Negocio $negocio, Cupo $cupo, array $datos): bool
    {
        $duracion = $negocio->minutos_liberacion ?? 90;
        $inicio = $this->aMinutos($datos['hora_entrada']);

        $existentes = Reserva::where('negocio_id', $negocio->id)
            ->where('fecha', $datos['fecha'])
            ->where('personas', $datos['personas'])
            ->whereIn('estado', ['pendiente', 'confirmada'])
            ->pluck('hora_entrada')
            ->map(fn ($hora) => $this->aMinutos($hora))
            ->all();

        
        $puntos = [$inicio];
        foreach ($existentes as $e) {
            if ($e >= $inicio && $e < $inicio + $duracion) {
                $puntos[] = $e;
            }
        }

        foreach ($puntos as $punto) {
            $ocupadas = 0;
            foreach ($existentes as $e) {
                if ($e <= $punto && ($e + $duracion) > $punto) {
                    $ocupadas++;
                }
            }

            if ($ocupadas + 1 > $cupo->cantidad) {
                return false;
            }
        }

        return true;
    }

    private function aMinutos(string $hora): int
    {
        [$h, $m] = array_map('intval', explode(':', $hora));

        return $h * 60 + $m;
    }

    private function fallar(string $campo, string $mensaje): void
    {
        throw ValidationException::withMessages([$campo => $mensaje]);
    }

    private function generarCodigo(): string
    {
        do {
            $codigo = 'PC-' . strtoupper(Str::random(6));
        } while (Reserva::where('codigo', $codigo)->exists());

        return $codigo;
    }
}