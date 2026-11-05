<?php

namespace App\Http\Controllers;

use App\Models\Negocio;
use App\Models\Reserva;
use App\Services\CancelacionService;
use Illuminate\Http\Request;

class ReservaDuenoController extends Controller
{
    public function __construct(private CancelacionService $cancelacion) {}

    public function index(Request $request)
    {
        Reserva::expirarImpagas();

        $hoy = now()->toDateString();

        $reservas = Reserva::with(['negocio', 'tipoHabitacion'])
            ->whereIn('negocio_id', $this->misNegocios())
            ->where('oculta_dueno', false)
            ->when(! $request->boolean('pasadas'), fn ($q) => $q->where(
                fn ($w) => $w->where('fecha', '>=', $hoy)->orWhere('fecha_salida', '>=', $hoy)
            ))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        return view('dueno.reservas.index', compact('reservas'));
    }

    public function historial(Request $request)
    {
        $request->validate([
            'desde' => 'nullable|date_format:Y-m-d',
            'hasta' => 'nullable|date_format:Y-m-d|after_or_equal:desde',
            'negocio' => 'nullable|string|max:100',
        ]);

        $reservas = Reserva::with(['negocio', 'tipoHabitacion'])
            ->whereIn('negocio_id', $this->misNegocios())
            ->when($request->filled('desde'), fn ($q) => $q->where('fecha', '>=', $request->desde))
            ->when($request->filled('hasta'), fn ($q) => $q->where('fecha', '<=', $request->hasta))
            ->when($request->filled('negocio'), fn ($q) => $q->whereHas(
                'negocio', fn ($n) => $n->where('nombre', 'like', '%' . $request->negocio . '%')
            ))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('dueno.reservas.historial', compact('reservas'));
    }

    public function cancelar(Reserva $reserva)
    {
        $this->autorizar($reserva);

        $mensaje = $this->cancelacion->cancelar($reserva, 'negocio');

        return back()->with('exito', $mensaje ?? 'Esa reserva ya no estaba activa.');
    }

    // Libera la mesa o habitación antes de tiempo (por ejemplo, el cliente se fue antes)
    public function liberar(Reserva $reserva)
    {
        $this->autorizar($reserva);

        if ($reserva->estado === 'confirmada' && ! $reserva->liberada_at) {
            $reserva->update(['liberada_at' => now()]);
        }

        return back()->with('exito', 'Reserva liberada. Ese cupo vuelve a estar disponible.');
    }

    public function eliminar(Reserva $reserva)
    {
        $this->autorizar($reserva);

        $aviso = $this->cancelacion->cancelar($reserva, 'negocio');
        $reserva->update(['oculta_dueno' => true]);

        return back()->with('exito', 'Reserva eliminada de tu lista. Sigue disponible en tu historial.' . ($aviso ? ' ' . $aviso : ''));
    }

    private function misNegocios()
    {
        return Negocio::where('user_id', auth()->id())->pluck('id');
    }

    private function autorizar(Reserva $reserva): void
    {
        abort_unless($reserva->negocio->user_id === auth()->id(), 403);
    }
}