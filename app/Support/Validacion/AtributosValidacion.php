<?php

namespace App\Support\Validacion;

/**
 * Atributos HTML reutilizables para validacion basica en frontend.
 *
 * Esto no sustituye al backend: solo ayuda al usuario antes de enviar el
 * formulario. La validacion de verdad siempre queda en Laravel.
 */
class AtributosValidacion
{
    public const PATRON_DOCUMENTO_IDENTIDAD_ESPANOL = '[A-Za-z0-9 .-]{8,12}';

    public const TITULO_DOCUMENTO_IDENTIDAD_ESPANOL = 'Introduce un DNI, NIE o CIF espanol valido.';

    public const PATRON_TELEFONO_ESPANOL = '(\\+34|0034)?[\\s.-]?[6789][0-9\\s.-]{8,14}';

    public const TITULO_TELEFONO_ESPANOL = 'Introduce un telefono espanol valido, por ejemplo 600 123 456 o +34 954 123 456.';

    public const TITULO_EMAIL = 'Introduce un email valido, por ejemplo proveedor@dominio.es.';
}
