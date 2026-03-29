<?php

namespace App\Services;

class ApplicationUrlService
{
    public function loginUrl(): string
    {
        $baseUrl = trim((string) config('app.url'));
        if ($baseUrl !== '') {
            return rtrim($baseUrl, '/') . route('login', [], false);
        }

        return route('login');
    }

    public function assetUrl(string $path): string
    {
        $path = '/' . ltrim($path, '/');
        $baseUrl = trim((string) config('app.url'));

        if ($baseUrl !== '') {
            return rtrim($baseUrl, '/') . $path;
        }

        return url($path);
    }
}
