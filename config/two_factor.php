<?php

return [
    'enabled' => env('TWO_FACTOR_ENABLED', false),
    'required' => env('TWO_FACTOR_REQUIRED', true),
    'api_enabled' => env('TWO_FACTOR_API_ENABLED', false),
    'api_secret' => env('TWO_FACTOR_API_SECRET', ''),
];
