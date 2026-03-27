<?php

namespace App\Http\Controllers;

use App\Models\AdGroup;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdGroupController extends Controller
{
    public function index()
    {
        $groups = AdGroup::orderBy('name')->get();

        return response()->json([
            'groups' => $groups,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'dn' => 'nullable|string|max:500',
            'sid' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'source' => 'nullable|string|in:manual,ad,client',
            'active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação',
                'errors' => $validator->errors(),
            ], 422);
        }

        $group = AdGroup::create(array_merge([
            'source' => 'manual',
            'active' => true,
        ], $validator->validated()));

        app(AuditLogService::class)->log('ad_group.create', 'success', [
            'group_id' => $group->id,
            'name' => $group->name,
        ]);

        return response()->json([
            'success' => true,
            'group' => $group,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $group = AdGroup::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'dn' => 'nullable|string|max:500',
            'sid' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'source' => 'nullable|string|in:manual,ad,client',
            'active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação',
                'errors' => $validator->errors(),
            ], 422);
        }

        $group->update($validator->validated());

        app(AuditLogService::class)->log('ad_group.update', 'success', [
            'group_id' => $group->id,
            'name' => $group->name,
        ]);

        return response()->json([
            'success' => true,
            'group' => $group,
        ]);
    }

    public function destroy($id)
    {
        $group = AdGroup::findOrFail($id);
        $group->delete();

        app(AuditLogService::class)->log('ad_group.delete', 'success', [
            'group_id' => $group->id,
            'name' => $group->name,
        ]);

        return response()->json([
            'success' => true,
        ]);
    }
}
