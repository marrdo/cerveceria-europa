<?php

namespace App\Support\Validacion;

use App\Rules\DocumentoIdentidadEspanol;
use App\Rules\TelefonoEspanol;

/**
 * Punto unico para reglas reutilizables de validacion de formularios.
 *
 * Los controladores y Form Requests deben tirar de esta clase cuando necesiten
 * validar campos comunes como email, telefono o documentos espanoles.
 */
class ReglasValidacion
{
    /**
     * Reglas para DNI, NIE o CIF espanol.
     *
     * @return array<int, mixed>
     */
    public static function documentoIdentidadEspanol(bool $nullable = true): array
    {
        return [
            $nullable ? 'nullable' : 'required',
            'string',
            'max:50',
            new DocumentoIdentidadEspanol(),
        ];
    }

    /**
     * Reglas para email de contacto.
     *
     * @return array<int, string>
     */
    public static function email(bool $nullable = true): array
    {
        return [
            $nullable ? 'nullable' : 'required',
            'string',
            'email:rfc',
            'max:191',
        ];
    }

    /**
     * Reglas para telefono espanol.
     *
     * @return array<int, mixed>
     */
    public static function telefonoEspanol(bool $nullable = true): array
    {
        return [
            $nullable ? 'nullable' : 'required',
            'string',
            'max:30',
            new TelefonoEspanol(),
        ];
    }
}
