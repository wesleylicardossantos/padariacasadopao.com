<?php

namespace App\Modules\Estoque\Support;

class LegacyStockWriteGuard
{
    private int $depth = 0;
    private ?string $currentSource = null;

    public function runWithinGuard(callable $callback, string $source = 'stock_ledger')
    {
        $this->depth++;
        $previousSource = $this->currentSource;
        $this->currentSource = $source;

        try {
            return $callback();
        } finally {
            $this->depth = max(0, $this->depth - 1);
            $this->currentSource = $this->depth > 0 ? $previousSource : null;
        }
    }

    public function isAllowed(): bool
    {
        return $this->depth > 0;
    }

    public function source(): ?string
    {
        return $this->currentSource;
    }
}
