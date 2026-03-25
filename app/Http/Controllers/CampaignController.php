<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CampaignController extends Controller
{
    public function index()
    {
        $campaigns = Campaign::orderByDesc('priority')
            ->orderBy('name')
            ->get();

        return response()->json([
            'campaigns' => $campaigns,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'active' => 'nullable|boolean',
            'priority' => 'nullable|integer',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação',
                'errors' => $validator->errors(),
            ], 422);
        }

        $campaign = Campaign::create(array_merge([
            'active' => true,
            'priority' => 0,
        ], $validator->validated()));

        app(AuditLogService::class)->log('campaign.create', 'success', [
            'campaign_id' => $campaign->id,
            'name' => $campaign->name,
        ]);

        return response()->json([
            'success' => true,
            'campaign' => $campaign,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $campaign = Campaign::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'active' => 'nullable|boolean',
            'priority' => 'nullable|integer',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação',
                'errors' => $validator->errors(),
            ], 422);
        }

        $campaign->update($validator->validated());

        app(AuditLogService::class)->log('campaign.update', 'success', [
            'campaign_id' => $campaign->id,
            'name' => $campaign->name,
        ]);

        return response()->json([
            'success' => true,
            'campaign' => $campaign,
        ]);
    }

    public function destroy($id)
    {
        $campaign = Campaign::findOrFail($id);
        $campaign->delete();

        app(AuditLogService::class)->log('campaign.delete', 'success', [
            'campaign_id' => $campaign->id,
            'name' => $campaign->name,
        ]);

        return response()->json([
            'success' => true,
        ]);
    }
}
