<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Negocio;
use App\Services\EstadisticasService;
use Illuminate\Http\Request;

class EstadisticasAdminController extends Controller
{
    public function __construct(private EstadisticasService $estadisticas) {}

    public function index(Request $request)
    {
        $request->validate([
            'tipo' => 'nullable|in:restaurante,hotel,motel',
            'departamento_id' => 'nullable|string|size:2',
            'municipio_id' => 'nullable|string|size:5',
            'q' => 'nullable|string|max:100',
        ]);

        $rango = $this->estadisticas->rango($request);

        $hayFiltroNegocio = $request->filled('tipo') || $request->filled('departamento_id')
            || $request->filled('municipio_id') || $request->filled('q');

        // Sin filtros de negocio se calculan todos los negocios, eliminados incluidos
        $ids = null;

        if ($hayFiltroNegocio) {
            $ids = Negocio::withTrashed()
                ->when($request->filled('tipo'), fn ($q) => $q->where('tipo', $request->tipo))
                ->when($request->filled('departamento_id'), fn ($q) => $q->where('departamento_id', $request->departamento_id))
                ->when($request->filled('municipio_id'), fn ($q) => $q->where('municipio_id', $request->municipio_id))
                ->when($request->filled('q'), function ($q) use ($request) {
                    $texto = trim($request->q);

                    $q->where(function ($w) use ($texto) {
                        $w->where('nombre', 'like', '%' . $texto . '%');

                        if (preg_match('/^(neg-?)?\d+$/i', $texto)) {
                            $w->orWhere('id', Negocio::idDesdeCodigo($texto));
                        }
                    });
                })
                ->pluck('id')
                ->all();
        }

        $datos = $this->estadisticas->calcular($ids, $rango);
        $plataforma = $this->estadisticas->plataforma($rango);

        return view('admin.estadisticas', compact('datos', 'rango', 'plataforma', 'hayFiltroNegocio'));
    }
}