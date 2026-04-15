<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class AdGroupJsonService
{
    public function getAdImportCandidates(): array
    {
        return array_values(array_unique(array_filter([
            config('ad.group_json_path'),
            config('ad.mock_users_path'),
            storage_path('app/ad/mock-users.json'),
            storage_path('app/AD/mock-users.json'),
            storage_path('app/ad-groups.json'),
            storage_path('app/ad/ad-groups.json'),
            storage_path('app/AD/ad-groups.json'),
        ])));
    }

    public function getTargetRecords(): array
    {
        $path = $this->getAdImportPath();
        if (!$path) {
            return [];
        }

        return $this->getTargetRecordsFromPath($path);
    }

    public function getTargetRecordsFromPath(string $path): array
    {
        $data = $this->readJsonFile($path);
        if (!is_array($data)) {
            return [];
        }

        $records = [];

        foreach ($data as $record) {
            if (!is_array($record)) {
                continue;
            }

            $normalized = $this->normalizeTargetRecord($record);
            if ($normalized === null) {
                continue;
            }

            $records[] = $normalized;
        }

        return $records;
    }

    public function getGroupsForMachine(string $machine, ?string $username = null): array
    {
        $records = $this->getTargetRecords();
        if (empty($records)) {
            return [];
        }

        $machine = mb_strtolower(trim($machine));
        $groups = [];

        $username = $username ? mb_strtolower(trim($username)) : null;

        foreach ($records as $record) {
            $recordMachine = $record['machine'] ?? null;
            if (!$recordMachine) {
                continue;
            }

            if (mb_strtolower(trim($recordMachine)) !== $machine) {
                continue;
            }

            if ($username) {
                $recordUser = $record['user'] ?? null;
                if ($recordUser && mb_strtolower(trim($recordUser)) !== $username) {
                    continue;
                }
            }

            $group = $record['group'] ?? null;
            if ($group) {
                $groups[] = trim($group);
            }
        }

        return array_values(array_unique(array_filter($groups)));
    }

    public function getUsersFromMock(): array
    {
        $path = $this->resolvePath(config('ad.mock_users_path'));
        if (!$path) {
            return [];
        }

        $data = $this->readJsonFile($path);
        if (!is_array($data)) {
            return [];
        }

        return array_values(array_filter($data, function ($user) {
            if (!is_array($user)) {
                return false;
            }

            $identifier = $user['username'] ?? $user['login'] ?? $user['email'] ?? null;
            $password = $user['password'] ?? null;

            return !empty($identifier) && $password !== null;
        }));
    }

    public function getAdImportPath(): ?string
    {
        foreach ($this->getAdImportCandidates() as $candidate) {
            $resolved = $this->resolvePath($candidate);
            if ($resolved) {
                return $resolved;
            }
        }

        return null;
    }

    public function getExpectedAdImportPath(): ?string
    {
        foreach ($this->getAdImportCandidates() as $candidate) {
            if ($candidate) {
                return $candidate;
            }
        }

        return null;
    }

    public function normalizeTargetRecord(array $record): ?array
    {
        $machine = $this->value($record, ['Maquina', 'maquina', 'machine', 'Machine']);
        $user = $this->value($record, ['Usuario', 'usuario', 'user', 'User', 'username', 'Username']);
        $name = $this->value($record, ['Nome', 'nome', 'name', 'Name', 'display_name']);
        $email = $this->value($record, ['Email', 'email', 'mail', 'Mail']);
        $group = $this->value($record, ['Grupo', 'grupo', 'group', 'Group']);
        $effectiveAt = $this->value($record, ['Data', 'data', 'Date', 'date']);

        $machine = $machine ? trim($machine) : null;
        $user = $user ? trim($user) : null;
        $name = $name ? trim($name) : null;
        $email = $email ? mb_strtolower(trim($email)) : null;
        $group = $group ? trim($group) : null;
        $effectiveAt = $effectiveAt ? trim($effectiveAt) : null;

        if (!$machine || !$group) {
            return null;
        }

        return [
            'machine' => mb_strtolower($machine),
            'machine_original' => $machine,
            'user' => $user ? mb_strtolower($user) : null,
            'user_original' => $user,
            'name' => $name,
            'email' => $email,
            'group' => $group,
            'effective_at' => $effectiveAt,
            'source' => 'json',
        ];
    }

    private function readJsonFile(string $path)
    {
        try {
            $contents = file_get_contents($path);
            if ($contents === false) {
                return [];
            }

            $contents = $this->normalizeJsonContents($contents);
            $decoded = json_decode($contents, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::warning('Invalid AD JSON file', [
                    'path' => $path,
                    'error' => json_last_error_msg(),
                ]);
                return [];
            }

            return $decoded;
        } catch (\Throwable $e) {
            Log::warning('Failed to read AD JSON file', [
                'path' => $path,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    private function normalizeJsonContents(string $contents): string
    {
        $contents = preg_replace('/^\xEF\xBB\xBF/', '', $contents) ?? $contents;

        if (function_exists('mb_check_encoding') && !mb_check_encoding($contents, 'UTF-8')) {
            $converted = @mb_convert_encoding($contents, 'UTF-8', 'UTF-8, ISO-8859-1, Windows-1252');
            if (is_string($converted) && $converted !== '') {
                $contents = $converted;
            }
        }

        return $contents;
    }

    private function resolvePath(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        if (is_file($path)) {
            return $path;
        }

        if (!str_starts_with($path, '/')) {
            $basePath = base_path($path);
            if (is_file($basePath)) {
                return $basePath;
            }

            $storagePath = storage_path(ltrim($path, '/'));
            if (is_file($storagePath)) {
                return $storagePath;
            }
        }

        return null;
    }

    private function value(array $record, array $keys): ?string
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $record) && $record[$key] !== null) {
                return (string) $record[$key];
            }
        }

        return null;
    }
}
