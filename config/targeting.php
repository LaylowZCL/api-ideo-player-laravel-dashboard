<?php

return [
    'source' => env('AD_TARGET_SOURCE', 'json'), // json | ldap | hybrid
    'cache_enabled' => env('TARGET_CACHE_ENABLED', true),
    'cache_ttl' => (int) env('TARGET_CACHE_TTL', 600),
    'debug' => env('TARGET_DEBUG', false),
];
