<?php

namespace App\Services;

use App\Mail\UserWelcomeMail;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class UserProvisioningService
{
    public function __construct(
        private readonly ApplicationUrlService $applicationUrlService,
    ) {
    }

    public function createUser(array $attributes, string $sourceLabel = 'criação manual'): array
    {
        $plainPassword = $attributes['password'] ?? $this->generateTemporaryPassword();

        $user = DB::transaction(function () use ($attributes, $plainPassword) {
            $user = User::create([
                'name' => $attributes['name'],
                'email' => mb_strtolower(trim($attributes['email'])),
                'username' => $this->normalizeUsername($attributes['username'] ?? null, $attributes['email']),
                'password' => Hash::make($plainPassword),
                'must_change_password' => true,
                'password_changed_at' => null,
                'role' => $attributes['role'],
                'permissions' => $attributes['permissions'] ?? User::defaultPermissionsForRole($attributes['role']),
                'user_type' => $attributes['role'],
                'email_verified_at' => $attributes['email_verified_at'] ?? now(),
            ]);

            $user->syncLegacyRoleFields();
            $user->save();

            return $user;
        });

        $mailSent = $this->sendWelcomeEmail($user, $plainPassword, $sourceLabel);

        return [
            'user' => $user->fresh(),
            'plain_password' => $plainPassword,
            'mail_sent' => $mailSent,
        ];
    }

    public function sendWelcomeEmail(User $user, string $plainPassword, string $sourceLabel): bool
    {
        try {
            Mail::to($user->email)->send(new UserWelcomeMail(
                user: $user,
                plainPassword: $plainPassword,
                loginUrl: $this->applicationUrlService->loginUrl(),
                logoUrl: $this->applicationUrlService->assetUrl('assets/images/logo-bm.png'),
                sourceLabel: $sourceLabel
            ));

            return true;
        } catch (\Throwable $exception) {
            logger()->warning('Falha ao enviar email de boas-vindas.', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    public function generateTemporaryPassword(): string
    {
        return Str::password(14, true, true, true, false);
    }

    public function normalizeUsername(?string $username, string $email): string
    {
        $base = trim((string) ($username ?: strstr($email, '@', true) ?: $email));
        $normalized = mb_strtolower(preg_replace('/[^a-zA-Z0-9._-]/', '', $base) ?: '');

        if ($normalized === '') {
            $normalized = 'utilizador';
        }

        $candidate = $normalized;
        $suffix = 1;

        while (User::where('username', $candidate)->exists()) {
            $candidate = $normalized . $suffix;
            $suffix++;
        }

        return $candidate;
    }
}
