<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Negocio;
use App\Models\User;

class DashboardAdminController extends Controller
{
    public function index()
    {
        $pendientes = Negocio::where('estado', 'pendiente')->count();
        $aprobados = Negocio::where('estado', 'aprobado')->count();
        $totalUsuarios = User::count();

        return view('admin.dashboard', compact('pendientes', 'aprobados', 'totalUsuarios'));
    }
}