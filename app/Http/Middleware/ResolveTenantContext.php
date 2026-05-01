<?php

namespace App\Http\Middleware;

use App\Support\Tenancy\ResolveEmpresaId;
use App\Support\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenantContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $empresaId = ResolveEmpresaId::fromRequest($request, (int) (auth()->user()->empresa_id ?? 0));
        $filialId = TenantContext::filialId($request, auth()->user()->filial_id ?? null);
        $userId = TenantContext::userId($request, auth()->id());

        if ($empresaId > 0) {
            app()->instance('tenant.empresa_id', $empresaId);

            $request->attributes->set('tenant_empresa_id', $empresaId);
            $request->attributes->set('empresa_id', $empresaId);

            if ((int) $request->input('empresa_id', 0) !== $empresaId) {
                $request->merge(['empresa_id' => $empresaId]);
            }

            if (! session()->has('empresa_id') || (int) session('empresa_id') !== $empresaId) {
                session(['empresa_id' => $empresaId]);
            }
        }

        if ($filialId !== null) {
            app()->instance('tenant.filial_id', $filialId);
            $request->attributes->set('tenant_filial_id', $filialId);
            $request->attributes->set('filial_id', $filialId);

            if ((string) $request->input('filial_id', '') !== (string) $filialId) {
                $request->merge(['filial_id' => $filialId]);
            }

            if (! session()->has('filial_id') || (int) session('filial_id') !== (int) $filialId) {
                session(['filial_id' => $filialId]);
            }
        }

        if ($userId !== null) {
            app()->instance('tenant.user_id', $userId);
            $request->attributes->set('tenant_user_id', $userId);
        }

        return $next($request);
    }
}
