<?php

namespace App\Http\Controllers;

use App\Services\ActiveDirectoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class AdHealthController extends Controller
{
    public function health(): JsonResponse
    {
        $result = app(ActiveDirectoryService::class)->checkConnection();

        return response()->json([
            'success' => $result['connected'] ?? false,
            'connected' => $result['connected'] ?? false,
            'bound' => $result['bound'] ?? false,
            'host' => $result['host'] ?? null,
            'port' => $result['port'] ?? null,
            'base_dn' => $result['base_dn'] ?? null,
            'use_ssl' => $result['use_ssl'] ?? null,
            'use_tls' => $result['use_tls'] ?? null,
            'require_ssl' => $result['require_ssl'] ?? null,
            'error' => $result['error'] ?? null,
            'checked_at' => now()->toIso8601String(),
        ]);
    }

    public function jsonStatus(): JsonResponse
    {
        $path = config('ad.group_json_path');
        $exists = $path && is_file($path);
        $entries = 0;
        $lastModified = null;

        if ($exists) {
            $contents = file_get_contents($path);
            $decoded = json_decode($contents, true);
            if (is_array($decoded)) {
                $entries = count($decoded);
            }
            $lastModified = date('Y-m-d H:i:s', filemtime($path));
        }

        $statusPath = storage_path('app/AD/import-status.json');
        $lastImport = null;
        if (is_file($statusPath)) {
            $status = json_decode(file_get_contents($statusPath), true);
            $lastImport = $status['last_import_at'] ?? null;
        }

        return response()->json([
            'success' => $exists,
            'path' => $path,
            'exists' => $exists,
            'entries' => $entries,
            'last_modified' => $lastModified,
            'last_import_at' => $lastImport,
        ]);
    }
}
