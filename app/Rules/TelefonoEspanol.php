<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Valida telefonos espanoles fijos o moviles.
 *
 * Acepta formatos humanos como "+34 600 123 456", "0034 954 123 456"
 * o "600-123-456" y valida sobre el numero normalizado.
 */
class TelefonoEspanol implements ValidationRule
{
    /**
     * Ejecuta la validacion del telefono.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! self::esValido($value)) {
            $fail('El campo :attribute debe ser un numero espanol valido.');
        }
    }

    /**
     * Normaliza el telefono para guardar una version consistente.
     */
    public static function normalizar(?string $telefono): ?string
    {
        if ($telefono === null) {
            return null;
        }

        $normalizado = trim($telefono);
        $normalizado = str_replace([' ', '-', '.', '(', ')'], '', $normalizado);

        if (str_starts_with($normalizado, '0034')) {
            $normalizado = '+34'.substr($normalizado, 4);
        }

        return $normalizado === '' ? null : $normalizado;
    }

    /**
     * Comprueba si el telefono corresponde a numeracion espanola habitual.
     */
    public static function esValido(?string $telefono): bool
    {
        $telefono = self::normalizar($telefono);

        if ($telefono === null) {
            return false;
        }

        if (str_starts_with($telefono, '+34')) {
            $telefono = substr($telefono, 3);
        }

        return preg_match('/^[6789][0-9]{8}$/', $telefono) === 1;
    }
}
