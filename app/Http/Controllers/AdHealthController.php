<?php

namespace App\Http\Controllers;

use App\Services\AdGroupJsonService;
use App\Services\ActiveDirectoryService;
use Illuminate\Http\JsonResponse;

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
        /** @var AdGroupJsonService $jsonService */
        $jsonService = app(AdGroupJsonService::class);
        $path = $jsonService->getAdImportPath();
        $statusPath = storage_path('app/AD/import-status.json');
        $lastImport = null;
        $importStatus = [];
        if (is_file($statusPath)) {
            $importStatus = json_decode(file_get_contents($statusPath), true) ?: [];
            $lastImport = $importStatus['last_import_at'] ?? null;
        }

        $path = $path ?: ($importStatus['source_path'] ?? $jsonService->getExpectedAdImportPath());
        $exists = $path && is_file($path);
        $entries = 0;
        $lastModified = null;

        if ($exists) {
            $entries = count($jsonService->getTargetRecordsFromPath($path));
            $lastModified = date('Y-m-d H:i:s', filemtime($path));
        }

        return response()->json([
            'success' => $exists,
            'path' => $path,
            'filename' => $path ? basename($path) : null,
            'exists' => $exists,
            'entries' => $entries,
            'last_modified' => $lastModified,
            'last_import_at' => $lastImport,
            'clients_created' => $importStatus['clients_created'] ?? 0,
            'clients_updated' => $importStatus['clients_updated'] ?? 0,
            'groups_processed' => $importStatus['groups_processed'] ?? 0,
            'targets_processed' => $importStatus['targets_processed'] ?? 0,
        ]);
    }
}
