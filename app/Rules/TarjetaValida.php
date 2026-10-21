<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class TarjetaValida implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $numero = preg_replace('/\D/', '', $value);

        if (strlen($numero) < 13 || strlen($numero) > 19) {
            $fail('El número de tarjeta no es válido.');
            return;
        }

        $suma = 0;
        $alterna = false;

        for ($i = strlen($numero) - 1; $i >= 0; $i--) {
            $digito = (int) $numero[$i];

            if ($alterna) {
                $digito *= 2;
                if ($digito > 9) {
                    $digito -= 9;
                }
            }

            $suma += $digito;
            $alterna = ! $alterna;
        }

        if ($suma % 10 !== 0) {
            $fail('La tarjeta fue rechazada. Verifica el número.');
        }
    }
}