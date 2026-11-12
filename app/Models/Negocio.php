<?php

namespace App\Models;

use App\Support\Formato;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

#[Fillable([
    'user_id', 'nombre', 'tipo', 'direccion', 'ubicacion', 'descripcion', 'contacto',
    'estado', 'whatsapp', 'hora_apertura', 'hora_cierre', 'cupo_sugerido', 'cupo_manual',
    'minutos_liberacion', 'medio_pago', 'congelado', 'congelado_por', 'congelado_at',
    'departamento_id', 'municipio_id', 'localidad_id', 'barrio', 'indicaciones',
])]
class Negocio extends Model
{
    // "Eliminar" oculta el negocio pero no borra su historial de la base de datos
    use SoftDeletes;

    // Las reservas dejan de recibirse estas horas antes del cierre (se cambia solo aquí)
    public const HORAS_ANTES_DEL_CIERRE = 3;

    protected function casts(): array
    {
        return [
            'congelado' => 'boolean',
            'congelado_at' => 'datetime',
        ];
    }

    // Mantiene coherente la ubicación (la localidad define el municipio, y este el departamento)
    // y arma el texto "Municipio, Departamento" que se muestra en la página
    protected static function booted(): void
    {
        static::saving(function (Negocio $negocio) {
            if ($negocio->localidad_id) {
                $municipioDeLaLocalidad = DB::table('localidades')->where('id', $negocio->localidad_id)->value('municipio_id');

                if ($municipioDeLaLocalidad) {
                    $negocio->municipio_id = $municipioDeLaLocalidad;
                }
            }

            if ($negocio->municipio_id) {
                $municipio = DB::table('municipios')->where('id', $negocio->municipio_id)->first();

                if ($municipio) {
                    $negocio->departamento_id = $municipio->departamento_id;
                    $departamento = DB::table('departamentos')->where('id', $municipio->departamento_id)->value('nombre');

                    $negocio->ubicacion = Formato::lugar($municipio->nombre) . ($departamento ? ', ' . Formato::lugar($departamento) : '');
                }
            }
        });
    }

    /* ===== Identificación ===== */

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

    /* ===== Horario de atención ===== */

    private static function aMinutos(string $hora): int
    {
        [$h, $m] = array_map('intval', explode(':', $hora));

        return $h * 60 + $m;
    }

    // Ventana en minutos del día. Si cierra después de medianoche, "cierra" pasa de 1440.
    // Devuelve null cuando no hay horario (atiende 24 horas).
    public function ventanaDeReservas(): ?array
    {
        if (! $this->hora_apertura || ! $this->hora_cierre) {
            return null;
        }

        $abre = self::aMinutos($this->hora_apertura);
        $cierra = self::aMinutos($this->hora_cierre);

        if ($abre === $cierra) {
            return null;
        }

        if ($cierra < $abre) {
            $cierra += 1440;
        }

        return [
            'abre' => $abre,
            'cierra' => $cierra,
            'ultima_entrada' => $cierra - self::HORAS_ANTES_DEL_CIERRE * 60,
        ];
    }

    // ¿Se puede entrar a esa hora? Entre la apertura y 3 horas antes del cierre
    public function aceptaEntradaA(string $hora): bool
    {
        $v = $this->ventanaDeReservas();

        if (! $v) {
            return true;
        }

        $t = self::aMinutos($hora);

        if ($t < $v['abre']) {
            $t += 1440;
        }

        return $t >= $v['abre'] && $t <= $v['ultima_entrada'];
    }

    // Última hora de entrada, en formato "H:i"
    public function ultimaEntrada(): ?string
    {
        $v = $this->ventanaDeReservas();

        if (! $v) {
            return null;
        }

        $minutos = $v['ultima_entrada'] % 1440;
        if ($minutos < 0) {
            $minutos += 1440;
        }

        return sprintf('%02d:%02d', intdiv($minutos, 60), $minutos % 60);
    }

    // Cómo está el negocio en este momento (se usa para mostrar "No disponible")
    public function estadoDeAtencion(?Carbon $ahora = null): array
    {
        $ahora = $ahora ?? now();
        $v = $this->ventanaDeReservas();

        if (! $v) {
            return ['abierto' => true, 'reservable' => true, 'mensaje' => null];
        }

        if ($v['ultima_entrada'] < $v['abre']) {
            return ['abierto' => false, 'reservable' => false, 'mensaje' => 'No recibe reservas con su horario actual'];
        }

        $t = $ahora->hour * 60 + $ahora->minute;
        $tn = $t < $v['abre'] ? $t + 1440 : $t;
        $abre = Formato::hora($this->hora_apertura);

        if ($tn >= $v['abre'] && $tn <= $v['ultima_entrada']) {
            return ['abierto' => true, 'reservable' => true, 'mensaje' => null];
        }

        if ($tn > $v['ultima_entrada'] && $tn <= $v['cierra']) {
            return [
                'abierto' => true,
                'reservable' => false,
                'mensaje' => 'Las reservas cerraron por hoy (se cierran ' . self::HORAS_ANTES_DEL_CIERRE . ' horas antes del cierre)',
            ];
        }

        if ($t < $v['abre']) {
            return ['abierto' => false, 'reservable' => true, 'mensaje' => "Cerrado ahora · abre a las {$abre}"];
        }

        return ['abierto' => false, 'reservable' => false, 'mensaje' => "Cerrado por hoy · abre mañana a las {$abre}"];
    }

    /* ===== Relaciones ===== */

    public function duenio() { return $this->belongsTo(User::class, 'user_id'); }
    public function departamento() { return $this->belongsTo(Departamento::class, 'departamento_id'); }
    public function municipio() { return $this->belongsTo(Municipio::class, 'municipio_id'); }
    public function localidad() { return $this->belongsTo(Localidad::class, 'localidad_id'); }
    public function imagenes() { return $this->hasMany(Imagen::class); }
    public function menus() { return $this->hasMany(Menu::class)->orderBy('id'); }
    public function reservas() { return $this->hasMany(Reserva::class); }
    public function resenas() { return $this->hasMany(Resena::class); }
    public function tarifas() { return $this->hasMany(Tarifa::class); }
    public function cupos() { return $this->hasMany(Cupo::class); }
    public function tiposHabitacion() { return $this->hasMany(TipoHabitacion::class); }
    public function categoriasHabitacion() { return $this->hasMany(CategoriaHabitacion::class); }

    public function cuposHabitacion()
    {
        return $this->hasManyThrough(CupoHabitacion::class, TipoHabitacion::class, 'negocio_id', 'tipo_habitacion_id');
    }
}