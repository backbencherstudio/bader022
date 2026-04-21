<?php

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost:5174',
        'http://localhost:5173',
        'http://127.0.0.1:5174',
        'http://127.0.0.1:5173',
        'http://localhost:3000',
        'https://bader022-front-end.vercel.app',
        'https://bokli.io',
<<<<<<< HEAD
        // 'https://bokli.io',



=======
        'https://www.bokli.io',
        'https://bader022.apphero.agency',
>>>>>>> dev
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*', 'Remember-Token'],

    'exposed_headers' => ['Remember-Token'],

    'max_age' => 0,

    'supports_credentials' => true,

];
