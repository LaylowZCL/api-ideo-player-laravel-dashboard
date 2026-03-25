<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class AdGroupJsonService
{
    public function getGroupsForMachine(string $machine, ?string $username = null): array
    {
        $records = $this->readRecords();
        if (empty($records)) {
            return [];
        }

        $machine = mb_strtolower(trim($machine));
        $groups = [];

        $username = $username ? mb_strtolower(trim($username)) : null;

        foreach ($records as $record) {
            $recordMachine = $this->value($record, ['Maquina', 'maquina', 'machine', 'Machine']);
            if (!$recordMachine) {
                continue;
            }

            if (mb_strtolower(trim($recordMachine)) !== $machine) {
                continue;
            }

            if ($username) {
                $recordUser = $this->value($record, ['Usuario', 'usuario', 'user', 'User', 'username', 'Username']);
                if ($recordUser && mb_strtolower(trim($recordUser)) !== $username) {
                    continue;
                }
            }

            $group = $this->value($record, ['Grupo', 'grupo', 'group', 'Group']);
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

        return $data;
    }

    private function readRecords(): array
    {
        $path = $this->resolvePath(config('ad.group_json_path'));
        if (!$path) {
            return [];
        }

        $data = $this->readJsonFile($path);
        if (!is_array($data)) {
            return [];
        }

        return $data;
    }

    private function readJsonFile(string $path)
    {
        try {
            $contents = file_get_contents($path);
            if ($contents === false) {
                return [];
            }

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
