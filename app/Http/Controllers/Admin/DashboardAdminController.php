<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BuzonMensaje;
use App\Models\Negocio;
use App\Models\User;

class DashboardAdminController extends Controller
{
    public function index()
    {
        return view('admin.dashboard', [
            'pendientes' => Negocio::where('estado', 'pendiente')->count(),
            'aprobados' => Negocio::where('estado', 'aprobado')->count(),
            'congelados' => Negocio::where('congelado', true)->count(),
            'totalUsuarios' => User::count(),
            'mensajesNuevos' => BuzonMensaje::whereNull('archivado_at')->where('estado', 'nuevo')->count(),
        ]);
    }
}