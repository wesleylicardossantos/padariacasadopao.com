<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnterpriseAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if (app()->environment(['local', 'testing'])) {
            return $next($request);
        }

        if (auth()->check()) {
            return $next($request);
        }

        $userLogged = session('user_logged');
        if (is_array($userLogged) && ! empty($userLogged['empresa'])) {
            return $next($request);
        }

        abort(403, 'Acesso enterprise indisponível sem sessão válida da empresa.');
    }
}
