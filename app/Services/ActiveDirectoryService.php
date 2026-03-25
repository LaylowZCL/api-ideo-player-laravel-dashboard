<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use App\Services\AdGroupJsonService;

class ActiveDirectoryService
{
    public function authenticate(string $login, string $password): ?array
    {
        if (!config('ad.enabled')) {
            return null;
        }

        $mockOnly = config('ad.mock_only', false);
        $hasMockUsers = !empty(app(AdGroupJsonService::class)->getUsersFromMock());

        if ($mockOnly || (app()->environment('local') && $hasMockUsers)) {
            $mockUser = $this->authenticateMock($login, $password);
            if ($mockUser) {
                return $mockUser;
            }

            if ($mockOnly) {
                Log::warning('AD mock_only enabled: skipping LDAP bind.');
                return null;
            }
        }

        if (!function_exists('ldap_connect')) {
            Log::warning('LDAP extension not available');
            return null;
        }

        $connection = $this->connect();
        if (!$connection) {
            return null;
        }

        if (!$this->bindService($connection)) {
            return null;
        }

        $user = $this->findUser($connection, $login);
        if (!$user || empty($user['dn'])) {
            return null;
        }

        if (!@ldap_bind($connection, $user['dn'], $password)) {
            return null;
        }

        return $user;
    }

    public function lookupUser(string $login): ?array
    {
        if (!config('ad.enabled')) {
            return null;
        }

        if (!function_exists('ldap_connect')) {
            Log::warning('LDAP extension not available');
            return null;
        }

        $connection = $this->connect();
        if (!$connection) {
            return null;
        }

        if (!$this->bindService($connection)) {
            return null;
        }

        return $this->findUser($connection, $login);
    }

    public function checkConnection(): array
    {
        $result = [
            'connected' => false,
            'bound' => false,
            'host' => config('ad.host'),
            'port' => config('ad.port'),
            'base_dn' => config('ad.base_dn'),
            'use_ssl' => config('ad.use_ssl'),
            'use_tls' => config('ad.use_tls'),
            'require_ssl' => config('ad.require_ssl'),
            'error' => null,
        ];

        if (!function_exists('ldap_connect')) {
            $result['error'] = 'LDAP extension not available';
            return $result;
        }

        $connection = $this->connect();
        if (!$connection) {
            $result['error'] = 'Failed to connect to AD';
            return $result;
        }

        $result['connected'] = true;

        if (!$this->bindService($connection)) {
            $result['error'] = 'Failed to bind with service account';
            return $result;
        }

        $result['bound'] = true;
        return $result;
    }

    public function getGroupsForComputer(string $clientId): array
    {
        if (!config('ad.enabled')) {
            return [];
        }

        if (!function_exists('ldap_connect')) {
            return [];
        }

        $connection = $this->connect();
        if (!$connection) {
            return [];
        }

        if (!$this->bindService($connection)) {
            return [];
        }

        $computerDn = $this->findComputerDn($connection, $clientId);
        if (!$computerDn) {
            return [];
        }

        $groupAttribute = config('ad.group_attribute', 'memberOf');
        $result = @ldap_read($connection, $computerDn, '(objectClass=computer)', [$groupAttribute]);
        if (!$result) {
            return [];
        }

        $entries = ldap_get_entries($connection, $result);
        if ($entries['count'] < 1) {
            return [];
        }

        $groups = [];
        $memberOf = $entries[0][strtolower($groupAttribute)] ?? [];
        for ($i = 0; $i < ($memberOf['count'] ?? 0); $i++) {
            $dn = $memberOf[$i];
            $groups[] = [
                'dn' => $dn,
                'name' => $this->extractGroupName($dn),
            ];
        }

        return $groups;
    }

    private function connect()
    {
        $host = config('ad.host');
        $port = config('ad.port');

        if (!$host) {
            Log::warning('AD host not configured');
            return null;
        }

        if (config('ad.require_ssl') && !config('ad.use_ssl')) {
            Log::warning('AD SSL is required but disabled.');
            return null;
        }

        if (config('ad.use_ssl') && !str_starts_with($host, 'ldaps://')) {
            $host = 'ldaps://' . $host;
        }

        $connection = @ldap_connect($host, $port);
        if (!$connection) {
            Log::warning('Failed to connect to AD');
            return null;
        }

        ldap_set_option($connection, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($connection, LDAP_OPT_REFERRALS, 0);

        if (config('ad.use_tls')) {
            if (!@ldap_start_tls($connection)) {
                Log::warning('Failed to start TLS for AD');
                return null;
            }
        }

        return $connection;
    }

    private function bindService($connection): bool
    {
        $bindDn = config('ad.bind_dn');
        $bindPassword = config('ad.bind_password');

        if (!$bindDn) {
            return true;
        }

        if (!@ldap_bind($connection, $bindDn, $bindPassword)) {
            Log::warning('AD bind failed');
            return false;
        }

        return true;
    }

    private function findUser($connection, string $login): ?array
    {
        $baseDn = config('ad.base_dn');
        if (!$baseDn) {
            Log::warning('AD base DN not configured');
            return null;
        }

        $userAttr = config('ad.user_attribute', 'sAMAccountName');
        $loginEscaped = ldap_escape($login, '', LDAP_ESCAPE_FILTER);

        $filter = sprintf('(|(%s=%s)(userPrincipalName=%s)(mail=%s))', $userAttr, $loginEscaped, $loginEscaped, $loginEscaped);
        $attributes = [$userAttr, 'displayName', 'mail', 'userPrincipalName', config('ad.group_attribute', 'memberOf')];

        $search = @ldap_search($connection, $baseDn, $filter, $attributes);
        if (!$search) {
            return null;
        }

        $entries = ldap_get_entries($connection, $search);
        if ($entries['count'] < 1) {
            return null;
        }

        $entry = $entries[0];
        $dn = $entry['dn'] ?? null;
        if (!$dn) {
            return null;
        }

        $groups = [];
        $memberOf = $entry[strtolower(config('ad.group_attribute', 'memberOf'))] ?? [];
        for ($i = 0; $i < ($memberOf['count'] ?? 0); $i++) {
            $groups[] = $memberOf[$i];
        }

        return [
            'dn' => $dn,
            'name' => $entry['displayname'][0] ?? $login,
            'email' => $entry['mail'][0] ?? null,
            'username' => $entry[strtolower($userAttr)][0] ?? $login,
            'groups' => $groups,
        ];
    }

    private function findComputerDn($connection, string $clientId): ?string
    {
        $baseDn = config('ad.computer_base_dn') ?: config('ad.base_dn');
        if (!$baseDn) {
            return null;
        }

        $computerName = str_ends_with($clientId, '$') ? $clientId : $clientId . '$';
        $computerEscaped = ldap_escape($computerName, '', LDAP_ESCAPE_FILTER);

        $filter = sprintf('(&(objectClass=computer)(sAMAccountName=%s))', $computerEscaped);
        $search = @ldap_search($connection, $baseDn, $filter, ['dn']);
        if (!$search) {
            return null;
        }

        $entries = ldap_get_entries($connection, $search);
        if ($entries['count'] < 1) {
            return null;
        }

        return $entries[0]['dn'] ?? null;
    }

    private function extractGroupName(string $dn): string
    {
        if (preg_match('/CN=([^,]+)/i', $dn, $matches)) {
            return $matches[1];
        }

        return $dn;
    }

    private function authenticateMock(string $login, string $password): ?array
    {
        $mockUsers = app(AdGroupJsonService::class)->getUsersFromMock();
        if (empty($mockUsers)) {
            return null;
        }

        foreach ($mockUsers as $user) {
            $username = $user['username'] ?? $user['login'] ?? $user['email'] ?? null;
            if (!$username) {
                continue;
            }

            if (strcasecmp($username, $login) !== 0) {
                continue;
            }

            $mockPassword = $user['password'] ?? null;
            if ($mockPassword === null || $mockPassword !== $password) {
                return null;
            }

            return [
                'dn' => $user['dn'] ?? $username,
                'name' => $user['name'] ?? $username,
                'email' => $user['email'] ?? null,
                'username' => $username,
                'groups' => $user['groups'] ?? [],
            ];
        }

        return null;
    }
}
