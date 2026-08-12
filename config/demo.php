<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Restauración destructiva de la demostración
    |--------------------------------------------------------------------------
    |
    | La restauración solo se permite si esta bandera está activa y el nombre
    | real de la base coincide exactamente con DEMO_DATABASE.
    |
    */
    'enabled' => (bool) env('DEMO_MODE', false),
    'database' => env('DEMO_DATABASE'),
    'superadmin' => [
        'name' => env('SUPERADMIN_NAME', 'Superadmin Demo'),
        'email' => env('SUPERADMIN_EMAIL', 'superadmin@demo.local'),
        'password' => env('SUPERADMIN_PASSWORD', 'password'),
    ],

    'storage' => [
        'public' => [
            'negocio',
            'web-publica',
        ],
        'private' => [
            'documentos_compra',
            'planificacion-turnos',
        ],
    ],
];
