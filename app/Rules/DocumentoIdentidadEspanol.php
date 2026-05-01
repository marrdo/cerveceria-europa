<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Valida documentos espanoles habituales: DNI, NIE y CIF.
 *
 * La regla acepta formatos con espacios o guiones, pero siempre valida sobre
 * el documento normalizado en mayusculas.
 */
class DocumentoIdentidadEspanol implements ValidationRule
{
    private const LETRAS_DNI = 'TRWAGMYFPDXBNJZSQVHLCKE';

    /**
     * Ejecuta la validacion del documento.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! self::esValido($value)) {
            $fail('El campo :attribute debe ser un DNI, NIE o CIF espanol valido.');
        }
    }

    /**
     * Normaliza el documento para guardar una version consistente.
     */
    public static function normalizar(?string $documento): ?string
    {
        if ($documento === null) {
            return null;
        }

        $normalizado = strtoupper(trim($documento));
        $normalizado = str_replace([' ', '-', '.'], '', $normalizado);

        return $normalizado === '' ? null : $normalizado;
    }

    /**
     * Comprueba si el documento normalizado corresponde a DNI, NIE o CIF.
     */
    public static function esValido(?string $documento): bool
    {
        $documento = self::normalizar($documento);

        if ($documento === null) {
            return false;
        }

        return self::esDniValido($documento)
            || self::esNieValido($documento)
            || self::esCifValido($documento);
    }

    /**
     * Valida DNI con ocho digitos y letra de control.
     */
    private static function esDniValido(string $documento): bool
    {
        if (! preg_match('/^[0-9]{8}[A-Z]$/', $documento)) {
            return false;
        }

        $numero = (int) substr($documento, 0, 8);
        $letraEsperada = self::LETRAS_DNI[$numero % 23];

        return $documento[8] === $letraEsperada;
    }

    /**
     * Valida NIE transformando X/Y/Z en 0/1/2 y aplicando control de DNI.
     */
    private static function esNieValido(string $documento): bool
    {
        if (! preg_match('/^[XYZ][0-9]{7}[A-Z]$/', $documento)) {
            return false;
        }

        $prefijo = ['X' => '0', 'Y' => '1', 'Z' => '2'][$documento[0]];

        return self::esDniValido($prefijo.substr($documento, 1));
    }

    /**
     * Valida CIF con letra inicial, siete digitos y control final.
     */
    private static function esCifValido(string $documento): bool
    {
        if (! preg_match('/^[ABCDEFGHJKLMNPQRSUVW][0-9]{7}[0-9A-J]$/', $documento)) {
            return false;
        }

        $tipo = $documento[0];
        $numero = substr($documento, 1, 7);
        $control = $documento[8];

        $sumaPares = 0;
        $sumaImpares = 0;

        for ($indice = 0; $indice < 7; $indice++) {
            $digito = (int) $numero[$indice];

            if ($indice % 2 === 0) {
                $doble = $digito * 2;
                $sumaImpares += intdiv($doble, 10) + ($doble % 10);
                continue;
            }

            $sumaPares += $digito;
        }

        $digitoControl = (10 - (($sumaPares + $sumaImpares) % 10)) % 10;
        $letraControl = 'JABCDEFGHI'[$digitoControl];

        return match (true) {
            str_contains('PQRSNW', $tipo) => $control === $letraControl,
            str_contains('ABEH', $tipo) => $control === (string) $digitoControl,
            default => $control === (string) $digitoControl || $control === $letraControl,
        };
    }
}
