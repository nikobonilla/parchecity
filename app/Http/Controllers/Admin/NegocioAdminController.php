<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Negocio;
use App\Services\AprobacionService;
use App\Services\NegocioAccionesService;
use Illuminate\Http\Request;

class NegocioAdminController extends Controller
{
    public function __construct(
        private AprobacionService $aprobacionService,
        private NegocioAccionesService $acciones,
    ) {}

    public function pendientes()
    {
        $negocios = Negocio::with('duenio')->where('estado', 'pendiente')->get();

        return view('admin.negocios.pendientes', compact('negocios'));
    }

    public function index(Request $request)
    {
        $request->validate([
            'tipo' => 'nullable|in:restaurante,hotel,motel',
            'q' => 'nullable|string|max:100',
        ]);

        $negocios = Negocio::with('duenio')
            ->when($request->filled('tipo'), fn ($q) => $q->where('tipo', $request->tipo))
            ->when($request->filled('q'), function ($q) use ($request) {
                $texto = trim($request->q);
                $esId = preg_match('/^(neg-?)?\d+$/i', $texto) === 1;

                $q->where(function ($w) use ($texto, $esId) {
                    $w->where('nombre', 'like', '%' . $texto . '%');

                    if ($esId) {
                        $w->orWhere('id', Negocio::idDesdeCodigo($texto));
                    }
                });
            })
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

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

    public function editar(Negocio $negocio)
    {
        return view('admin.negocios.editar', compact('negocio'));
    }

    // El admin edita los datos básicos. Fotos, tarifas y habitaciones las gestiona el dueño.
    public function actualizar(Request $request, Negocio $negocio)
    {
        $datos = $request->validate([
            'nombre' => 'required|string|max:255',
            'tipo' => 'required|in:restaurante,hotel,motel',
            'direccion' => 'required|string|max:255',
            'ubicacion' => 'nullable|string|max:255',
            'descripcion' => 'required|string',
            'contacto' => 'required|string|max:255',
            'whatsapp' => 'nullable|string|max:20',
            'hora_apertura' => 'nullable|date_format:H:i,H:i:s',
            'hora_cierre' => 'nullable|date_format:H:i,H:i:s',
            'minutos_liberacion' => 'required|integer|min:10|max:1440',
        ]);

        $negocio->update($datos);

        return redirect()->route('admin.negocios.index')->with('exito', 'Negocio actualizado.');
    }

    public function congelar(Negocio $negocio)
    {
        $this->acciones->congelar($negocio, 'admin');

        return back()->with('exito', 'Negocio congelado por la administración. El dueño no podrá descongelarlo.');
    }

    public function descongelar(Negocio $negocio)
    {
        $this->acciones->descongelar($negocio, 'admin');

        return back()->with('exito', 'Negocio descongelado.');
    }

    public function destroy(Negocio $negocio)
    {
        $canceladas = $this->acciones->eliminar($negocio);

        $mensaje = 'Negocio eliminado.';
        if ($canceladas > 0) {
            $mensaje .= " Se cancelaron {$canceladas} reservas activas (las pagadas se reembolsaron).";
        }

        return back()->with('exito', $mensaje);
    }
}