<?php

namespace App\Http\Middleware;

use App\Models\SystemSetting;
use App\Models\Client;
use App\Services\TwoFactorService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ApiAuthMiddleware
{

    public function handle(Request $request, Closure $next): Response
    {
        $defaultApiKey = config('app.api_key', 'VIDEO_POPUP_SECRET_2025');
        $defaultClientId = config('app.client_id', 'ELECTRON_VIDEO_PLAYER');
        
        $apiKey = $request->header('X-API-Key', $request->input('api_key'));
        $client = $request->header('X-Client-ID', $request->input('client_id'));

        $allowedApiKeys = array_values(array_filter([
            $defaultApiKey,
            optional(SystemSetting::orderByDesc('id')->first())->api_key,
        ]));

        $allowedClientIds = array_values(array_filter([
            $defaultClientId,
            'ELECTRON_VIDEO_PLAYER',
            'ElectronClient',
        ]));
        
        if (!$apiKey) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required',
                'error' => 'Missing API key',
                'hint' => 'Send X-API-Key header (or api_key param).'
            ], 401);
        }
        
        $clientApiKey = null;
        if ($client) {
            $clientApiKey = Client::where('client_id', $client)->value('api_key');
        }

        if (!in_array($apiKey, $allowedApiKeys, true) && (!$clientApiKey || $apiKey !== $clientApiKey)) {
            Log::warning('API auth failed: invalid key', [
                'path' => $request->path(),
                'client_id' => $client,
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Authentication failed',
                'error' => 'Invalid API key'
            ], 403);
        }

        if (config('two_factor.api_enabled')) {
            $secret = config('two_factor.api_secret');
            $code = $request->header('X-2FA-Code', $request->input('two_factor_code'));

            if (!$secret) {
                return response()->json([
                    'success' => false,
                    'message' => 'Two-factor secret not configured',
                    'error' => 'Missing API 2FA secret'
                ], 500);
            }

            if (!$code || !app(TwoFactorService::class)->verifyCode($secret, $code)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Two-factor verification failed',
                    'error' => 'Invalid 2FA code',
                ], 403);
            }
        }

        // Client-ID opcional para compatibilidade com clients antigos.
        if ($client && !in_array($client, $allowedClientIds, true) && !Client::where('client_id', $client)->exists()) {
            Log::warning('API auth warning: unknown client id', [
                'client_id' => $client,
                'path' => $request->path(),
                'ip' => $request->ip(),
            ]);
        }
        
        return $next($request);
    }
}
