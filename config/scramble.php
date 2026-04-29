<?php

use Dedoc\Scramble\Http\Middleware\RestrictedDocsAccess;

return [
    'api_path' => 'api',
    'api_domain' => null,

    'info' => [
        'title' => 'MicroPago API',
        'version' => '1.0.0',
        'description' => 'API REST del sistema de micropagos NFC para transporte publico de Santa Cruz de la Sierra, Bolivia. Desarrollado por el DREAM TEAM PHP.',
    ],
    'servers' => null,
    'docs_path' => 'api/docs',
    'docs_json_path' => 'api/docs.json',

    'middleware' => [
        'web',
        // En produccion puedes agregar RestrictedDocsAccess::class
    ],

    'extensions' => [],
];
