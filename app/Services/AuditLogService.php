<?php

namespace App\Services;

use App\Models\Log as AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLogService
{
    public function log(string $event, string $status = 'success', array $details = [], string $level = 'info'): void
    {
        $user = Auth::user();

        $payload = array_merge([
            'actor_id' => $user?->id,
            'actor_email' => $user?->email,
            'ip' => Request::ip(),
            'path' => Request::path(),
            'method' => Request::method(),
        ], $details);

        try {
            AuditLog::create([
                'time' => now()->format('Y-m-d H:i:s'),
                'event' => $event,
                'status' => $status,
                'video' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'level' => $level,
            ]);
        } catch (\Throwable $e) {
            // Evita quebrar fluxos por falha de log
        }
    }
}
