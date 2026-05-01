<?php

namespace App\Modules\RH\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RHContext
{
    public static function filialId(?Request $request = null): mixed
    {
        $request = $request ?: request();

        return $request?->attributes->get('filial_id')
            ?? $request?->input('filial_id')
            ?? $request?->query('filial_id')
            ?? ($request->filial_id ?? null)
            ?? (app()->bound('tenant.filial_id') ? app('tenant.filial_id') : null)
            ?? data_get(session('filial'), 'id');
    }

    public static function empresaId(?Request $request = null): int
    {
        $request = $request ?: request();

        $empresaId = $request?->attributes->get('empresa_id')
            ?? $request?->input('empresa_id')
            ?? $request?->query('empresa_id')
            ?? ($request->empresa_id ?? null)
            ?? (app()->bound('tenant.empresa_id') ? app('tenant.empresa_id') : null)
            ?? session('tenant.empresa_id')
            ?? session('funcionario_portal.empresa_id');

        if (!empty($empresaId)) {
            return (int) $empresaId;
        }

        $userLogged = session('user_logged');
        $empresaId = data_get($userLogged, 'empresa_id')
            ?? data_get($userLogged, 'empresa.id')
            ?? (is_array(data_get($userLogged, 'empresa')) || is_object(data_get($userLogged, 'empresa'))
                ? data_get($userLogged, 'empresa.id')
                : data_get($userLogged, 'empresa'));

        if (!empty($empresaId)) {
            return (int) $empresaId;
        }

        $user = Auth::user();
        if ($user && !empty($user->empresa_id)) {
            return (int) $user->empresa_id;
        }

        return 0;
    }

    public static function applyEmpresaScope(Builder $query, ?int $empresaId = null, string $table = ''): Builder
    {
        $empresaId = $empresaId ?: self::empresaId();
        if ($empresaId <= 0) {
            return $query;
        }

        $table = $table ?: $query->getModel()->getTable();
        if (DB::getSchemaBuilder()->hasColumn($table, 'empresa_id')) {
            $query->where($table . '.empresa_id', $empresaId);
        }

        return $query;
    }

    public static function applyTenantScope(Builder $query, ?int $empresaId = null, mixed $filialId = null, string $table = ''): Builder
    {
        $table = $table ?: $query->getModel()->getTable();
        self::applyEmpresaScope($query, $empresaId, $table);

        $filialId = $filialId ?? self::filialId();
        if ($filialId === null || $filialId === '' || $filialId === 'todos') {
            return $query;
        }

        if (DB::getSchemaBuilder()->hasColumn($table, 'filial_id')) {
            $query->where(function (Builder $inner) use ($table, $filialId) {
                $inner->where($table . '.filial_id', $filialId)
                    ->orWhereNull($table . '.filial_id');
            });
        }

        return $query;
    }
}
