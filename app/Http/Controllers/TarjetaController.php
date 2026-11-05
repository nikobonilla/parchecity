<?php

namespace App\Http\Controllers;

use App\Models\Tarjeta;
use Illuminate\Http\Request;

class TarjetaController extends Controller
{
    public function index()
    {
        $tarjetas = auth()->user()->tarjetas()->latest()->get();

        return view('cliente.tarjetas.index', compact('tarjetas'));
    }

    public function store(Request $request)
    {
        $datos = $request->validate(Tarjeta::reglas(), Tarjeta::mensajes());

        if (Tarjeta::venceEn((int) $datos['mes'], (int) $datos['ano'])->lt(now())) {
            return back()->withInput()->withErrors(['mes' => 'Esa tarjeta ya está vencida.']);
        }

        Tarjeta::crearPara(auth()->user(), $datos);

        return back()->with('exito', 'Tarjeta guardada.');
    }

    public function edit(Tarjeta $tarjeta)
    {
        abort_unless($tarjeta->user_id === auth()->id(), 403);

        return view('cliente.tarjetas.editar', compact('tarjeta'));
    }

    // El número no se puede cambiar (no lo guardamos). Para otro número, agrega una tarjeta nueva.
    public function update(Request $request, Tarjeta $tarjeta)
    {
        abort_unless($tarjeta->user_id === auth()->id(), 403);

        $datos = $request->validate([
            'tipo' => 'required|in:credito,debito',
            'titular' => 'required|string|max:255',
            'mes' => 'required|integer|between:1,12',
            'ano' => 'required|integer|between:0,2100',
        ], Tarjeta::mensajes());

        if (Tarjeta::venceEn((int) $datos['mes'], (int) $datos['ano'])->lt(now())) {
            return back()->withInput()->withErrors(['mes' => 'Esa fecha de vencimiento ya pasó.']);
        }

        $tarjeta->update([
            'tipo' => $datos['tipo'],
            'titular' => $datos['titular'],
            'mes' => (int) $datos['mes'],
            'ano' => Tarjeta::normalizarAno((int) $datos['ano']),
        ]);

        return redirect()->route('cliente.tarjetas.index')->with('exito', 'Tarjeta actualizada.');
    }

    public function destroy(Tarjeta $tarjeta)
    {
        abort_unless($tarjeta->user_id === auth()->id(), 403);

        $tarjeta->delete();

        return back()->with('exito', 'Tarjeta eliminada.');
    }
}