<?php

namespace App\Http\Controllers;

use App\Support\Formato;
use Illuminate\Support\Facades\DB;

class UbicacionController extends Controller
{
    public function municipios(string $departamento)
    {
        return DB::table('municipios')
            ->where('departamento_id', $departamento)
            ->orderBy('nombre')
            ->get(['id', 'nombre'])
            ->map(fn ($m) => ['id' => $m->id, 'nombre' => Formato::lugar($m->nombre)])
            ->values();
    }

    public function localidades(string $municipio)
    {
        return DB::table('localidades')
            ->where('municipio_id', $municipio)
            ->orderBy('nombre')
            ->get(['id', 'nombre'])
            ->map(fn ($l) => ['id' => $l->id, 'nombre' => $l->nombre])
            ->values();
    }
}