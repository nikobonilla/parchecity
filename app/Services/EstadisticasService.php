<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EstadisticasService
{
    public const AGRUPACIONES = ['dia' => 'Por día', 'semana' => 'Por semana', 'mes' => 'Por mes', 'anio' => 'Por año'];

    // Lee las fechas del formulario: un solo día, un rango, o el mes actual por defecto
    public function rango(Request $request): array
    {
        $request->validate([
            'dia' => 'nullable|date_format:Y-m-d',
            'desde' => 'nullable|date_format:Y-m-d',
            'hasta' => 'nullable|date_format:Y-m-d|after_or_equal:desde',
            'agrupar' => 'nullable|in:dia,semana,mes,anio',
        ]);

        if ($request->filled('dia')) {
            $desde = Carbon::parse($request->dia)->startOfDay();
            $hasta = $desde->copy();
        } else {
            $desde = $request->filled('desde') ? Carbon::parse($request->desde)->startOfDay() : now()->startOfMonth();
            $hasta = $request->filled('hasta') ? Carbon::parse($request->hasta)->startOfDay() : now()->endOfMonth()->startOfDay();

            if ($hasta->lt($desde)) {
                $hasta = $desde->copy();
            }
        }

        // Máximo 5 años por consulta
        if ($desde->lt($hasta->copy()->subYears(5))) {
            $desde = $hasta->copy()->subYears(5);
        }

        $dias = (int) $desde->diffInDays($hasta) + 1;
        $automatica = $dias <= 31 ? 'dia' : ($dias <= 183 ? 'semana' : ($dias <= 1100 ? 'mes' : 'anio'));

        return [
            'desde' => $desde,
            'hasta' => $hasta,
            'dias' => $dias,
            'agrupar' => $request->filled('agrupar') ? $request->agrupar : $automatica,
        ];
    }

    // $negocioIds = null significa "todos los negocios"
    public function calcular(?array $negocioIds, array $rango): array
    {
        $ahora = now()->toDateTimeString();
        $desde = $rango['desde']->toDateString();
        $hasta = $rango['hasta']->toDateString();

        $base = fn () => DB::table('reservas as r')
            ->whereBetween('r.fecha', [$desde, $hasta])
            ->when($negocioIds !== null, fn ($q) => $q->whereIn('r.negocio_id', $negocioIds));

        // Momento en que termina la reserva (para saber si ya se completó)
        $fin = "TIMESTAMP(COALESCE(r.fecha_salida, r.fecha), COALESCE(r.hora_salida, '23:59:59'))";
        $pagado = "CASE WHEN r.pago_estado = 'pagado' THEN r.valor_reserva ELSE 0 END";

        $kpi = $base()->selectRaw("
            COUNT(*) as total,
            COALESCE(SUM(r.estado = 'cancelada'), 0) as canceladas,
            COALESCE(SUM(r.estado = 'pendiente'), 0) as pendientes,
            COALESCE(SUM(r.estado = 'confirmada' AND {$fin} < ?), 0) as completadas,
            COALESCE(SUM(r.estado = 'confirmada' AND {$fin} >= ?), 0) as confirmadas,
            COALESCE(SUM({$pagado}), 0) as ingresos,
            COALESCE(SUM(CASE WHEN r.pago_estado = 'reembolsado' THEN r.valor_reserva ELSE 0 END), 0) as reembolsos
        ", [$ahora, $ahora])->first();

        $periodo = match ($rango['agrupar']) {
            'dia' => 'r.fecha',
            'semana' => 'YEARWEEK(r.fecha, 3)',
            'mes' => "DATE_FORMAT(r.fecha, '%Y-%m')",
            'anio' => 'YEAR(r.fecha)',
        };

        $serie = $base()->selectRaw("
            {$periodo} as periodo,
            MIN(r.fecha) as primera,
            COUNT(*) as total,
            COALESCE(SUM(r.estado = 'cancelada'), 0) as canceladas,
            COALESCE(SUM(r.estado = 'confirmada' AND {$fin} < ?), 0) as completadas,
            COALESCE(SUM({$pagado}), 0) as ingresos
        ", [$ahora])->groupBy('periodo')->orderBy('primera')->get();

        $habitaciones = $base()
            ->join('tipos_habitacion as t', 't.id', '=', 'r.tipo_habitacion_id')
            ->selectRaw("t.nombre, COUNT(*) as total, COALESCE(SUM({$pagado}), 0) as ingresos")
            ->groupBy('t.id', 't.nombre')->orderByDesc('total')->limit(8)->get();

        $modalidades = $base()
            ->whereNotNull('r.tipo_habitacion_id')
            ->selectRaw('r.modalidad, COUNT(*) as total')
            ->groupBy('r.modalidad')->orderByDesc('total')->get();

        $franjas = $base()
            ->join('tarifas as f', 'f.id', '=', 'r.tarifa_id')
            ->selectRaw("f.franja_horaria as nombre, COUNT(*) as total, COALESCE(SUM({$pagado}), 0) as ingresos")
            ->groupBy('f.id', 'f.franja_horaria')->orderByDesc('total')->limit(8)->get();

        $grupos = $base()
            ->whereNull('r.tipo_habitacion_id')->whereNotNull('r.personas')
            ->selectRaw('r.personas, COUNT(*) as total')
            ->groupBy('r.personas')->orderBy('r.personas')->get();

        $negocios = $base()
            ->join('negocios as n', 'n.id', '=', 'r.negocio_id')
            ->selectRaw("n.id, n.nombre, n.tipo, COUNT(*) as total, COALESCE(SUM({$pagado}), 0) as ingresos")
            ->groupBy('n.id', 'n.nombre', 'n.tipo')->orderByDesc('total')->limit(10)->get();

        return compact('kpi', 'serie', 'habitaciones', 'modalidades', 'franjas', 'grupos', 'negocios');
    }

    // Extras que solo ve el administrador: toda la plataforma
    public function plataforma(array $rango): array
    {
        $desde = $rango['desde']->toDateString();
        $hasta = $rango['hasta']->toDateString();
        $pagado = "CASE WHEN r.pago_estado = 'pagado' THEN r.valor_reserva ELSE 0 END";

        $enRango = fn () => DB::table('reservas as r')
            ->join('negocios as n', 'n.id', '=', 'r.negocio_id')
            ->whereBetween('r.fecha', [$desde, $hasta]);

        return [
            'usuarios_por_rol' => DB::table('users')->selectRaw('rol, COUNT(*) as total')->groupBy('rol')->pluck('total', 'rol'),
            'usuarios_nuevos' => DB::table('users')
                ->whereBetween('created_at', [$rango['desde']->copy()->startOfDay(), $rango['hasta']->copy()->endOfDay()])
                ->count(),

            'negocios_por_estado' => DB::table('negocios')->whereNull('deleted_at')->selectRaw('estado, COUNT(*) as total')->groupBy('estado')->pluck('total', 'estado'),
            'negocios_por_tipo' => DB::table('negocios')->whereNull('deleted_at')->selectRaw('tipo, COUNT(*) as total')->groupBy('tipo')->pluck('total', 'tipo'),
            'negocios_congelados' => DB::table('negocios')->whereNull('deleted_at')->where('congelado', true)->count(),
            'negocios_eliminados' => DB::table('negocios')->whereNotNull('deleted_at')->count(),

            'por_tipo' => $enRango()
                ->selectRaw("n.tipo, COUNT(*) as total, COALESCE(SUM({$pagado}), 0) as ingresos")
                ->groupBy('n.tipo')->orderByDesc('total')->get(),

            'por_municipio' => $enRango()
                ->join('municipios as m', 'm.id', '=', 'n.municipio_id')
                ->selectRaw("m.nombre, COUNT(*) as total, COALESCE(SUM({$pagado}), 0) as ingresos")
                ->groupBy('m.id', 'm.nombre')->orderByDesc('total')->limit(8)->get(),

            'buzon_por_estado' => DB::table('buzon_mensajes')->selectRaw('estado, COUNT(*) as total')->groupBy('estado')->pluck('total', 'estado'),
            'buzon_sin_responder' => DB::table('buzon_mensajes')->whereNull('respuesta')->count(),

            'resenas' => DB::table('resenas')->selectRaw('COUNT(*) as total, AVG(calificacion) as promedio')->first(),

            'favoritos_top' => DB::table('favoritos as f')
                ->join('negocios as n', 'n.id', '=', 'f.negocio_id')
                ->selectRaw('n.id, n.nombre, COUNT(*) as total')
                ->groupBy('n.id', 'n.nombre')->orderByDesc('total')->limit(5)->get(),
        ];
    }
}