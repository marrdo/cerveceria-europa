<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Exportaciones de cuadrantes
    |--------------------------------------------------------------------------
    |
    | Los Excel publicados se guardan en un disco privado. En producción puede
    | cambiarse a S3 sin modificar el dominio ni exponer los archivos por URL.
    |
    */

    'exportaciones' => [
        'disk' => env('PLANIFICACION_TURNOS_EXPORT_DISK', 'local'),
        'directorio' => 'planificacion-turnos/cuadrantes',
    ],

];
