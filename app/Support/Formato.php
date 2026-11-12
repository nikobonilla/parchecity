<?php

namespace App\Support;

use Carbon\Carbon;

class Formato
{
    private const TILDES = [
        'Bogota' => 'Bogotá', 'Medellin' => 'Medellín', 'Cucuta' => 'Cúcuta', 'Popayan' => 'Popayán',
        'Ibague' => 'Ibagué', 'Monteria' => 'Montería', 'Quibdo' => 'Quibdó', 'Mitu' => 'Mitú',
        'Inirida' => 'Inírida', 'Jose' => 'José', 'Andres' => 'Andrés', 'Maria' => 'María',
        'Atlantico' => 'Atlántico', 'Bolivar' => 'Bolívar', 'Boyaca' => 'Boyacá', 'Caqueta' => 'Caquetá',
        'Cordoba' => 'Córdoba', 'Choco' => 'Chocó', 'Guainia' => 'Guainía', 'Narino' => 'Nariño',
        'Quindio' => 'Quindío', 'Vaupes' => 'Vaupés', 'Archipielago' => 'Archipiélago', 'Carreno' => 'Carreño',
    ];

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

    // "VALLE DEL CAUCA" → "Valle del Cauca", "BOGOTA, D.C." → "Bogotá, D.C."
    public static function lugar(?string $nombre): string
    {
        if ($nombre === null || $nombre === '') {
            return '';
        }

        $texto = mb_convert_case(mb_strtolower($nombre), MB_CASE_TITLE);
        $texto = preg_replace_callback('/\s(De|Del|La|Las|Los|Y|El)(?=\s)/u', fn ($m) => mb_strtolower($m[0]), $texto);
        $texto = preg_replace_callback('/\p{L}+/u', fn ($m) => self::TILDES[$m[0]] ?? $m[0], $texto);

        return str_replace('D.c.', 'D.C.', $texto);
    }
}