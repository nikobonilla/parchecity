<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Negocio;
use App\Services\AprobacionService;

class NegocioAdminController extends Controller
{
    public function __construct(private AprobacionService $aprobacionService) {}

    public function pendientes()
    {
        $negocios = Negocio::where('estado', 'pendiente')->get();
        return view('admin.negocios.pendientes', compact('negocios'));
    }

    public function index()
    {
        $negocios = Negocio::all();
        return view('admin.negocios.index', compact('negocios'));
    }

    public function aprobar(Negocio $negocio)
    {
        $this->aprobacionService->aprobar($negocio);
        return back()->with('exito', 'Negocio aprobado.');
    }

    public function rechazar(Negocio $negocio)
    {
        $this->aprobacionService->rechazar($negocio);
        return back()->with('exito', 'Negocio rechazado.');
    }
}