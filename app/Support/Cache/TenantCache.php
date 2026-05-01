<?php

namespace App\Support\Cache;

use Closure;
use Illuminate\Support\Facades\Cache;

class TenantCache
{
    public static function remember(string $namespace, int $empresaId, string $suffix, $ttl, Closure $callback)
    {
        $key = self::key($namespace, $empresaId, $suffix);

        return Cache::remember($key, $ttl, $callback);
    }

    public static function key(string $namespace, int $empresaId, string $suffix): string
    {
        $empresaId = max(0, $empresaId);

        return implode(':', array_filter([
            'tenant',
            $namespace,
            'empresa',
            (string) $empresaId,
            trim($suffix, ':'),
        ], static fn ($value) => $value !== ''));
    }
}
