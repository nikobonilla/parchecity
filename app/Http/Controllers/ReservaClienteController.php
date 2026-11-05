<?php

namespace App\Http\Controllers;

use App\Models\Reserva;
use App\Services\CancelacionService;
use Illuminate\Http\Request;

class ReservaClienteController extends Controller
{
    public function __construct(private CancelacionService $cancelacion) {}

    public function index(Request $request)
    {
        Reserva::expirarImpagas();

        $hoy = now()->toDateString();

        $reservas = Reserva::with(['negocio', 'tipoHabitacion'])
            ->where('user_id', auth()->id())
            ->where('oculta_cliente', false)
            ->when(! $request->boolean('pasadas'), fn ($q) => $q->where(
                fn ($w) => $w->where('fecha', '>=', $hoy)->orWhere('fecha_salida', '>=', $hoy)
            ))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        return view('cliente.reservas.index', compact('reservas'));
    }

    public function historial(Request $request)
    {
        $request->validate([
            'desde' => 'nullable|date_format:Y-m-d',
            'hasta' => 'nullable|date_format:Y-m-d|after_or_equal:desde',
            'negocio' => 'nullable|string|max:100',
        ]);

        $reservas = Reserva::with(['negocio', 'tipoHabitacion'])
            ->where('user_id', auth()->id())
            ->when($request->filled('desde'), fn ($q) => $q->where('fecha', '>=', $request->desde))
            ->when($request->filled('hasta'), fn ($q) => $q->where('fecha', '<=', $request->hasta))
            ->when($request->filled('negocio'), fn ($q) => $q->whereHas(
                'negocio', fn ($n) => $n->where('nombre', 'like', '%' . $request->negocio . '%')
            ))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('cliente.reservas.historial', compact('reservas'));
    }

    public function editar(Reserva $reserva)
    {
        abort_unless($reserva->user_id === auth()->id(), 403);
        abort_unless($reserva->estaActiva(), 404);

        return view('cliente.reservas.editar', compact('reserva'));
    }

    // Solo se editan los datos de contacto. La fecha, la hora y las personas cambian el cupo,
    // por eso para cambiarlos se cancela y se crea otra reserva.
    public function actualizar(Request $request, Reserva $reserva)
    {
        abort_unless($reserva->user_id === auth()->id(), 403);
        abort_unless($reserva->estaActiva(), 404);

        $datos = $request->validate([
            'nombre_cliente' => 'required|string|max:255',
            'correo_cliente' => 'required|email|max:255',
            'telefono' => 'required|string|max:20',
            'comentario' => 'nullable|string|max:500',
        ]);

        $reserva->update($datos);

        return redirect()->route('cliente.reservas.index')->with('exito', 'Los datos de la reserva se actualizaron.');
    }

    public function cancelar(Reserva $reserva)
    {
        abort_unless($reserva->user_id === auth()->id(), 403);

        $mensaje = $this->cancelacion->cancelar($reserva, 'cliente');

        return back()->with('exito', $mensaje ?? 'Esa reserva ya no estaba activa.');
    }

    public function eliminar(Reserva $reserva)
    {
        abort_unless($reserva->user_id === auth()->id(), 403);

        $aviso = $this->cancelacion->cancelar($reserva, 'cliente');
        $reserva->update(['oculta_cliente' => true]);

        return back()->with('exito', 'Reserva eliminada de tu lista. Sigue disponible en tu historial.' . ($aviso ? ' ' . $aviso : ''));
    }
}