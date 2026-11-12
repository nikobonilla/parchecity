<?php

namespace App\Observers;

use App\Models\BuzonMensaje;
use App\Support\Avisos;

class BuzonMensajeObserver
{
    public function created(BuzonMensaje $mensaje): void
    {
        $etiqueta = ['recomendacion' => 'Recomendación', 'mejora' => 'Mejora', 'queja' => 'Queja'][$mensaje->tipo];

        Avisos::aAdmins('buzon', 'Nuevo mensaje en el buzón',
            $etiqueta . ' de ' . ($mensaje->user->name ?? 'un usuario') . '.',
            route('admin.buzon.index'), $mensaje->tipo === 'queja' ? 'danger' : 'info');
    }

    public function updated(BuzonMensaje $mensaje): void
    {
        if ($mensaje->wasChanged('respuesta') && $mensaje->respuesta) {
            Avisos::a($mensaje->user, 'buzon', 'Respondieron tu solicitud',
                'La administración respondió tu mensaje del buzón.', route('buzon.mis'), 'success');
        }
    }
}