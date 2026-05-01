<?php

namespace App\Http\Middleware;

use App\Modules\SaaS\Services\PlanLimitService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceSaasPlan
{
    protected PlanLimitService $limits;

    public function __construct(PlanLimitService $limits)
    {
        $this->limits = $limits;
    }

    public function handle(Request $request, Closure $next, string $feature = ''): Response
    {
        $empresaId = (int) ($request->empresa_id ?? session('user_logged.empresa') ?? auth()->user()->empresa_id ?? 0);

        if ($empresaId <= 0 || $feature === '') {
            return $next($request);
        }

        $check = $this->limits->check($empresaId, $feature);
        if (! ($check['allowed'] ?? true)) {
            abort(402, $check['message'] ?? 'Limite do plano excedido.');
        }

        return $next($request);
    }
}
