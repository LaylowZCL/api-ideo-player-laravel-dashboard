<?php

namespace App\Services\Auth;

use Illuminate\Support\Facades\Log;

class RoleMapperService
{
    public function mapGroupsToRole(array $groups): string
    {
        $map = config('ad.group_map', []);
        $groupsLower = array_map('strtolower', $groups);

        $ordered = [
            'super_admin',
            'admin',
            'manager',
        ];

        foreach ($ordered as $role) {
            $dn = $map[$role] ?? null;
            if (!$dn) {
                continue;
            }
            if (in_array(strtolower($dn), $groupsLower, true)) {
                $this->debug($role, $groups);
                return $role;
            }
        }

        $this->debug('user', $groups);
        return 'user';
    }

    private function debug(string $role, array $groups): void
    {
        if (!config('targeting.debug')) {
            return;
        }

        Log::info('RoleMapper', [
            'role' => $role,
            'groups' => $groups,
        ]);
    }
}
