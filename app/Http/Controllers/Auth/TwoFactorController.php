<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\TwoFactorService;
use Illuminate\Http\Request;

class TwoFactorController extends Controller
{
    public function showSetup(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return redirect()->route('login');
        }

        $alreadyEnabled = $user->hasTwoFactorEnabled();
        $secret = null;
        $otpauth = null;

        if (!$alreadyEnabled) {
            $service = app(TwoFactorService::class);
            $secret = $request->session()->get('two_factor_setup_secret');
            if (!$secret) {
                $secret = $service->generateSecret();
                $request->session()->put('two_factor_setup_secret', $secret);
            }

            $otpauth = sprintf(
                'otpauth://totp/%s:%s?secret=%s&issuer=%s',
                rawurlencode(config('app.name')),
                rawurlencode($user->email ?? $user->name ?? 'user'),
                $secret,
                rawurlencode(config('app.name'))
            );
        }

        return view('auth.two-factor-setup', [
            'alreadyEnabled' => $alreadyEnabled,
            'secret' => $secret,
            'otpauth' => $otpauth,
        ]);
    }

    public function enable(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return redirect()->route('login');
        }

        $request->validate([
            'code' => 'required|string|min:6|max:10',
        ]);

        $secret = $request->session()->get('two_factor_setup_secret');
        if (!$secret) {
            return back()->withErrors(['code' => 'Sessão expirada. Gere novamente o segredo.']);
        }

        $service = app(TwoFactorService::class);
        if (!$service->verifyCode($secret, $request->input('code'))) {
            return back()->withErrors(['code' => 'Código inválido.']);
        }

        $user->two_factor_secret = $service->encryptSecret($secret);
        $user->two_factor_recovery_codes = json_encode($service->recoveryCodes());
        $user->two_factor_confirmed_at = now();
        $user->save();

        $request->session()->forget('two_factor_setup_secret');
        $request->session()->put('two_factor_passed', true);

        return redirect()->route('dashboard')->with('status', '2FA ativado com sucesso.');
    }

    public function showChallenge(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return redirect()->route('login');
        }

        if (!$user->hasTwoFactorEnabled()) {
            return redirect()->route('two-factor.setup');
        }

        return view('auth.two-factor-challenge');
    }

    public function verifyChallenge(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return redirect()->route('login');
        }

        $request->validate([
            'code' => 'nullable|string|min:6|max:10',
            'recovery_code' => 'nullable|string|min:6|max:20',
        ]);

        $service = app(TwoFactorService::class);
        $secret = $service->decryptSecret($user->two_factor_secret);

        if ($secret && $request->filled('code')) {
            if ($service->verifyCode($secret, $request->input('code'))) {
                $request->session()->put('two_factor_passed', true);
                return redirect()->intended('/dashboard');
            }
        }

        if ($request->filled('recovery_code')) {
            $codes = json_decode($user->two_factor_recovery_codes ?? '[]', true) ?: [];
            $index = array_search($request->input('recovery_code'), $codes, true);
            if ($index !== false) {
                unset($codes[$index]);
                $user->two_factor_recovery_codes = json_encode(array_values($codes));
                $user->save();

                $request->session()->put('two_factor_passed', true);
                return redirect()->intended('/dashboard');
            }
        }

        return back()->withErrors(['code' => 'Código inválido.']);
    }

    public function disable(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return redirect()->route('login');
        }

        $user->two_factor_secret = null;
        $user->two_factor_recovery_codes = null;
        $user->two_factor_confirmed_at = null;
        $user->save();

        $request->session()->forget('two_factor_passed');

        return redirect()->route('dashboard')->with('status', '2FA desativado.');
    }
}
