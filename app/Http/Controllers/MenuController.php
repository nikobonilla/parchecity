<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Negocio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MenuController extends Controller
{
    public function store(Request $request, Negocio $negocio)
    {
        abort_unless($negocio->user_id === auth()->id(), 403);
        abort_unless($negocio->tipo === 'restaurante', 404);

        $request->validate([
            'archivos' => 'required|array|max:10',
            'archivos.*' => 'file|mimes:jpg,jpeg,png,webp,pdf|max:10240',
            'titulo' => 'nullable|string|max:100',
        ], [
            'archivos.required' => 'Elige al menos un archivo (foto o PDF).',
            'archivos.*.mimes' => 'El menú debe ser una foto (JPG, PNG o WEBP) o un PDF.',
            'archivos.*.max' => 'Cada archivo puede pesar máximo 10 MB.',
        ]);

        foreach ($request->file('archivos') as $archivo) {
            $negocio->menus()->create([
                'titulo' => $request->filled('titulo') ? $request->titulo : null,
                'ruta' => $archivo->store('menus', 'public'),
                'tipo' => $archivo->getMimeType() === 'application/pdf' ? 'pdf' : 'imagen',
            ]);
        }

        return back()->with('exito', 'Menú subido.');
    }

    public function update(Request $request, Menu $menu)
    {
        abort_unless($menu->negocio->user_id === auth()->id(), 403);

        $datos = $request->validate(['titulo' => 'nullable|string|max:100']);

        $menu->update(['titulo' => $datos['titulo'] ?? null]);

        return back()->with('exito', 'Título actualizado.');
    }

    public function destroy(Menu $menu)
    {
        abort_unless($menu->negocio->user_id === auth()->id(), 403);

        Storage::disk('public')->delete($menu->ruta);
        $menu->delete();

        return back()->with('exito', 'Archivo del menú eliminado.');
    }
}