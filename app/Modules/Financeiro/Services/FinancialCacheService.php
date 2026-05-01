<?php

namespace App\Modules\Financeiro\Services;

use Illuminate\Support\Facades\Cache;

class FinancialCacheService
{
    public function remember(string $segment, int $empresaId, $filialId, \DateTimeInterface|int $ttl, callable $callback): mixed
    {
        return Cache::remember($this->key($segment, $empresaId, $filialId), $ttl, $callback);
    }

    public function forgetByEmpresa(int $empresaId, $filialId = null): void
    {
        $filiais = array_unique(array_filter([
            'todos',
            (string) $filialId,
            $filialId,
            null,
            '',
            '-1',
            -1,
        ], static fn ($value) => $value !== null && $value !== ''));

        foreach ($filiais as $scope) {
            foreach (['snapshot', 'overview', 'aging', 'cashflow'] as $segment) {
                Cache::forget($this->key($segment, $empresaId, $scope));
            }
        }
    }

    public function key(string $segment, int $empresaId, $filialId): string
    {
        $scope = ($filialId === null || $filialId === '') ? 'todos' : (string) $filialId;

        return sprintf('tenant:%s:financeiro:%s:%s', $empresaId, $segment, $scope);
    }
}
