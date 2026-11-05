<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BuzonMensaje;
use Illuminate\Http\Request;

class BuzonAdminController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'tipo_mensaje' => 'nullable|in:recomendacion,mejora,queja',
            'estado' => 'nullable|in:nuevo,leido,resuelto',
        ]);

        $base = fn () => BuzonMensaje::with(['user', 'negocios'])
            ->when($request->filled('tipo_mensaje'), fn ($q) => $q->where('tipo', $request->tipo_mensaje))
            ->when($request->filled('estado'), fn ($q) => $q->where('estado', $request->estado))
            ->orderByDesc('created_at')
            ->limit(100);

        // Un mensaje que habla de varios tipos de negocio aparece en cada pestaña que le corresponde
        $secciones = [];
        foreach (['hotel' => 'Hoteles', 'motel' => 'Moteles', 'restaurante' => 'Restaurantes'] as $tipo => $titulo) {
            $secciones[$tipo] = [
                'titulo' => $titulo,
                'mensajes' => $base()->whereHas('negocios', fn ($q) => $q->withTrashed()->where('tipo', $tipo))->get(),
            ];
        }

        $secciones['general'] = [
            'titulo' => 'General',
            'mensajes' => $base()->whereDoesntHave('negocios')->get(),
        ];

        return view('admin.buzon.index', compact('secciones'));
    }

    public function estado(Request $request, BuzonMensaje $mensaje)
    {
        $datos = $request->validate(['estado' => 'required|in:nuevo,leido,resuelto']);

        $mensaje->update($datos);

        return back();
    }

    public function destroy(BuzonMensaje $mensaje)
    {
        $mensaje->delete();

        return back()->with('exito', 'Mensaje eliminado.');
    }
}