<?php

namespace Tests\Feature;

use App\Models\AdGroupTarget;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdJsonImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_json_creates_targets(): void
    {
        Storage::fake('local');

        $payload = [
            [
                'Maquina' => 'DISPLAY001',
                'Usuario' => 'joao',
                'Grupo' => 'GRP_VIDEO_MANHA',
                'Data' => '2026-03-20 08:00:00',
            ],
            [
                'Maquina' => 'DISPLAY001',
                'Usuario' => 'joao',
                'Grupo' => 'GRP_VIDEO_TARDE',
                'Data' => '2026-03-20 08:00:00',
            ],
        ];

        $path = storage_path('app/AD/ad-groups.json');
        if (!is_dir(storage_path('app/AD'))) {
            mkdir(storage_path('app/AD'), 0755, true);
        }
        file_put_contents($path, json_encode($payload));

        $this->artisan('ad:import-json')->assertExitCode(0);

        $this->assertDatabaseCount('ad_group_targets', 2);
        $this->assertDatabaseHas('ad_group_targets', [
            'machine_name' => 'display001',
            'user_name' => 'joao',
        ]);
    }
}
