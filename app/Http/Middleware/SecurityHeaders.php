<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Apply baseline browser hardening headers to every dynamic response.
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        $isSecure = $request->isSecure() || $this->isForwardedHttps($request);

        $response->headers->set('Content-Security-Policy', $this->buildCsp($request));
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');

        if ($isSecure) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        if ($this->shouldDisableCaching($request)) {
            $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate, private');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
        }

        return $response;
    }

    private function buildCsp(Request $request): string
    {
        $viteSources = $this->viteDevSources();

        $scriptSources = array_filter(array_merge([
            "'self'",
            "'unsafe-inline'",
            "'unsafe-eval'",
            'https://cdn.jsdelivr.net',
            'https://code.jquery.com',
            'https://unpkg.com',
        ], $viteSources['script']));

        $styleSources = array_filter(array_merge([
            "'self'",
            "'unsafe-inline'",
            'https://cdn.jsdelivr.net',
            'https://fonts.googleapis.com',
        ], $viteSources['style']));

        $connectSources = array_filter(array_merge([
            "'self'",
            'https:',
        ], $viteSources['connect']));

        $directives = [
            "default-src 'self'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'none'",
            "object-src 'none'",
            'script-src '.implode(' ', $scriptSources),
            'style-src '.implode(' ', $styleSources),
            "img-src 'self' data: blob: https://api.qrserver.com https:",
            "font-src 'self' data: https://fonts.gstatic.com https://cdn.jsdelivr.net",
            'connect-src '.implode(' ', $connectSources),
            "media-src 'self' blob:",
        ];

        if ($request->isSecure() || $this->isForwardedHttps($request)) {
            $directives[] = 'upgrade-insecure-requests';
        }

        return implode('; ', $directives);
    }

    private function shouldDisableCaching(Request $request): bool
    {
        if ($request->isMethodCacheable() === false) {
            return true;
        }

        if ($request->user()) {
            return true;
        }

        return $request->routeIs(
            'login',
            'logout',
            'password.*',
            'two-factor.*',
            'force-password.*'
        );
    }

    private function isForwardedHttps(Request $request): bool
    {
        return strtolower((string) $request->headers->get('X-Forwarded-Proto')) === 'https';
    }

    /**
     * Allow Vite dev server/HMR sources while local development is active.
     *
     * @return array{script: array<int, string>, style: array<int, string>, connect: array<int, string>}
     */
    private function viteDevSources(): array
    {
        $hotFile = public_path('hot');

        if (!is_file($hotFile)) {
            return [
                'script' => [],
                'style' => [],
                'connect' => [],
            ];
        }

        $url = trim((string) file_get_contents($hotFile));

        if ($url === '') {
            return [
                'script' => [],
                'style' => [],
                'connect' => [],
            ];
        }

        $variants = $this->expandDevServerOrigins($url);
        $wsUrls = array_map(function (string $origin) {
            return Str::startsWith($origin, 'https://')
                ? preg_replace('/^https:\/\//', 'wss://', $origin)
                : preg_replace('/^http:\/\//', 'ws://', $origin);
        }, $variants);

        return [
            'script' => $variants,
            'style' => $variants,
            'connect' => array_values(array_unique(array_filter(array_merge($variants, $wsUrls)))),
        ];
    }

    /**
     * When Vite runs in dev we may see localhost, 127.0.0.1 or [::1].
     *
     * @return array<int, string>
     */
    private function expandDevServerOrigins(string $url): array
    {
        $parts = parse_url($url);

        if (!is_array($parts) || empty($parts['scheme']) || empty($parts['host'])) {
            return [$url];
        }

        $scheme = $parts['scheme'];
        $host = $parts['host'];
        $port = $parts['port'] ?? null;

        $candidates = [$host];

        if (in_array($host, ['[::1]', '::1', 'localhost', '127.0.0.1'], true)) {
            $candidates = ['127.0.0.1', 'localhost', '[::1]'];
        }

        return array_values(array_unique(array_map(function (string $candidate) use ($scheme, $port) {
            return sprintf('%s://%s%s', $scheme, $candidate, $port ? ':'.$port : '');
        }, $candidates)));
    }
}
