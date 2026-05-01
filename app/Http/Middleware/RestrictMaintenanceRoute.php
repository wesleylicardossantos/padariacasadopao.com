<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictMaintenanceRoute
{
    public function handle(Request $request, Closure $next): Response
    {
        $allowed = app()->environment(['local', 'testing'])
            || (bool) config('app.debug')
            || in_array($request->ip(), \App\Support\RuntimeConfig::maintenanceAllowedIps(), true);

        if (! $allowed) {
            abort(403, 'Rota de manutenção indisponível neste ambiente.');
        }

        return $next($request);
    }
}
