<?php

namespace Tests\Feature;

use App\Models\AdGroup;
use App\Models\AdGroupTarget;
use App\Models\Client;
use App\Services\AdTargetingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdTargetingServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_resolves_user_specific_groups_first(): void
    {
        $groupUser = AdGroup::create(['name' => 'GRP_USER', 'source' => 'ad', 'active' => true]);
        $groupMachine = AdGroup::create(['name' => 'GRP_MACHINE', 'source' => 'ad', 'active' => true]);

        $client = Client::create(['client_id' => 'display001']);

        AdGroupTarget::create([
            'client_id' => $client->id,
            'machine_name' => 'display001',
            'user_name' => 'joao',
            'ad_group_id' => $groupUser->id,
        ]);

        AdGroupTarget::create([
            'client_id' => $client->id,
            'machine_name' => 'display001',
            'user_name' => null,
            'ad_group_id' => $groupMachine->id,
        ]);

        $service = app(AdTargetingService::class);
        $result = $service->resolveTargetGroupIds($client, 'display001', 'joao');

        $this->assertSame([$groupUser->id], $result);
    }
}
