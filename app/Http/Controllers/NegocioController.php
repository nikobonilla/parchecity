<?php

namespace App\Http\Controllers;

use App\Models\Negocio;
use App\Models\Imagen;
use App\Services\NegocioService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class NegocioController extends Controller
{
    public function __construct(private NegocioService $negocioService) {}

    public function index()
    {
        $negocios = Negocio::where('user_id', auth()->id())->get();
        return view('dueno.negocios.index', compact('negocios'));
    }

    public function create()
    {
        return view('dueno.negocios.create');
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'nombre' => 'required|string|max:255',
            'tipo' => 'required|in:restaurante,hotel,motel',
            'direccion' => 'required|string|max:255',
            'ubicacion' => 'nullable|string|max:255',
            'descripcion' => 'required|string',
            'contacto' => 'required|string|max:255',
            'whatsapp' => 'nullable|string|max:20',
            'hora_apertura' => 'nullable|date_format:H:i',
            'hora_cierre' => 'nullable|date_format:H:i',
            'medio_pago' => 'nullable|string|max:255',
            'imagenes.*' => 'nullable|image|max:5120',
        ]);

        $imagenes = $request->file('imagenes', []);
        unset($datos['imagenes']);

        $this->negocioService->registrar($datos, auth()->id(), $imagenes ?? []);

        return redirect()->route('dueno.negocios.index')->with('exito', 'Negocio registrado. Queda pendiente de aprobación.');
    }

    public function edit(Negocio $negocio)
    {
        abort_unless($negocio->user_id === auth()->id(), 403);
        return view('dueno.negocios.editar', compact('negocio'));
    }

    public function update(Request $request, Negocio $negocio)
    {
        abort_unless($negocio->user_id === auth()->id(), 403);

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
            'medio_pago' => 'nullable|string|max:255',
            'imagenes.*' => 'nullable|image|max:5120',
        ]);

        $imagenes = $request->file('imagenes', []);
        unset($datos['imagenes']);

        $negocio->update($datos);

        foreach ($imagenes ?? [] as $imagen) {
            $ruta = $imagen->store('negocios', 'public');
            $negocio->imagenes()->create(['ruta' => $ruta]);
        }

        return redirect()->route('dueno.negocios.index')->with('exito', 'Negocio actualizado.');
    }

    public function eliminarImagen(Imagen $imagen)
    {
        abort_unless($imagen->negocio->user_id === auth()->id(), 403);

        Storage::disk('public')->delete($imagen->ruta);
        $imagen->delete();

        return back()->with('exito', 'Imagen eliminada.');
    }
}