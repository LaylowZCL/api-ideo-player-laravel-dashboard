<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ClientController extends Controller
{
    public function index()
    {
        $clients = Client::with('adGroups')
            ->orderByDesc('last_seen_at')
            ->get();

        return response()->json([
            'clients' => $clients,
        ]);
    }

    public function update(Request $request, $id)
    {
        $client = Client::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'hostname' => 'nullable|string|max:255',
            'platform' => 'nullable|string|max:50',
            'version' => 'nullable|string|max:50',
            'api_key' => 'nullable|string|max:255',
            'ad_dn' => 'nullable|string|max:500',
            'ad_sid' => 'nullable|string|max:255',
            'ad_group_ids' => 'nullable|array',
            'ad_group_ids.*' => 'integer|exists:ad_groups,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        $groupIds = $data['ad_group_ids'] ?? null;
        unset($data['ad_group_ids']);

        $client->update($data);

        if (is_array($groupIds)) {
            $client->adGroups()->sync($groupIds);
        }

        app(AuditLogService::class)->log('client.update', 'success', [
            'client_id' => $client->id,
            'client_code' => $client->client_id,
            'groups' => $groupIds,
        ]);

        return response()->json([
            'success' => true,
            'client' => $client->load('adGroups'),
        ]);
    }
}
