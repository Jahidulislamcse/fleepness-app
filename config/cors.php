<?php

return [
    'paths' => ['api/*'], // Enable CORS for API routes
    'allowed_methods' => ['*'], // Allow all HTTP methods (GET, POST, PUT, DELETE, etc.)
    'allowed_origins' => ['*'], // Allow all origins (Change to your frontend URL for security)
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,
];
