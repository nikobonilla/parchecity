<?php

namespace App\Http\Controllers;

use App\Models\Negocio;
use App\Models\TipoHabitacion;
use App\Services\DisponibilidadService;
use Illuminate\Http\Request;

class NegocioPublicoController extends Controller
{
    public function __construct(private DisponibilidadService $disponibilidad) {}

    public function index(Request $request)
    {
        return $this->listar($request, null);
    }

    public function restaurantes(Request $request)
    {
        return $this->listar($request, 'restaurante');
    }

    public function hoteles(Request $request)
    {
        return $this->listar($request, 'hotel');
    }

    public function moteles(Request $request)
    {
        return $this->listar($request, 'motel');
    }

    private function listar(Request $request, ?string $tipoFijo)
    {
        $request->validate([
            'tipo' => 'nullable|in:restaurante,hotel,motel',
            'departamento_id' => 'nullable|string|size:2',
            'municipio_id' => 'nullable|string|size:5',
            'localidad_id' => 'nullable|integer',
            'q' => 'nullable|string|max:100',
            'fecha' => 'nullable|date_format:Y-m-d',
            'disponibilidad' => 'nullable|boolean',
        ]);

        $hoy = now()->toDateString();
        $fecha = $request->input('fecha', $hoy);
        $tipo = $tipoFijo ?? $request->input('tipo');

        $consulta = Negocio::where('estado', 'aprobado')
            ->when($tipo, fn ($q) => $q->where('tipo', $tipo))
            ->when($request->filled('departamento_id'), fn ($q) => $q->where('departamento_id', $request->departamento_id))
            ->when($request->filled('municipio_id'), fn ($q) => $q->where('municipio_id', $request->municipio_id))
            ->when($request->filled('localidad_id'), fn ($q) => $q->where('localidad_id', $request->localidad_id))
            ->when($request->filled('q'), function ($q) use ($request) {
                $texto = trim($request->q);

                $q->where(function ($w) use ($texto) {
                    $w->where('nombre', 'like', '%' . $texto . '%');

                    if (preg_match('/^(neg-?)?\d+$/i', $texto)) {
                        $w->orWhere('id', Negocio::idDesdeCodigo($texto));
                    }
                });
            });

        // Disponibilidad real: se calcula descontando las reservas que ya existen
        $candidatos = (clone $consulta)->get(['id', 'tipo', 'hora_apertura', 'hora_cierre', 'minutos_liberacion', 'congelado']);
        $resumen = $this->disponibilidad->resumen($candidatos, $fecha);

        if ($request->boolean('disponibilidad')) {
            $ids = $candidatos->filter(function ($negocio) use ($resumen, $fecha, $hoy) {
                if ($negocio->congelado) {
                    return false;
                }

                if ($fecha === $hoy && ! $negocio->estadoDeAtencion()['reservable']) {
                    return false;
                }

                return ($resumen[$negocio->id]['libres'] ?? 0) > 0;
            })->pluck('id');

            $consulta->whereIn('id', $ids);
        }

        $negocios = $consulta->with('imagenes')->orderBy('nombre')->paginate(12)->withQueryString();

        return view('negocios.index', compact('negocios', 'tipoFijo', 'fecha', 'resumen'));
    }

    public function show(Request $request, Negocio $negocio)
    {
        $this->autorizarVista($negocio);

        $categorias = collect();
        $habitaciones = collect();
        $categoriaActiva = null;

        if ($negocio->tipo !== 'restaurante') {
            $categoriaActiva = $request->query('categoria');

            $categorias = $negocio->categoriasHabitacion()
                ->whereHas('tiposHabitacion')
                ->orderBy('nombre')
                ->get();

            $habitaciones = $negocio->tiposHabitacion()
                ->with(['categoria', 'imagenes'])
                ->when($categoriaActiva, fn ($q) => $q->where('categoria_id', $categoriaActiva))
                ->orderBy('nombre')
                ->get();
        }

        return view('negocios.show', compact('negocio', 'categorias', 'habitaciones', 'categoriaActiva'));
    }

    public function habitacion(Negocio $negocio, TipoHabitacion $tipoHabitacion)
    {
        $this->autorizarVista($negocio);
        abort_unless($tipoHabitacion->negocio_id === $negocio->id, 404);

        $tipoHabitacion->load(['categoria', 'imagenes']);

        return view('negocios.habitacion', compact('negocio', 'tipoHabitacion'));
    }

    private function autorizarVista(Negocio $negocio): void
    {
        $esSuNegocio = auth()->check() && auth()->id() === $negocio->user_id;
        $esAdmin = auth()->check() && auth()->user()->rol === 'administrador';

        abort_unless($negocio->estado === 'aprobado' || $esSuNegocio || $esAdmin, 404);
    }
}