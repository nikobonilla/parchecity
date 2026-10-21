<?php

namespace App\Http\Controllers;

use App\Models\Negocio;
use App\Models\TipoHabitacion;
use Illuminate\Http\Request;

class NegocioPublicoController extends Controller
{
    public function index(Request $request)
    {
        $negocios = Negocio::with('imagenes')
            ->where('estado', 'aprobado')
            ->when($request->ubicacion, fn ($q) => $q->where('ubicacion', 'like', "%{$request->ubicacion}%"))
            ->paginate(12);

        return view('negocios.index', compact('negocios'));
    }

    public function restaurantes(Request $request)
    {
        return $this->porTipo('restaurante', $request);
    }

    public function hoteles(Request $request)
    {
        return $this->porTipo('hotel', $request);
    }

    public function moteles(Request $request)
    {
        return $this->porTipo('motel', $request);
    }

    private function porTipo(string $tipo, Request $request)
    {
        $negocios = Negocio::with('imagenes')
            ->where('estado', 'aprobado')
            ->where('tipo', $tipo)
            ->when($request->ubicacion, fn ($q) => $q->where('ubicacion', 'like', "%{$request->ubicacion}%"))
            ->paginate(12);

        return view('negocios.index', compact('negocios'));
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