<?php

namespace App\Http\Controllers;

use App\Models\Negocio;
use App\Services\NegocioAccionesService;

class NegocioEstadoController extends Controller
{
    public function __construct(private NegocioAccionesService $acciones) {}

    public function congelar(Negocio $negocio)
    {
        $this->autorizar($negocio);

        $this->acciones->congelar($negocio, 'dueno');

        return back()->with('exito', 'Negocio congelado. Sigue visible pero no recibe reservas nuevas.');
    }

    public function descongelar(Negocio $negocio)
    {
        $this->autorizar($negocio);

        $error = $this->acciones->descongelar($negocio, 'dueno');

        return $error
            ? back()->withErrors(['negocio' => $error])
            : back()->with('exito', 'Negocio descongelado. Vuelve a recibir reservas.');
    }

    public function destroy(Negocio $negocio)
    {
        $this->autorizar($negocio);

        $canceladas = $this->acciones->eliminar($negocio);

        $mensaje = 'Negocio eliminado.';
        if ($canceladas > 0) {
            $mensaje .= " Se cancelaron {$canceladas} reservas activas (las pagadas se reembolsaron y los clientes recibieron su correo).";
        }

        return redirect()->route('dueno.negocios.index')->with('exito', $mensaje);
    }

    private function autorizar(Negocio $negocio): void
    {
        abort_unless($negocio->user_id === auth()->id(), 403);
    }
}