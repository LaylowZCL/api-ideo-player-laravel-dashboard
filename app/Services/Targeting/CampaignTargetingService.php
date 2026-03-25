<?php

namespace App\Services\Targeting;

use App\Models\Client;
use App\Models\Schedule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class CampaignTargetingService
{
    public function buildQuery(?Client $client, string $day, ?string $username, ?string $machineName): Builder
    {
        $now = now();
        $context = $this->getTargetContext($client, $username, $machineName);

        $query = Schedule::query()
            ->where('active', true)
            ->whereJsonContains('days', $day)
            ->with(['targetGroups', 'targetClients', 'campaign']);

        $query->where(function ($campaignQuery) use ($now) {
            $campaignQuery
                ->whereNull('campaign_id')
                ->orWhereHas('campaign', function ($q) use ($now) {
                    $q->where('active', true)
                        ->where(function ($dateQuery) use ($now) {
                            $dateQuery->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
                        })
                        ->where(function ($dateQuery) use ($now) {
                            $dateQuery->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
                        });
                });
        });

        $query->where(function ($targetQuery) use ($client, $context) {
            $targetQuery->where(function ($q) {
                $q->whereDoesntHave('targetGroups')
                    ->whereDoesntHave('targetClients');
            });

            if ($client) {
                $targetQuery->orWhereHas('targetClients', function ($q) use ($client) {
                    $q->where('clients.id', $client->id);
                });
            }

            if (!empty($context['group_ids'])) {
                $targetQuery->orWhereHas('targetGroups', function ($q) use ($context) {
                    $q->whereIn('ad_groups.id', $context['group_ids']);
                });
            }
        });

        $this->debug('query', [
            'client_id' => $client?->client_id,
            'username' => $username,
            'machine' => $machineName,
            'context' => $context,
        ]);

        return $query;
    }

    public function getTargetContext(?Client $client, ?string $username, ?string $machineName): array
    {
        $resolver = app(TargetResolverService::class);

        $userGroups = $resolver->resolveUserGroupIds($username, $machineName);
        $machineGroups = $resolver->resolveMachineGroupIds($machineName);

        $groupIds = array_values(array_unique(array_merge($userGroups, $machineGroups)));

        if (empty($groupIds) && $client) {
            $groupIds = $client->adGroups->pluck('id')->all();
        }

        return [
            'user_groups' => $userGroups,
            'machine_groups' => $machineGroups,
            'group_ids' => $groupIds,
        ];
    }

    public function sortSchedules(Collection $schedules, array $context, ?Client $client): Collection
    {
        return $schedules->sort(function ($a, $b) use ($context, $client) {
            $aPriority = $this->targetPriority($a, $context, $client);
            $bPriority = $this->targetPriority($b, $context, $client);
            if ($aPriority !== $bPriority) {
                return $bPriority <=> $aPriority;
            }
            if ($a->time !== $b->time) {
                return $a->time <=> $b->time;
            }
            $aCampaign = $a->campaign?->priority ?? 0;
            $bCampaign = $b->campaign?->priority ?? 0;
            if ($aCampaign !== $bCampaign) {
                return $bCampaign <=> $aCampaign;
            }
            if (($a->priority ?? 0) !== ($b->priority ?? 0)) {
                return ($b->priority ?? 0) <=> ($a->priority ?? 0);
            }
            return $a->id <=> $b->id;
        })->values();
    }

    private function targetPriority($schedule, array $context, ?Client $client): int
    {
        $userGroups = $context['user_groups'] ?? [];
        $machineGroups = $context['machine_groups'] ?? [];
        $allGroups = $context['group_ids'] ?? [];

        $groupIds = $schedule->targetGroups?->pluck('id')->all() ?? [];
        $clientIds = $schedule->targetClients?->pluck('id')->all() ?? [];

        if (!empty($userGroups) && count(array_intersect($groupIds, $userGroups)) > 0) {
            return 4;
        }

        if ($client && in_array($client->id, $clientIds, true)) {
            return 3;
        }

        if (!empty($machineGroups) && count(array_intersect($groupIds, $machineGroups)) > 0) {
            return 3;
        }

        if (!empty($allGroups) && count(array_intersect($groupIds, $allGroups)) > 0) {
            return 2;
        }

        return 1;
    }

    private function debug(string $stage, array $payload): void
    {
        if (!config('targeting.debug')) {
            return;
        }

        Log::info('CampaignTargeting', [
            'stage' => $stage,
            'payload' => $payload,
        ]);
    }
}
