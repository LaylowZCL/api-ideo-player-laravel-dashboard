<?php

$bmAuthRaw = env('BM_DASHBOARD_AUTH', null);
$bmAuth = null;
if ($bmAuthRaw !== null) {
    $bmAuth = filter_var($bmAuthRaw, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
}

$adEnabled = env('AD_ENABLED', false);

$dashboardUsesAd = $bmAuth === null
    ? $adEnabled
    : !$bmAuth;

$allowLocalFallback = $dashboardUsesAd
    ? env('AD_ALLOW_LOCAL_FALLBACK', true)
    : true;

return [
    'enabled' => $adEnabled,
    'dashboard_uses_ad' => $dashboardUsesAd,
    'allow_local_fallback' => $allowLocalFallback,
    'host' => env('AD_HOST', ''),
    'port' => env('AD_PORT', 389),
    'use_ssl' => env('AD_USE_SSL', false),
    'use_tls' => env('AD_USE_TLS', false),
    'require_ssl' => env('AD_REQUIRE_SSL', true),
    'base_dn' => env('AD_BASE_DN', ''),
    'computer_base_dn' => env('AD_COMPUTER_BASE_DN', ''),
    'bind_dn' => env('AD_BIND_DN', ''),
    'bind_password' => env('AD_BIND_PASSWORD', ''),
    'user_attribute' => env('AD_USER_ATTRIBUTE', 'sAMAccountName'),
    'group_attribute' => env('AD_GROUP_ATTRIBUTE', 'memberOf'),
    'sync_client_groups' => env('AD_SYNC_CLIENT_GROUPS', false),
    'group_source' => env('AD_GROUP_SOURCE', 'ldap'),
    'group_json_path' => env('AD_GROUP_JSON_PATH', env('AD_MOCK_USERS_PATH', storage_path('app/ad/mock-users.json'))),
    'mock_users_path' => env('AD_MOCK_USERS_PATH', storage_path('app/ad/mock-users.json')),
    'mock_only' => env('AD_MOCK_ONLY', false),
    'sso_enabled' => env('AD_SSO_ENABLED', true),
    'sso_header' => env('AD_SSO_HEADER', 'REMOTE_USER'),
    'group_map' => [
        'super_admin' => env('AD_GROUP_SUPER_ADMIN'),
        'admin' => env('AD_GROUP_ADMIN'),
        'manager' => env('AD_GROUP_MANAGER'),
    ],
];
