<?php

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost:5174',
        'http://localhost:5173',
        'http://127.0.0.1:5174',
        'http://127.0.0.1:5173',
        'http://*.devlaro.com',
        'https://*.devlaro.com',
        'http://192.168.7.82:3000',
        'http://localhost:3000',
        'http://192.168.7.102:3000',
        'https://bader022-front-end.vercel.app',
        'http://192.168.7.66:3000',
        'https://bokli.io',
        // 'https://bokli.io',
        'http://192.168.7.66:3000',

    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => ['Remember-Token'],

    'max_age' => 0,

    'supports_credentials' => true,

];
