<?php

namespace App\Http\Controllers;

use App\Models\Reserva;

class DashboardController extends Controller
{
    public function index()
    {
        $usuario = auth()->user();

        if ($usuario->rol === 'administrador') {
            return redirect()->route('admin.dashboard');
        }

        Reserva::expirarImpagas();

        $hoy = now()->toDateString();

        $tarjetas = $usuario->tarjetas()->latest()->get();

        $proximas = Reserva::with(['negocio', 'tipoHabitacion'])
            ->where('user_id', $usuario->id)
            ->where('oculta_cliente', false)
            ->whereIn('estado', ['pendiente', 'confirmada'])
            ->where(fn ($w) => $w->where('fecha', '>=', $hoy)->orWhere('fecha_salida', '>=', $hoy))
            ->orderByDesc('created_at')
            ->limit(3)
            ->get();

        return view('dashboard', compact('usuario', 'tarjetas', 'proximas'));
    }
}