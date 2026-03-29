<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class EnsureModulePermission
{
    public function handle(Request $request, Closure $next, string $module)
    {
        $user = $request->user();

        if (!$user) {
            abort(403, 'Não tem permissão para aceder a este recurso.');
        }

        if (!Gate::allows('access-module', $module)) {
            abort(403, 'Não tem permissão para aceder a este recurso.');
        }

        return $next($request);
    }
}
