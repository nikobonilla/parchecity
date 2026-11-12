<?php

namespace App\Support;

use App\Models\User;
use App\Notifications\Aviso;

class Avisos
{
    // Avisa a un usuario. Si algo falla, no rompe lo que se estaba haciendo.
    public static function a(?User $usuario, string $categoria, string $titulo, string $mensaje, ?string $url = null, string $nivel = 'info'): void
    {
        if (! $usuario) {
            return;
        }

        try {
            $usuario->notify(new Aviso($categoria, $titulo, $mensaje, self::relativa($url), $nivel));
        } catch (\Throwable $e) {
            report($e);
        }
    }

    // El administrador recibe todo lo que pasa en la página
    public static function aAdmins(string $categoria, string $titulo, string $mensaje, ?string $url = null, string $nivel = 'info', ?int $excepto = null): void
    {
        User::where('rol', 'administrador')
            ->when($excepto, fn ($q) => $q->where('id', '!=', $excepto))
            ->get()
            ->each(fn ($admin) => self::a($admin, $categoria, $titulo, $mensaje, $url, $nivel));
    }

    // Guarda solo la ruta (sin dominio) para que el enlace funcione desde cualquier dirección
    private static function relativa(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        $ruta = parse_url($url, PHP_URL_PATH) ?: '/';
        $consulta = parse_url($url, PHP_URL_QUERY);

        return $ruta . ($consulta ? '?' . $consulta : '');
    }
}