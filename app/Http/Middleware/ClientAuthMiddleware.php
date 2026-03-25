<?php

namespace App\Http\Middleware;

use App\Models\Client;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ClientAuthMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!config('client-auth.enabled')) {
            return $next($request);
        }

        $clientId = $request->header('X-Client-ID', $request->input('client_id'));
        if (!$clientId) {
            return $next($request);
        }

        $clientId = mb_strtolower(trim($clientId));
        $tokenHeader = config('client-auth.token_header', 'X-Client-Token');
        $incomingToken = $request->header($tokenHeader, $request->input('client_token'));

        $client = Client::firstOrCreate(
            ['client_id' => $clientId],
            [
                'first_seen_at' => now(),
                'last_seen_at' => now(),
                'ip_address' => $request->ip(),
            ]
        );

        $client->last_seen_at = now();
        $client->ip_address = $request->ip();
        $client->save();

        $needsNewToken = empty($client->client_token);
        if ($incomingToken && $client->client_token && !hash_equals($client->client_token, $incomingToken)) {
            Log::warning('Client token mismatch', [
                'client_id' => $clientId,
                'path' => $request->path(),
                'ip' => $request->ip(),
            ]);
            $needsNewToken = true;
        }

        if ($needsNewToken) {
            $client->client_token = Str::random(40);
            $client->save();
        }

        $response = $next($request);
        if ($client->client_token) {
            $response->headers->set($tokenHeader, $client->client_token);
        }

        return $response;
    }
}
