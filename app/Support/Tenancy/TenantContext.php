<?php

namespace App\Support\Tenancy;

use Illuminate\Http\Request;

class TenantContext
{
    public static function empresaId(?Request $request = null, int $fallback = 0): int
    {
        return self::resolveInt([
            $request?->input('empresa_id'),
            $request?->route('empresa_id'),
            $request?->attributes->get('tenant_empresa_id'),
            $request?->attributes->get('empresa_id'),
            $request?->empresa_id ?? null,
            app()->bound('tenant.empresa_id') ? app('tenant.empresa_id') : null,
            session('tenant.empresa_id'),
            session('user_logged.empresa'),
            session('empresa_id'),
            session('empresa'),
            auth()->user()->empresa_id ?? null,
            $fallback,
        ]);
    }

    public static function filialId(?Request $request = null, ?int $fallback = null): ?int
    {
        return self::resolveNullableInt([
            $request?->input('filial_id'),
            $request?->route('filial_id'),
            $request?->attributes->get('tenant_filial_id'),
            $request?->attributes->get('filial_id'),
            app()->bound('tenant.filial_id') ? app('tenant.filial_id') : null,
            session('tenant.filial_id'),
            session('filial_id'),
            session('user_logged.filial'),
            auth()->user()->filial_id ?? null,
            $fallback,
        ]);
    }

    public static function userId(?Request $request = null, ?int $fallback = null): ?int
    {
        return self::resolveNullableInt([
            $request?->input('usuario_id'),
            $request?->input('user_id'),
            $request?->input('pdv_usuario_id'),
            $request?->attributes->get('tenant_user_id'),
            app()->bound('tenant.user_id') ? app('tenant.user_id') : null,
            session('tenant.user_id'),
            session('user_logged.id'),
            auth()->id(),
            $fallback,
        ]);
    }

    public static function userLogged(?Request $request = null): array
    {
        $sessionUser = session('user_logged');
        if (is_array($sessionUser)) {
            return $sessionUser;
        }

        $user = auth()->user();
        if (! $user) {
            return [];
        }

        return [
            'id' => $user->id,
            'empresa' => self::empresaId($request, (int) ($user->empresa_id ?? 0)),
            'empresa_id' => self::empresaId($request, (int) ($user->empresa_id ?? 0)),
            'filial' => self::filialId($request, $user->filial_id ?? null),
            'filial_id' => self::filialId($request, $user->filial_id ?? null),
            'login' => $user->login ?? $user->email ?? null,
            'nome_empresa' => $user->empresa?->nome ?? null,
        ];
    }

    public static function empresaName(?Request $request = null, string $fallback = ''): string
    {
        return (string) (data_get(self::userLogged($request), 'nome_empresa') ?: $fallback);
    }

    public static function snapshot(?Request $request = null): array
    {
        return [
            'empresa_id' => self::empresaId($request),
            'filial_id' => self::filialId($request),
            'user_id' => self::userId($request),
        ];
    }

    private static function resolveInt(array $candidates): int
    {
        foreach ($candidates as $candidate) {
            if ($candidate === null || $candidate === '') {
                continue;
            }

            return (int) $candidate;
        }

        return 0;
    }

    private static function resolveNullableInt(array $candidates): ?int
    {
        foreach ($candidates as $candidate) {
            if ($candidate === null || $candidate === '') {
                continue;
            }

            return (int) $candidate;
        }

        return null;
    }
}
