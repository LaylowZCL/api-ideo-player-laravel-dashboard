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

        // Definição dos Gates baseados em user_type
        $gate->define('isAdmin', function ($user) {
            return $user->user_type === 'admin';
        });

        $gate->define('isManager', function ($user) {
            return $user->user_type === 'manager' || $user->user_type === 'admin';
        });

        $gate->define('isUser', function ($user) {
            return $user->user_type === 'user' || $user->user_type === 'manager' || $user->user_type === 'admin';
        });
        
        // Gate específico para apenas manager (não admin)
        $gate->define('isManagerOnly', function ($user) {
            return $user->user_type === 'manager';
        });
    }
}
