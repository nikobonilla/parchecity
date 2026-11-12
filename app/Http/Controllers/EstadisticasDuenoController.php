<?php

namespace App\Http\Controllers;

use App\Models\Negocio;
use App\Services\EstadisticasService;
use Illuminate\Http\Request;

class EstadisticasDuenoController extends Controller
{
    public function __construct(private EstadisticasService $estadisticas) {}

    public function index(Request $request)
    {
        $request->validate(['negocio_id' => 'nullable|integer']);

        // También cuentan los negocios eliminados: sus reservas ya ocurrieron
        $negocios = Negocio::withTrashed()->where('user_id', auth()->id())->orderBy('nombre')->get();

        if ($negocios->isEmpty()) {
            return view('dueno.estadisticas', ['negocios' => $negocios, 'datos' => null, 'rango' => null]);
        }

        $ids = $request->filled('negocio_id')
            ? $negocios->where('id', (int) $request->negocio_id)->pluck('id')->all()
            : $negocios->pluck('id')->all();

        abort_if(empty($ids), 403);

        $rango = $this->estadisticas->rango($request);
        $datos = $this->estadisticas->calcular($ids, $rango);

        return view('dueno.estadisticas', compact('negocios', 'datos', 'rango'));
    }
}