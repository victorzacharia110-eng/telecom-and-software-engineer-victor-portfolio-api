<?php

return [

    'paths' => [
        'api/*',
        'sanctum/csrf-cookie',
        'auth/*',
        'login',
        'register',
        'logout',
        'user',
    ],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost:3000',      //  Your Vite dev server
        'http://localhost:5173',      // Vue Vite dev server (alternative)
        'http://127.0.0.1:3000',      //  Localhost fallback
        'http://127.0.0.1:5173',      // Optional fallback
        'https://tscl.online',        // Production frontend
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,   // Must be true for Sanctum
];