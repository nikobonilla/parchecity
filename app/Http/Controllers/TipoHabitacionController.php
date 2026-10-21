<?php

namespace App\Http\Controllers;

use App\Models\CategoriaHabitacion;
use App\Models\ImagenHabitacion;
use App\Models\Negocio;
use App\Models\TipoHabitacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TipoHabitacionController extends Controller
{
    public function index(Negocio $negocio)
    {
        abort_unless($negocio->user_id === auth()->id(), 403);
        abort_if($negocio->tipo === 'restaurante', 404);

        $tipos = $negocio->tiposHabitacion()->with(['categoria', 'imagenes'])->get()
            ->sortBy(fn ($t) => ($t->categoria->nombre ?? 'zzz') . $t->nombre)
            ->groupBy(fn ($t) => $t->categoria->nombre ?? 'Sin categoría');

        $categorias = $negocio->categoriasHabitacion()
            ->withCount('tiposHabitacion')
            ->orderBy('nombre')
            ->get();

        return view('dueno.habitaciones.index', compact('negocio', 'tipos', 'categorias'));
    }

    public function store(Request $request, Negocio $negocio)
    {
        abort_unless($negocio->user_id === auth()->id(), 403);

        $datos = $this->validar($request, $negocio);
        $imagenes = $request->file('imagenes', []);
        unset($datos['imagenes']);

        $tipo = $negocio->tiposHabitacion()->create($datos);
        $this->guardarImagenes($tipo, $imagenes ?? []);

        return back()->with('exito', 'Habitación creada.');
    }

    public function update(Request $request, TipoHabitacion $tipoHabitacion)
    {
        abort_unless($tipoHabitacion->negocio->user_id === auth()->id(), 403);

        $datos = $this->validar($request, $tipoHabitacion->negocio);
        $imagenes = $request->file('imagenes', []);
        unset($datos['imagenes']);

        $tipoHabitacion->update($datos);
        $this->guardarImagenes($tipoHabitacion, $imagenes ?? []);

        return back()->with('exito', 'Habitación actualizada.');
    }

    public function destroy(TipoHabitacion $tipoHabitacion)
    {
        abort_unless($tipoHabitacion->negocio->user_id === auth()->id(), 403);

        foreach ($tipoHabitacion->imagenes as $imagen) {
            Storage::disk('public')->delete($imagen->ruta);
        }

        $tipoHabitacion->delete();

        return back()->with('exito', 'Habitación eliminada.');
    }

    public function eliminarImagen(ImagenHabitacion $imagenHabitacion)
    {
        abort_unless($imagenHabitacion->tipoHabitacion->negocio->user_id === auth()->id(), 403);

        Storage::disk('public')->delete($imagenHabitacion->ruta);
        $imagenHabitacion->delete();

        return back()->with('exito', 'Imagen eliminada.');
    }

    public function storeCategoria(Request $request, Negocio $negocio)
    {
        abort_unless($negocio->user_id === auth()->id(), 403);

        $datos = $request->validate(
            ['nombre_categoria' => 'required|string|max:100'],
            ['nombre_categoria.required' => 'Escribe el nombre de la categoría.']
        );

        $nombre = trim(preg_replace('/\s+/', ' ', $datos['nombre_categoria']));

        $negocio->categoriasHabitacion()->firstOrCreate(['nombre' => $nombre]);

        return back()->with('exito', 'Categoría guardada.');
    }

    public function destroyCategoria(CategoriaHabitacion $categoria)
    {
        abort_unless($categoria->negocio->user_id === auth()->id(), 403);

        if ($categoria->tiposHabitacion()->exists()) {
            return back()->withErrors(['categoria' => 'No puedes eliminar una categoría que todavía tiene habitaciones.']);
        }

        $categoria->delete();

        return back()->with('exito', 'Categoría eliminada.');
    }

    private function guardarImagenes(TipoHabitacion $tipo, array $imagenes): void
    {
        foreach ($imagenes as $imagen) {
            $ruta = $imagen->store('habitaciones', 'public');
            $tipo->imagenes()->create(['ruta' => $ruta]);
        }
    }

    private function validar(Request $request, Negocio $negocio): array
    {
        $datos = $request->validate([
            'nombre' => 'required|string|max:255',
            'categoria_id' => ['required', Rule::exists('categorias_habitacion', 'id')->where('negocio_id', $negocio->id)],
            'capacidad' => 'nullable|integer|between:1,20',
            'especificaciones' => 'nullable|string|max:500',
            'precio_hora' => 'nullable|numeric|min:1',
            'precio_noche' => 'nullable|numeric|min:1',
            'imagenes.*' => 'nullable|image|max:5120',
        ], [
            'categoria_id.required' => 'Elige una categoría. Si no tienes, créala primero en "Nueva categoría".',
            'categoria_id.exists' => 'La categoría elegida no es válida.',
        ]);

        if (empty($datos['precio_hora']) && empty($datos['precio_noche'])) {
            throw ValidationException::withMessages([
                'precio_hora' => 'Pon al menos un precio: por hora, por noche, o los dos.',
            ]);
        }

        return $datos;
    }
}