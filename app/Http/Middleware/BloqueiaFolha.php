<?php

namespace App\Http\Middleware;

use Closure;

class BloqueiaFolha
{
    public function handle($request, Closure $next)
    {
        if(function_exists('folhaFechadaGlobal') && folhaFechadaGlobal()){
            return back()->with('error','Folha fechada. Alterações bloqueadas.');
        }

        return $next($request);
    }
}
