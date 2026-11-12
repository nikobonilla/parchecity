<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BuzonMensaje;
use App\Models\Negocio;
use Illuminate\Http\Request;

class BuzonAdminController extends Controller
{
    // El buzón muestra solo lo que no se ha quitado
    public function index(Request $request)
    {
        $request->validate([
            'tipo_mensaje' => 'nullable|in:recomendacion,mejora,queja',
            'estado' => 'nullable|in:nuevo,leido,resuelto',
        ]);

        $base = fn () => BuzonMensaje::with(['user', 'negocios', 'respondidoPor'])
            ->whereNull('archivado_at')
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

    // Todos los mensajes de siempre, incluso los quitados del buzón
    public function historial(Request $request)
    {
        $request->validate([
            'tipo_mensaje' => 'nullable|in:recomendacion,mejora,queja',
            'estado' => 'nullable|in:nuevo,leido,resuelto',
            'desde' => 'nullable|date_format:Y-m-d',
            'hasta' => 'nullable|date_format:Y-m-d|after_or_equal:desde',
            'q' => 'nullable|string|max:100',
        ]);

        $mensajes = BuzonMensaje::with(['user', 'negocios', 'respondidoPor'])
            ->when($request->filled('tipo_mensaje'), fn ($q) => $q->where('tipo', $request->tipo_mensaje))
            ->when($request->filled('estado'), fn ($q) => $q->where('estado', $request->estado))
            ->when($request->filled('desde'), fn ($q) => $q->whereDate('created_at', '>=', $request->desde))
            ->when($request->filled('hasta'), fn ($q) => $q->whereDate('created_at', '<=', $request->hasta))
            ->when($request->filled('q'), function ($q) use ($request) {
                $texto = trim($request->q);

                $q->where(function ($w) use ($texto) {
                    $w->where('mensaje', 'like', '%' . $texto . '%')
                        ->orWhere('negocios_texto', 'like', '%' . $texto . '%')
                        ->orWhereHas('user', fn ($u) => $u->where('name', 'like', '%' . $texto . '%'));

                    if (preg_match('/^(neg-?)?\d+$/i', $texto)) {
                        $w->orWhereHas('negocios', fn ($n) => $n->withTrashed()->where('negocios.id', Negocio::idDesdeCodigo($texto)));
                    }
                });
            })
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.buzon.historial', compact('mensajes'));
    }

    public function responder(Request $request, BuzonMensaje $mensaje)
    {
        $datos = $request->validate(['respuesta' => 'required|string|min:3|max:2000']);

        $mensaje->update([
            'respuesta' => $datos['respuesta'],
            'respondido_at' => now(),
            'respondido_por' => auth()->id(),
            'estado' => $request->boolean('resuelto') ? 'resuelto' : 'leido',
        ]);

        return back()->with('exito', 'Respuesta enviada. El usuario la verá en "Mis solicitudes".');
    }

    public function estado(Request $request, BuzonMensaje $mensaje)
    {
        $datos = $request->validate(['estado' => 'required|in:nuevo,leido,resuelto']);

        $mensaje->update($datos);

        return back();
    }

    // "Eliminar" lo saca del buzón, pero queda en el historial
    public function destroy(BuzonMensaje $mensaje)
    {
        $mensaje->update(['archivado_at' => now()]);

        return back()->with('exito', 'Mensaje quitado del buzón. Sigue en el historial.');
    }

    public function restaurar(BuzonMensaje $mensaje)
    {
        $mensaje->update(['archivado_at' => null]);

        return back()->with('exito', 'Mensaje devuelto al buzón.');
    }
}