<?php

return [
    'paths' => ['api/*'],
    'allowed_origins' => ['*'],
    'supports_credentials' => true,
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
    'exposed_headers' => ['Authorization'],
    'max_age' => 3600,
];
