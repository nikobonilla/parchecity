<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['tipo', 'marca', 'ultimos4', 'titular', 'mes', 'ano'])]
class Tarjeta extends Model
{
    public function user() { return $this->belongsTo(User::class); }

    public function vencida(): bool
    {
        return self::venceEn($this->mes, $this->ano)->lt(now());
    }

    // Ej: "Visa crédito •••• 4242"
    public function etiqueta(): string
    {
        return $this->marca . ' ' . ($this->tipo === 'credito' ? 'crédito' : 'débito') . ' •••• ' . $this->ultimos4;
    }

    public static function reglas(): array
    {
        return [
            'tipo' => 'required|in:credito,debito',
            'numero' => ['required', function ($atributo, $valor, $fallar) {
                $digitos = preg_replace('/\D/', '', (string) $valor);
                if (strlen($digitos) < 12 || strlen($digitos) > 19) {
                    $fallar('El número de la tarjeta debe tener entre 12 y 19 dígitos.');
                }
            }],
            'titular' => 'required|string|max:255',
            'mes' => 'required|integer|between:1,12',
            'ano' => 'required|integer|between:0,2100',
        ];
    }

    public static function mensajes(): array
    {
        return [
            'tipo.required' => 'Elige si la tarjeta es de crédito o de débito.',
            'numero.required' => 'Escribe el número de la tarjeta.',
            'titular.required' => 'Escribe el nombre del titular.',
            'mes.required' => 'Escribe el mes de vencimiento.',
            'mes.between' => 'El mes debe estar entre 1 y 12.',
            'ano.required' => 'Escribe el año de vencimiento.',
        ];
    }

    // Acepta el año con 2 dígitos (28) o con 4 (2028)
    public static function normalizarAno(int $ano): int
    {
        return $ano < 100 ? $ano + 2000 : $ano;
    }

    public static function venceEn(int $mes, int $ano): Carbon
    {
        return Carbon::create(self::normalizarAno($ano), $mes, 1)->endOfMonth();
    }

    public static function marcaDe(string $digitos): string
    {
        if (preg_match('/^4/', $digitos)) {
            return 'Visa';
        }
        if (preg_match('/^(5[1-5]|2[2-7])/', $digitos)) {
            return 'Mastercard';
        }
        if (preg_match('/^3[47]/', $digitos)) {
            return 'American Express';
        }

        return 'Tarjeta';
    }

    // Guarda solo lo necesario: nunca el número completo ni el CVV
    public static function crearPara(User $usuario, array $datos): self
    {
        $digitos = preg_replace('/\D/', '', $datos['numero']);

        return $usuario->tarjetas()->create([
            'tipo' => $datos['tipo'],
            'marca' => self::marcaDe($digitos),
            'ultimos4' => substr($digitos, -4),
            'titular' => $datos['titular'],
            'mes' => (int) $datos['mes'],
            'ano' => self::normalizarAno((int) $datos['ano']),
        ]);
    }
}