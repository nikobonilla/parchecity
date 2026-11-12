<?php

namespace App\Services;

use App\Models\Cupo;
use App\Models\CupoHabitacion;
use App\Models\Negocio;
use App\Models\Reserva;
use App\Models\TipoHabitacion;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Support\Formato;

class ReservaService
{
    /* ============ RESTAURANTES ============ */

    public function crear(array $datos, Negocio $negocio, int $userId): Reserva
    {
        Reserva::expirarImpagas();

        $this->validarHorario($datos, $negocio);

        // La tarifa depende de la franja (mañana, tarde, noche) de la hora de entrada
        $tarifa = $negocio->tarifas()
            ->whereTime('hora_inicio', '<=', $datos['hora_entrada'])
            ->whereTime('hora_fin', '>=', $datos['hora_entrada'])
            ->first();

        if (! $tarifa) {
            $this->fallar('hora_entrada', 'A esa hora el negocio no tiene una tarifa configurada. Elige otra hora.');
        }

        // No hay hora de salida: la mesa se ocupa el tiempo de permanencia que fijó el dueño
        $permanencia = (int) ($negocio->minutos_liberacion ?? 90);
        $inicio = Carbon::createFromFormat('Y-m-d H:i', $datos['fecha'] . ' ' . $datos['hora_entrada']);
        $fin = $inicio->copy()->addMinutes($permanencia);

        return DB::transaction(function () use ($datos, $negocio, $userId, $tarifa, $inicio, $fin, $permanencia) {
            // Bloquea la fila mientras se revisa: evita que dos personas tomen la última mesa a la vez
            $cupo = Cupo::where('negocio_id', $negocio->id)
                ->where('fecha', $datos['fecha'])
                ->where('personas', $datos['personas'])
                ->lockForUpdate()
                ->first();

            if (! $cupo || ! $cupo->activo || $cupo->cantidad < 1) {
                $this->fallar('personas', 'No hay disponibilidad para esa cantidad de personas en esa fecha.');
            }

            $ocupadas = Reserva::where('negocio_id', $negocio->id)
                ->whereNull('tipo_habitacion_id')
                ->where('personas', $datos['personas'])
                ->whereIn('estado', ['pendiente', 'confirmada'])
                ->whereBetween('fecha', [$inicio->copy()->subDay()->toDateString(), $inicio->copy()->addDay()->toDateString()])
                ->get()
                ->map(fn ($r) => $this->intervalo($r, $negocio));

            if ($this->excedeCapacidad($ocupadas, $inicio, $fin, (int) $cupo->cantidad)) {
                $this->fallar('hora_entrada', "Ya no hay mesas disponibles a esa hora. Cada mesa se ocupa {$permanencia} minutos desde la entrada. Prueba con otra hora.");
            }

            return Reserva::create([
                'negocio_id' => $negocio->id,
                'user_id' => $userId,
                'fecha' => $datos['fecha'],
                'hora_entrada' => $datos['hora_entrada'],
                'hora_salida' => $fin->format('H:i'),
                'personas' => $datos['personas'],
                'estado' => 'pendiente',
                'pago_estado' => 'pendiente',
                'tarifa_id' => $tarifa->id,
                'valor_reserva' => $tarifa->precio,
                'nombre_cliente' => $datos['nombre_cliente'],
                'correo_cliente' => $datos['correo_cliente'],
                'telefono' => $datos['telefono'],
                'comentario' => $datos['comentario'] ?? null,
            ]);
        });
    }

    private function validarHorario(array $datos, Negocio $negocio): void
    {
        $this->validarHorarioNegocio($negocio, $datos['hora_entrada']);

        $entrada = $this->aMinutos($datos['hora_entrada']);

        if ($datos['fecha'] === now()->toDateString() && $entrada <= (now()->hour * 60 + now()->minute)) {
            $this->fallar('hora_entrada', 'Esa hora ya pasó. Elige una hora posterior.');
        }
    }

    // Regla de las 3 horas en el servicio de reservas
    private function validarHorarioNegocio(Negocio $negocio, string $hora): void
    {
        $ventana = $negocio->ventanaDeReservas();
        if (! $ventana) {
            return; // sin horario: atiende 24 horas
        }
        $horas = Negocio::HORAS_ANTES_DEL_CIERRE;
        if ($ventana['ultima_entrada'] < $ventana['abre']) {
            $this->fallar('hora_entrada', "Este negocio no recibe reservas con su horario actual (las reservas se cierran {$horas} horas antes del cierre).");
        }
        if (! $negocio->aceptaEntradaA($hora)) {
            $this->fallar(
                'hora_entrada',
                'El negocio atiende de ' . Formato::hora($negocio->hora_apertura) . ' a ' . Formato::hora($negocio->hora_cierre)
                . ' y recibe reservas hasta las ' . Formato::hora($negocio->ultimaEntrada())
                . " ({$horas} horas antes del cierre). Elige una hora de entrada dentro de ese rango."
            );
        }
    }

    /* ============ HOTELES Y MOTELES ============ */

    public function crearHabitacion(array $datos, Negocio $negocio, TipoHabitacion $tipo, int $userId): Reserva
    {
        Reserva::expirarImpagas();

        $modalidad = $datos['modalidad'];
        $cantidad = (int) $datos['cantidad'];

        if ($modalidad === 'horas') {
            if (! $tipo->precio_hora) {
                $this->fallar('modalidad', 'Esta habitación no se reserva por horas.');
            }
            if ($cantidad < 3 || $cantidad > 12) {
                $this->fallar('cantidad', 'Por horas puedes reservar de 3 a 12 horas.');
            }
            $precioUnitario = $tipo->precio_hora;
        } else {
            if (! $tipo->precio_noche) {
                $this->fallar('modalidad', 'Esta habitación no se reserva por noches.');
            }
            $precioUnitario = $tipo->precio_noche;
        }

        if ($tipo->capacidad) {
            if (empty($datos['personas'])) {
                $this->fallar('personas', 'Indica cuántas personas van.');
            }
            if ((int) $datos['personas'] > $tipo->capacidad) {
                $this->fallar('personas', "Esta habitación admite máximo {$tipo->capacidad} personas.");
            }
        }

        $inicio = Carbon::createFromFormat('Y-m-d H:i', $datos['fecha'] . ' ' . $datos['hora_entrada']);
        
        $this->validarHorarioNegocio($negocio, $datos['hora_entrada']);
        if ($inicio->lte(now())) {
            $this->fallar('hora_entrada', 'Esa fecha y hora ya pasó. Elige una posterior.');
        }

        $fin = $modalidad === 'horas'
            ? $inicio->copy()->addHours($cantidad)
            : $inicio->copy()->addDays($cantidad);

        // Margen de limpieza: antes de cada entrada y después de cada salida
        $margen = (int) ($negocio->minutos_liberacion ?? 90);

        return DB::transaction(function () use ($datos, $negocio, $tipo, $userId, $inicio, $fin, $margen, $modalidad, $cantidad, $precioUnitario) {
            TipoHabitacion::whereKey($tipo->id)->lockForUpdate()->first();

            $capacidad = $this->capacidadHabitacion($tipo, $inicio, $fin);

            $ocupadas = Reserva::where('tipo_habitacion_id', $tipo->id)
                ->whereIn('estado', ['pendiente', 'confirmada'])
                ->where('fecha', '<=', $fin->copy()->addMinutes($margen)->toDateString())
                ->where('fecha_salida', '>=', $inicio->copy()->subDays(2)->toDateString())
                ->get()
                ->map(fn ($r) => $this->intervalo($r, $negocio));

            if ($this->excedeCapacidad($ocupadas, $inicio, $fin->copy()->addMinutes($margen), $capacidad)) {
                $this->fallar('fecha', "Esa habitación no está disponible en ese horario. Debe quedar libre {$margen} minutos antes de cada entrada para poder prepararla. Prueba con otro horario.");
            }

            return Reserva::create([
                'negocio_id' => $negocio->id,
                'user_id' => $userId,
                'tipo_habitacion_id' => $tipo->id,
                'fecha' => $inicio->toDateString(),
                'hora_entrada' => $inicio->format('H:i:s'),
                'fecha_salida' => $fin->toDateString(),
                'hora_salida' => $fin->format('H:i:s'),
                'modalidad' => $modalidad,
                'cantidad' => $cantidad,
                'personas' => $datos['personas'] ?? null,
                'estado' => 'pendiente',
                'pago_estado' => 'pendiente',
                'valor_reserva' => $precioUnitario * $cantidad, // el precio real siempre se calcula aquí, nunca en el navegador
                'nombre_cliente' => $datos['nombre_cliente'],
                'correo_cliente' => $datos['correo_cliente'],
                'telefono' => $datos['telefono'],
                'comentario' => $datos['comentario'] ?? null,
            ]);
        });
    }

    // Cada fecha que toca la estadía debe estar habilitada; se usa la menor cantidad de habitaciones
    private function capacidadHabitacion(TipoHabitacion $tipo, Carbon $inicio, Carbon $fin): int
    {
        $fechas = [];
        for ($dia = $inicio->copy()->startOfDay(); $dia->lt($fin); $dia->addDay()) {
            $fechas[] = $dia->toDateString();
        }

        $cupos = CupoHabitacion::where('tipo_habitacion_id', $tipo->id)
            ->whereIn('fecha', $fechas)
            ->get()
            ->keyBy('fecha');

        $menor = null;
        foreach ($fechas as $fecha) {
            $cupo = $cupos->get($fecha);

            if (! $cupo || ! $cupo->activo || $cupo->cantidad < 1) {
                $this->fallar('fecha', 'Esta habitación no está disponible el ' . Carbon::parse($fecha)->format('d/m/Y') . '. Prueba con otras fechas.');
            }

            $menor = $menor === null ? $cupo->cantidad : min($menor, $cupo->cantidad);
        }

        return (int) $menor;
    }

    /* ============ LÓGICA COMPARTIDA ============ */

    private function intervalo(Reserva $r, Negocio $negocio): array
    {
        return $r->intervaloOcupado($negocio);
    }

    // ¿En algún momento del intervalo nuevo se pasaría el número de mesas o habitaciones?
    private function excedeCapacidad(Collection $intervalos, Carbon $desde, Carbon $hasta, int $capacidad): bool
    {
        $puntos = [$desde->copy()];

        foreach ($intervalos as $i) {
            if ($i['desde']->gt($desde) && $i['desde']->lt($hasta)) {
                $puntos[] = $i['desde']->copy();
            }
        }

        foreach ($puntos as $punto) {
            $ocupadas = $intervalos
                ->filter(fn ($i) => $i['desde']->lte($punto) && $i['hasta']->gt($punto))
                ->count();

            if ($ocupadas + 1 > $capacidad) {
                return true;
            }
        }

        return false;
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
}