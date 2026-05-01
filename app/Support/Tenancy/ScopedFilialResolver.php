<?php

namespace App\Support\Tenancy;

use App\Models\Filial;
use Illuminate\Http\Request;

class ScopedFilialResolver
{
    public static function resolveForEmpresa(int $empresaId, mixed $candidate = null, ?Request $request = null): ?int
    {
        if ($empresaId <= 0) {
            return null;
        }

        $resolved = self::normalize($candidate);
        if ($resolved !== null && self::belongsToEmpresa($resolved, $empresaId)) {
            return $resolved;
        }

        $tenantFilialId = self::normalize(TenantContext::filialId($request));
        if ($tenantFilialId !== null && self::belongsToEmpresa($tenantFilialId, $empresaId)) {
            return $tenantFilialId;
        }

        return Filial::query()
            ->where('empresa_id', $empresaId)
            ->orderBy('id')
            ->value('id');
    }

    public static function belongsToEmpresa(?int $filialId, int $empresaId): bool
    {
        if ($filialId === null || $empresaId <= 0) {
            return false;
        }

        return Filial::query()
            ->where('id', $filialId)
            ->where('empresa_id', $empresaId)
            ->exists();
    }

    private static function normalize(mixed $value): ?int
    {
        if ($value === null || $value === '' || (int) $value <= 0 || (int) $value === -1) {
            return null;
        }

        return (int) $value;
    }
}
