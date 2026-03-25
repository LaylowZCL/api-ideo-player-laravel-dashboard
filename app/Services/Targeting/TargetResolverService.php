<?php

namespace App\Services\Targeting;

use App\Models\AdGroup;
use App\Models\AdGroupTarget;
use App\Services\ActiveDirectoryService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TargetResolverService
{
    public function resolveUserGroupIds(?string $username, ?string $machineName = null): array
    {
        if (!$username) {
            return [];
        }

        $username = $this->normalize($username);
        $machineName = $machineName ? $this->normalize($machineName) : null;
        $source = $this->source();

        return $this->remember("user:{$source}:{$username}:{$machineName}", function () use ($source, $username, $machineName) {
            return $this->resolveUserGroupsFromSource($source, $username, $machineName);
        });
    }

    public function resolveMachineGroupIds(?string $machineName): array
    {
        if (!$machineName) {
            return [];
        }

        $machineName = $this->normalize($machineName);
        $source = $this->source();

        return $this->remember("machine:{$source}:{$machineName}", function () use ($source, $machineName) {
            return $this->resolveMachineGroupsFromSource($source, $machineName);
        });
    }

    public function resolveGroupIds(?string $username, ?string $machineName): array
    {
        $userGroups = $this->resolveUserGroupIds($username, $machineName);
        $machineGroups = $this->resolveMachineGroupIds($machineName);

        return array_values(array_unique(array_merge($userGroups, $machineGroups)));
    }

    private function resolveUserGroupsFromSource(string $source, string $username, ?string $machineName): array
    {
        $result = [];

        if ($source === 'ldap') {
            $result = $this->resolveUserGroupsFromLdap($username);
        } elseif ($source === 'json') {
            $result = $this->resolveUserGroupsFromJson($username, $machineName);
        } elseif ($source === 'hybrid') {
            $result = $this->resolveUserGroupsFromLdap($username);
            if (empty($result)) {
                $result = $this->resolveUserGroupsFromJson($username, $machineName);
            }
        }

        $this->debug('user', $username, $machineName, $source, $result);
        return $result;
    }

    private function resolveMachineGroupsFromSource(string $source, string $machineName): array
    {
        $result = [];

        if ($source === 'ldap') {
            $result = $this->resolveMachineGroupsFromLdap($machineName);
        } elseif ($source === 'json') {
            $result = $this->resolveMachineGroupsFromJson($machineName);
        } elseif ($source === 'hybrid') {
            $result = $this->resolveMachineGroupsFromLdap($machineName);
            if (empty($result)) {
                $result = $this->resolveMachineGroupsFromJson($machineName);
            }
        }

        $this->debug('machine', $machineName, null, $source, $result);
        return $result;
    }

    private function resolveUserGroupsFromJson(string $username, ?string $machineName): array
    {
        if (!$machineName) {
            return [];
        }

        return AdGroupTarget::query()
            ->where('machine_name', $machineName)
            ->where('user_name', $username)
            ->pluck('ad_group_id')
            ->unique()
            ->values()
            ->all();
    }

    private function resolveMachineGroupsFromJson(string $machineName): array
    {
        return AdGroupTarget::query()
            ->where('machine_name', $machineName)
            ->whereNull('user_name')
            ->pluck('ad_group_id')
            ->unique()
            ->values()
            ->all();
    }

    private function resolveUserGroupsFromLdap(string $username): array
    {
        $user = app(ActiveDirectoryService::class)->lookupUser($username);
        if (!$user) {
            return [];
        }

        $groups = $user['groups'] ?? [];
        return $this->mapGroupsToIds($groups);
    }

    private function resolveMachineGroupsFromLdap(string $machineName): array
    {
        $groups = app(ActiveDirectoryService::class)->getGroupsForComputer($machineName);
        return $this->mapGroupsToIds($groups);
    }

    private function mapGroupsToIds(array $groups): array
    {
        $ids = [];

        foreach ($groups as $group) {
            $dn = null;
            $name = null;

            if (is_array($group)) {
                $dn = $group['dn'] ?? null;
                $name = $group['name'] ?? null;
            } elseif (is_string($group)) {
                $dn = $group;
                $name = $this->extractGroupName($dn);
            }

            $name = $name ? trim($name) : null;
            $dn = $dn ? trim($dn) : null;

            if (!$name && !$dn) {
                continue;
            }

            $adGroup = AdGroup::query()
                ->when($dn && $name, fn ($q) => $q->where('dn', $dn)->orWhere('name', $name))
                ->when($dn && !$name, fn ($q) => $q->where('dn', $dn))
                ->when(!$dn && $name, fn ($q) => $q->where('name', $name))
                ->first();

            if (!$adGroup) {
                $adGroup = AdGroup::create([
                    'name' => $name ?: $dn,
                    'dn' => $dn,
                    'source' => 'ad',
                    'active' => true,
                ]);
            } elseif ($dn && !$adGroup->dn) {
                $adGroup->dn = $dn;
                $adGroup->save();
            }

            $ids[] = $adGroup->id;
        }

        return array_values(array_unique($ids));
    }

    private function extractGroupName(string $dn): string
    {
        if (preg_match('/CN=([^,]+)/i', $dn, $matches)) {
            return $matches[1];
        }

        return $dn;
    }

    private function remember(string $key, callable $resolver): array
    {
        if (!config('targeting.cache_enabled')) {
            return $resolver();
        }

        $ttl = max(60, (int) config('targeting.cache_ttl', 600));
        $cacheKey = 'targeting:' . $key;

        return Cache::remember($cacheKey, $ttl, $resolver);
    }

    private function source(): string
    {
        $source = strtolower((string) config('targeting.source', 'json'));
        if (!in_array($source, ['json', 'ldap', 'hybrid'], true)) {
            return 'json';
        }

        return $source;
    }

    private function normalize(string $value): string
    {
        return mb_strtolower(trim($value));
    }

    private function debug(string $type, ?string $principal, ?string $machine, string $source, array $result): void
    {
        if (!config('targeting.debug')) {
            return;
        }

        Log::info('TargetResolver', [
            'type' => $type,
            'principal' => $principal,
            'machine' => $machine,
            'source' => $source,
            'groups' => $result,
        ]);
    }
}
