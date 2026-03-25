<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(GateContract $gate): void
    {
        $this->registerPolicies();

        // Gate para super admin (acesso total)
        $gate->define('isSuperAdmin', function ($user) {
            return $user->isSuperAdmin();
        });

        // Gate para admin (inclui super admin)
        $gate->define('isAdmin', function ($user) {
            return $user->isAdmin();
        });

        // Gate para manager (inclui admin e super admin)
        $gate->define('isManager', function ($user) {
            return $user->isManager();
        });

        // Gate para usuário regular (todos têm essa permissão)
        $gate->define('isUser', function ($user) {
            return true;
        });

        // Gate específico para apenas manager (não admin, não super admin)
        $gate->define('isManagerOnly', function ($user) {
            return $user->roleName() === 'manager';
        });
    }
}
