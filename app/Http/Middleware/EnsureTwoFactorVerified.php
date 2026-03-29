<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureTwoFactorVerified
{
    public function handle(Request $request, Closure $next)
    {
        if (app()->environment('testing')) {
            return $next($request);
        }

        if (!config('two_factor.enabled')) {
            return $next($request);
        }

        if ($request->routeIs('two-factor.*')) {
            return $next($request);
        }

        if ($request->routeIs('force-password.*')) {
            return $next($request);
        }

        $user = $request->user();
        if (!$user) {
            return $next($request);
        }

        if ($user->requiresPasswordChange()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Password change required',
                    'code' => 'password_change_required',
                ], 403);
            }

            return redirect()->route('force-password.edit');
        }

        if (config('two_factor.required') && empty($user->two_factor_secret)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Two-factor setup required',
                    'code' => 'two_factor_setup_required',
                ], 403);
            }

            return redirect()->route('two-factor.setup');
        }

        if ($user->hasTwoFactorEnabled() && !$request->session()->get('two_factor_passed')) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Two-factor verification required',
                    'code' => 'two_factor_required',
                ], 403);
            }

            return redirect()->route('two-factor.challenge');
        }

        return $next($request);
    }
}
