<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class Aviso extends Notification
{
    public function __construct(
        public string $categoria,
        public string $titulo,
        public string $mensaje,
        public ?string $url = null,
        public string $nivel = 'info',
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'categoria' => $this->categoria,
            'titulo' => $this->titulo,
            'mensaje' => $this->mensaje,
            'url' => $this->url,
            'nivel' => $this->nivel,
        ];
    }
}