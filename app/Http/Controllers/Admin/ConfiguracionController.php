<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Configuracion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ConfiguracionController extends Controller
{
    public function edit()
    {
        $configuracion = Configuracion::obtener();

        return view('admin.configuracion', compact('configuracion'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'logo' => 'nullable|image|max:5120',
            'imagen_fondo' => 'nullable|image|max:5120',
            'imagen_home_1' => 'nullable|image|max:5120',
            'imagen_home_2' => 'nullable|image|max:5120',
            'whatsapp_soporte' => 'nullable|string|max:25',
        ]);

        $configuracion = Configuracion::obtener();

        foreach (['logo', 'imagen_fondo', 'imagen_home_1', 'imagen_home_2'] as $campo) {
            if ($request->hasFile($campo)) {
                if ($configuracion->$campo) {
                    Storage::disk('public')->delete($configuracion->$campo);
                }
                $configuracion->$campo = $request->file($campo)->store('configuracion', 'public');
            }
        }

        $configuracion->whatsapp_soporte = $request->filled('whatsapp_soporte') ? $request->whatsapp_soporte : null;

        $configuracion->save();

        return back()->with('exito', 'Configuración actualizada.');
    }
}