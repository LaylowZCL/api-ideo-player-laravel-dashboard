<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class FirstAccessPasswordController extends Controller
{
    public function show(Request $request)
    {
        return view('auth.force-password');
    }

    public function update(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user->password = Hash::make($request->input('password'));
        $user->must_change_password = false;
        $user->password_changed_at = now();
        $user->save();

        if (!config('two_factor.enabled')) {
            return redirect()->route('dashboard')->with('status', 'Palavra-passe alterada com sucesso.');
        }

        $request->session()->put('two_factor_passed', false);

        if ($user->hasTwoFactorEnabled()) {
            return redirect()->route('two-factor.challenge')->with('status', 'Palavra-passe alterada com sucesso.');
        }

        if (config('two_factor.required')) {
            return redirect()->route('two-factor.setup')->with('status', 'Palavra-passe alterada com sucesso.');
        }

        return redirect()->route('dashboard')->with('status', 'Palavra-passe alterada com sucesso.');
    }
}
