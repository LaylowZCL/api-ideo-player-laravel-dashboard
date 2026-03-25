<?php

return [
    'enabled' => env('CLIENT_TOKEN_ENABLED', true),
    'token_header' => env('CLIENT_TOKEN_HEADER', 'X-Client-Token'),
];
