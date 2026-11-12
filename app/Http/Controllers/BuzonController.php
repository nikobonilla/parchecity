<?php

namespace App\Http\Controllers;

use App\Models\BuzonMensaje;
use App\Models\Negocio;
use Illuminate\Http\Request;

class BuzonController extends Controller
{
    public function crear()
    {
        return view('buzon.crear');
    }

    // Lo que el usuario no ha eliminado de su lista
    public function mis(Request $request)
    {
        $request->validate(['estado' => 'nullable|in:pendientes,resueltas']);

        $mensajes = BuzonMensaje::with(['negocios', 'respondidoPor'])
            ->where('user_id', auth()->id())
            ->whereNull('oculto_usuario_at')
            ->when($request->estado === 'pendientes', fn ($q) => $q->whereIn('estado', ['nuevo', 'leido']))
            ->when($request->estado === 'resueltas', fn ($q) => $q->where('estado', 'resuelto'))
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return view('buzon.mis-solicitudes', compact('mensajes'));
    }

    // Todo lo que el usuario ha enviado, incluso lo que eliminó de su lista
    public function historial(Request $request)
    {
        $request->validate([
            'tipo' => 'nullable|in:recomendacion,mejora,queja',
            'estado' => 'nullable|in:nuevo,leido,resuelto',
            'desde' => 'nullable|date_format:Y-m-d',
            'hasta' => 'nullable|date_format:Y-m-d|after_or_equal:desde',
            'q' => 'nullable|string|max:100',
        ]);

        $mensajes = BuzonMensaje::with(['negocios', 'respondidoPor'])
            ->where('user_id', auth()->id())
            ->when($request->filled('tipo'), fn ($q) => $q->where('tipo', $request->tipo))
            ->when($request->filled('estado'), fn ($q) => $q->where('estado', $request->estado))
            ->when($request->filled('desde'), fn ($q) => $q->whereDate('created_at', '>=', $request->desde))
            ->when($request->filled('hasta'), fn ($q) => $q->whereDate('created_at', '<=', $request->hasta))
            ->when($request->filled('q'), function ($q) use ($request) {
                $texto = trim($request->q);

                $q->where(function ($w) use ($texto) {
                    $w->where('mensaje', 'like', '%' . $texto . '%')
                        ->orWhere('negocios_texto', 'like', '%' . $texto . '%')
                        ->orWhereHas('negocios', fn ($n) => $n->withTrashed()->where('nombre', 'like', '%' . $texto . '%'));

                    if (preg_match('/^(neg-?)?\d+$/i', $texto)) {
                        $w->orWhereHas('negocios', fn ($n) => $n->withTrashed()->where('negocios.id', Negocio::idDesdeCodigo($texto)));
                    }
                });
            })
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('buzon.historial', compact('mensajes'));
    }

    // "Eliminar" lo quita de Mis solicitudes, pero queda en el historial
    public function eliminar(BuzonMensaje $mensaje)
    {
        abort_unless($mensaje->user_id === auth()->id(), 403);

        $mensaje->update(['oculto_usuario_at' => now()]);

        return back()->with('exito', 'Mensaje eliminado de tu lista. Sigue en tu historial.');
    }

    public function restaurar(BuzonMensaje $mensaje)
    {
        abort_unless($mensaje->user_id === auth()->id(), 403);

        $mensaje->update(['oculto_usuario_at' => null]);

        return back()->with('exito', 'Mensaje devuelto a tus solicitudes.');
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'tipo' => 'required|in:recomendacion,mejora,queja',
            'negocios_texto' => 'nullable|string|max:255',
            'negocios_ids' => 'nullable|string|max:255',
            'mensaje' => 'required|string|min:10|max:2000',
        ], [
            'mensaje.min' => 'Cuéntanos un poco más (mínimo 10 caracteres).',
        ]);

        // Los IDs pueden venir varios, separados por comas o espacios
        $ids = [];
        $noEncontrados = [];

        foreach (preg_split('/[\s,;]+/', (string) ($datos['negocios_ids'] ?? ''), -1, PREG_SPLIT_NO_EMPTY) as $codigo) {
            $id = Negocio::idDesdeCodigo($codigo);

            if ($id && Negocio::whereKey($id)->exists()) {
                $ids[] = $id;
            } else {
                $noEncontrados[] = $codigo;
            }
        }

        if ($noEncontrados) {
            return back()->withInput()->withErrors([
                'negocios_ids' => 'No encontramos estos IDs: ' . implode(', ', $noEncontrados) . '. Revisa que estén bien escritos (ej: NEG-0004).',
            ]);
        }

        $mensaje = BuzonMensaje::create([
            'user_id' => auth()->id(),
            'tipo' => $datos['tipo'],
            'negocios_texto' => $datos['negocios_texto'] ?? null,
            'mensaje' => $datos['mensaje'],
        ]);

        $mensaje->negocios()->sync(array_unique($ids));

        return redirect()->route('buzon.mis')->with('exito', '¡Gracias! Tu mensaje llegó a la administración. Aquí puedes seguir su estado y ver la respuesta.');
    }
}