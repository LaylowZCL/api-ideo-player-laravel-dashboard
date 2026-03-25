<?php

namespace App\Services;

use App\Models\Client;
use App\Services\Targeting\TargetResolverService;

class AdTargetingService
{
    public function getGroupIdsForMachine(?string $machine): array
    {
        return app(TargetResolverService::class)->resolveMachineGroupIds($machine);
    }

    public function getGroupIdsForMachineUser(?string $machine, ?string $username): array
    {
        $resolver = app(TargetResolverService::class);
        $userGroups = $resolver->resolveUserGroupIds($username, $machine);
        if (!empty($userGroups)) {
            return $userGroups;
        }

        return $resolver->resolveMachineGroupIds($machine);
    }

    public function resolveTargetGroupIds(?Client $client, ?string $machine, ?string $username): array
    {
        $resolver = app(TargetResolverService::class);
        $groupIds = $resolver->resolveGroupIds($username, $machine);
        if (!empty($groupIds)) {
            return $groupIds;
        }

        return $client ? $client->adGroups->pluck('id')->all() : [];
    }
}
