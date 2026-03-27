<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'user_type',
        'role',
        'permissions',
        'is_superadmin',
        'is_admin',
        'is_manager',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'two_factor_confirmed_at' => 'datetime',
        'permissions' => 'array',
    ];

    public const MODULE_PERMISSIONS = [
        'dashboard',
        'videos',
        'schedules',
        'reports',
        'users',
        'groups',
        'targets',
        'clients',
        'campaigns',
        'logs',
        'settings',
    ];

    public function hasTwoFactorEnabled(): bool
    {
        return !empty($this->two_factor_secret) && $this->two_factor_confirmed_at !== null;
    }

    /**
     * Check if user is a super admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->roleName() === 'super_admin';
    }

    /**
     * Check if user is an admin (includes super admin)
     */
    public function isAdmin(): bool
    {
        return in_array($this->roleName(), ['super_admin', 'admin'], true);
    }

    /**
     * Check if user is a manager (includes admin)
     */
    public function isManager(): bool
    {
        return in_array($this->roleName(), ['super_admin', 'admin', 'manager'], true);
    }

    public function roleName(): string
    {
        if (!empty($this->role)) {
            return (string) $this->role;
        }

        if (!empty($this->user_type)) {
            return (string) $this->user_type;
        }

        return 'user';
    }

    public function permissionList(): array
    {
        if ($this->isSuperAdmin()) {
            return self::MODULE_PERMISSIONS;
        }

        $permissions = $this->permissions;
        if (is_array($permissions) && !empty($permissions)) {
            return array_values(array_unique(array_intersect(self::MODULE_PERMISSIONS, $permissions)));
        }

        return $this->defaultPermissionsForRole($this->roleName());
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return in_array($permission, $this->permissionList(), true);
    }

    public function syncLegacyRoleFields(): void
    {
        $role = $this->roleName();
        $this->user_type = $role;
        $this->is_superadmin = $role === 'super_admin';
        $this->is_admin = in_array($role, ['super_admin', 'admin'], true);
        $this->is_manager = in_array($role, ['super_admin', 'admin', 'manager'], true);
    }

    public static function defaultPermissionsForRole(string $role): array
    {
        return match ($role) {
            'super_admin' => self::MODULE_PERMISSIONS,
            'admin' => ['dashboard', 'videos', 'schedules', 'reports', 'users', 'groups', 'targets', 'clients', 'campaigns', 'logs', 'settings'],
            'manager' => ['dashboard', 'videos', 'schedules', 'reports', 'clients', 'campaigns'],
            default => ['dashboard', 'reports'],
        };
    }
}
