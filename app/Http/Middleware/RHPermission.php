<?php

namespace App\Http\Middleware;

use App\Services\RH\RHAccessControlService;
use Closure;

class RHPermission
{
    public function __construct(private RHAccessControlService $acl)
    {
    }

    public function handle($request, Closure $next, string $permission)
    {
        $this->acl->syncDefaultSetup();
        $this->acl->abortIfDenied($permission, null, (int) ($request->empresa_id ?? data_get(session('user_logged'), 'empresa') ?? 0));

        return $next($request);
    }
}
