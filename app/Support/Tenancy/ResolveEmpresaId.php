<?php

namespace App\Support\Tenancy;

use Illuminate\Http\Request;

class ResolveEmpresaId
{
    public static function fromRequest(?Request $request = null, int $fallback = 0): int
    {
        return TenantContext::empresaId($request, $fallback);
    }
}
