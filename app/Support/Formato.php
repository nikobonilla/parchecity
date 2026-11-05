<?php

namespace App\Support;

use Carbon\Carbon;

class Formato
{
    // 20000 → "$20.000 COP"
    public static function cop($valor): string
    {
        if ($valor === null || $valor === '') {
            return '-';
        }

        return '$' . number_format((float) $valor, 0, ',', '.') . ' COP';
    }

    // "14:30", "14:30:00" o una fecha → "2:30 p. m."
    public static function hora($valor): string
    {
        if ($valor === null || $valor === '') {
            return '-';
        }

        $c = $valor instanceof \DateTimeInterface
            ? Carbon::instance($valor)
            : Carbon::createFromFormat(substr_count((string) $valor, ':') === 2 ? 'H:i:s' : 'H:i', (string) $valor);

        return $c->format('g:i') . ' ' . ($c->hour < 12 ? 'a. m.' : 'p. m.');
    }
}