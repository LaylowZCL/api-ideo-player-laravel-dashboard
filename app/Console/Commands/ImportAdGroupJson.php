<?php

namespace App\Console\Commands;

use App\Models\AdGroup;
use App\Models\AdGroupTarget;
use App\Models\Client;
use App\Services\AdGroupJsonService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class ImportAdGroupJson extends Command
{
    protected $signature = 'ad:import-json {--path=}';
    protected $description = 'Importa mapeamento de grupos AD a partir de JSON diário.';

    public function handle(): int
    {
        /** @var AdGroupJsonService $jsonService */
        $jsonService = app(AdGroupJsonService::class);
        $resolvedPath = $jsonService->getAdImportPath();
        $expectedPath = $jsonService->getExpectedAdImportPath();
        $expectedFile = $expectedPath ? basename($expectedPath) : 'ficheiro configurado';

        if (!$resolvedPath || !is_file($resolvedPath)) {
            $this->error("Arquivo JSON AD não encontrado em {$expectedFile}.");
            return self::FAILURE;
        }

        $raw = $jsonService->getTargetRecordsFromPath($resolvedPath);
        if (empty($raw)) {
            $this->error('JSON inválido, vazio ou com codificação não suportada.');
            return self::FAILURE;
        }

        $byMachine = [];
        $targets = [];
        foreach ($raw as $record) {
            $machineKey = $record['machine'];
            $groupName = $record['group'];
            $userKey = $record['user'];

            $byMachine[$machineKey][] = $groupName;
            $targets[] = [
                'machine' => $machineKey,
                'user' => $userKey,
                'user_display_name' => $record['name'] ?? null,
                'user_email' => $record['email'] ?? null,
                'group' => $groupName,
                'effective_at' => $this->parseDate($record['effective_at']),
            ];
        }

        $createdClients = 0;
        $updatedClients = 0;
        $groupNames = [];

        foreach ($byMachine as $machine => $groups) {
            $client = Client::firstOrCreate(
                ['client_id' => $machine],
                ['first_seen_at' => now(), 'last_seen_at' => now()]
            );

            if ($client->wasRecentlyCreated) {
                $createdClients++;
            } else {
                $updatedClients++;
            }

            $groupIds = [];
            foreach (array_unique($groups) as $groupName) {
                $groupNames[] = $groupName;
                $group = AdGroup::firstOrCreate(
                    ['name' => $groupName],
                    ['source' => 'ad', 'active' => true]
                );
                $groupIds[] = $group->id;
            }

            if (!empty($groupIds)) {
                $client->adGroups()->sync($groupIds);
            }
        }

        foreach ($targets as $target) {
            $group = AdGroup::firstOrCreate(
                ['name' => $target['group']],
                ['source' => 'ad', 'active' => true]
            );

            $client = Client::where('client_id', $target['machine'])->first();

            AdGroupTarget::updateOrCreate(
                [
                    'machine_name' => $target['machine'],
                    'user_name' => $target['user'],
                    'ad_group_id' => $group->id,
                ],
                [
                    'client_id' => $client?->id,
                    'user_display_name' => $target['user_display_name'],
                    'user_email' => $target['user_email'],
                    'effective_at' => $target['effective_at'],
                    'source' => 'json',
                ]
            );
        }

        $this->info('Importação concluída.');
        $this->line('Clientes criados: ' . $createdClients);
        $this->line('Clientes atualizados: ' . $updatedClients);
        $this->line('Grupos processados: ' . count(array_unique($groupNames)));
        $this->line('Alvos processados: ' . count($targets));

        $status = [
            'last_import_at' => now()->toDateTimeString(),
            'source_path' => $resolvedPath,
            'records' => count($raw),
            'clients_created' => $createdClients,
            'clients_updated' => $updatedClients,
            'groups_processed' => count(array_unique($groupNames)),
            'targets_processed' => count($targets),
        ];
        Storage::disk('local')->put('AD/import-status.json', json_encode($status, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return self::SUCCESS;
    }

    private function parseDate($value): ?Carbon
    {
        if (!$value) {
            return now();
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable $e) {
            return now();
        }
    }

}
