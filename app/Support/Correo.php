<?php

namespace App\Support;

use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;

class Correo
{
    public static function enviar(?string $para, Mailable $correo): bool
    {
        if (! $para) {
            return false;
        }

        try {
            Mail::to($para)->send($correo);

            return true;
        } catch (\Throwable $e) {
            report($e);

            return false;
        }
    }
}