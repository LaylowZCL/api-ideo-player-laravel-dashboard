<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorService
{
    public function generateSecret(): string
    {
        $google2fa = new Google2FA();
        return $google2fa->generateSecretKey();
    }

    public function encryptSecret(string $secret): string
    {
        return Crypt::encryptString($secret);
    }

    public function decryptSecret(?string $secret): ?string
    {
        if (!$secret) {
            return null;
        }

        try {
            return Crypt::decryptString($secret);
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function verifyCode(string $secret, string $code): bool
    {
        $google2fa = new Google2FA();
        $normalized = preg_replace('/\s+/', '', $code);
        if ($normalized === null) {
            $normalized = $code;
        }
        $normalized = preg_replace('/[^0-9]/', '', $normalized);
        if ($normalized === null) {
            $normalized = $code;
        }

        // Allow small clock drift (2 steps ~ 60s)
        return $google2fa->verifyKey($secret, $normalized, 2);
    }

    public function recoveryCodes(int $count = 8): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = bin2hex(random_bytes(5));
        }
        return $codes;
    }
}
