<?php

namespace App\Http\Controllers;

use App\Models\AdGroupTarget;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdTargetController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'machine' => 'nullable|string|max:255',
            'user' => 'nullable|string|max:255',
            'group' => 'nullable|string|max:255',
            'per_page' => 'nullable|integer|min:10|max:200',
        ]);

        $query = AdGroupTarget::query()
            ->with(['adGroup', 'client'])
            ->orderByDesc('effective_at');

        if ($request->filled('machine')) {
            $query->where('machine_name', mb_strtolower(trim($request->machine)));
        }

        if ($request->filled('user')) {
            $query->where('user_name', mb_strtolower(trim($request->user)));
        }

        if ($request->filled('group')) {
            $query->whereHas('adGroup', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->group . '%');
            });
        }

        $perPage = $request->integer('per_page', 25);
        $targets = $query->paginate($perPage);

        $targets->getCollection()->transform(function ($target) {
            return [
                'id' => $target->id,
                'machine_name' => $target->machine_name,
                'user_name' => $target->user_name,
                'user_display_name' => $target->user_display_name,
                'user_email' => $target->user_email,
                'group' => $target->adGroup?->name,
                'effective_at' => optional($target->effective_at)->toDateTimeString(),
                'source' => $target->source,
                'client_id' => $target->client?->client_id,
            ];
        });

        return response()->json([
            'success' => true,
            'targets' => $targets,
        ]);
    }
}
