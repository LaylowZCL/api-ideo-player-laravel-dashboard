<?php

namespace App\Console\Commands;

use App\Models\AdGroup;
use App\Models\AdGroupTarget;
use App\Models\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class ImportAdGroupJson extends Command
{
    protected $signature = 'ad:import-json {--path=}';
    protected $description = 'Importa mapeamento de grupos AD a partir de JSON diário.';

    public function handle(): int
    {
        $path = $this->option('path') ?: config('ad.group_json_path');
        if (!$path || !is_file($path)) {
            $this->error('Arquivo JSON não encontrado: ' . ($path ?: 'null'));
            return self::FAILURE;
        }

        $raw = json_decode(file_get_contents($path), true);
        if (!is_array($raw)) {
            $this->error('JSON inválido.');
            return self::FAILURE;
        }

        $byMachine = [];
        $targets = [];
        foreach ($raw as $record) {
            if (!is_array($record)) {
                continue;
            }

            $machine = $record['Maquina'] ?? $record['maquina'] ?? $record['Machine'] ?? $record['machine'] ?? null;
            $user = $record['Usuario'] ?? $record['usuario'] ?? $record['User'] ?? $record['user'] ?? $record['username'] ?? $record['Username'] ?? null;
            $group = $record['Grupo'] ?? $record['grupo'] ?? $record['Group'] ?? $record['group'] ?? null;
            $date = $record['Data'] ?? $record['data'] ?? $record['Date'] ?? $record['date'] ?? null;
            if (!$machine || !$group) {
                continue;
            }

            $machineKey = mb_strtolower(trim((string) $machine));
            $groupName = trim((string) $group);
            $userKey = $user ? mb_strtolower(trim((string) $user)) : null;
            if ($machineKey === '' || $groupName === '') {
                continue;
            }

            $byMachine[$machineKey][] = $groupName;
            $targets[] = [
                'machine' => $machineKey,
                'user' => $userKey,
                'group' => $groupName,
                'effective_at' => $this->parseDate($date),
            ];
        }

        $createdClients = 0;
        $updatedClients = 0;

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
                    'effective_at' => $target['effective_at'],
                    'source' => 'json',
                ]
            );
        }

        $this->info('Importação concluída.');
        $this->line('Clientes criados: ' . $createdClients);
        $this->line('Clientes atualizados: ' . $updatedClients);

        $status = [
            'last_import_at' => now()->toDateTimeString(),
            'source_path' => $path,
            'records' => count($raw),
            'clients_created' => $createdClients,
            'clients_updated' => $updatedClients,
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
