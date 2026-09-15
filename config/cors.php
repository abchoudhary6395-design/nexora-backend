<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],

    // In production, replace '*' with the deployed frontend URL(s), e.g.
    // ['https://app.nexora.com']. '*' is fine for local development only.
    'allowed_origins' => explode(',', env('CORS_ALLOWED_ORIGINS', 'http://localhost:5173')),

    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,

    // Must be true so Sanctum can set/read the auth cookie if the SPA
    // ever switches from Bearer-token auth to cookie-based session auth.
    'supports_credentials' => true,
];
