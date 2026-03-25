<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActiveDirectoryService;
use App\Services\Auth\RoleMapperService;
use App\Services\AuditLogService;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/dashboard';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    public function showLoginForm()
    {
        if (config('ad.enabled') && config('ad.sso_enabled')) {
            $request = request();
            $ssoLogin = $this->getSsoLogin($request);
            if ($ssoLogin) {
                $adUser = app(ActiveDirectoryService::class)->lookupUser($ssoLogin);
                if ($adUser) {
                    $user = $this->syncAdUser($adUser);
                    $this->guard()->login($user, true);
                    app(AuditLogService::class)->log('auth.login', 'success', [
                        'auth_source' => 'ad_sso',
                        'login' => $ssoLogin,
                    ]);
                    return redirect()->intended($this->redirectTo);
                }
            }
        }

        return view('auth.login');
    }

    public function username()
    {
        return 'login';
    }

    protected function credentials(Request $request)
    {
        $login = $request->input('login');

        return [
            'email' => $login,
            'password' => $request->input('password'),
        ];
    }

    protected function attemptLogin(Request $request)
    {
        if (!config('ad.enabled')) {
            return $this->guard()->attempt(
                $this->credentials($request), $request->filled('remember')
            );
        }

        $login = $request->input($this->username());
        $password = $request->input('password');

        if (config('ad.sso_enabled') && empty($password)) {
            $ssoLogin = $this->getSsoLogin($request);
            if ($ssoLogin) {
                $adUser = app(ActiveDirectoryService::class)->lookupUser($ssoLogin);
                if ($adUser) {
                    $user = $this->syncAdUser($adUser);
                    $this->guard()->login($user, true);
                    app(AuditLogService::class)->log('auth.login', 'success', [
                        'auth_source' => 'ad_sso',
                        'login' => $ssoLogin,
                    ]);
                    return true;
                }
            }
        }

        $adUser = app(ActiveDirectoryService::class)->authenticate($login, $password);
        if ($adUser) {
            $user = $this->syncAdUser($adUser);
            $this->guard()->login($user, $request->filled('remember'));
            app(AuditLogService::class)->log('auth.login', 'success', [
                'auth_source' => 'ad',
                'login' => $login,
            ]);
            return true;
        }

        if (config('ad.allow_local_fallback')) {
            $result = $this->guard()->attempt(
                $this->credentials($request), $request->filled('remember')
            );
            app(AuditLogService::class)->log('auth.login', $result ? 'success' : 'failed', [
                'auth_source' => 'local',
                'login' => $login,
            ], $result ? 'info' : 'warning');
            return $result;
        }

        app(AuditLogService::class)->log('auth.login', 'failed', [
            'auth_source' => 'ad',
            'login' => $login,
        ], 'warning');
        return false;
    }

    protected function authenticated(Request $request, $user)
    {
        if (!config('two_factor.enabled')) {
            return null;
        }

        $request->session()->put('two_factor_passed', false);

        if ($user->hasTwoFactorEnabled()) {
            return redirect()->route('two-factor.challenge');
        }

        if (config('two_factor.required')) {
            return redirect()->route('two-factor.setup');
        }

        return null;
    }

    protected function loggedOut(Request $request)
    {
        $request->session()->forget('two_factor_passed');
        $request->session()->forget('two_factor_setup_secret');
    }

    private function syncAdUser(array $adUser): User
    {
        $email = $adUser['email'] ?? null;
        $username = $adUser['username'] ?? $adUser['name'] ?? 'ad-user';
        $name = $adUser['name'] ?? $username;

        $user = User::firstOrCreate(
            ['email' => $email ?: $username . '@local.ad'],
            [
                'name' => $name,
                'password' => Hash::make(Str::random(32)),
                'user_type' => 'user',
            ]
        );

        $mappedRole = app(RoleMapperService::class)->mapGroupsToRole($adUser['groups'] ?? []);
        if ($mappedRole) {
            $user->role = $mappedRole;
            $user->user_type = $mappedRole;
            $user->save();
        }

        if ($user->name !== $name) {
            $user->name = $name;
            $user->save();
        }

        return $user;
    }

    private function getSsoLogin(Request $request): ?string
    {
        $header = config('ad.sso_header', 'REMOTE_USER');
        $raw = $request->server($header) ?: $request->header($header);

        if (!$raw) {
            return null;
        }

        $value = trim($raw);
        if ($value === '') {
            return null;
        }

        if (str_contains($value, '\\')) {
            $parts = explode('\\', $value);
            $value = end($parts);
        }

        if (str_contains($value, '@')) {
            $value = strstr($value, '@', true);
        }

        return $value ?: null;
    }
}
