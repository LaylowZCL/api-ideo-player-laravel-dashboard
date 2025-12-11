<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class InternalApiAccess
{
    public function handle(Request $request, Closure $next)
    {
        // Verifica se a requisição vem do Vue (header personalizado)
        // OU se é uma requisição AJAX
        $isVueRequest = $request->header('X-Request-Source') === 'Vue-Component';
        $isAjax = $request->ajax() || $request->wantsJson();
        
        // Se não for Vue nem AJAX, bloqueia
        if (!$isVueRequest && !$isAjax) {
            return response()->json([
                'error' => 'Acesso restrito',
                'message' => 'Endpoint disponível apenas para a aplicação'
            ], 403);
        }
        
        return $next($request);
    }
}