<?php

namespace App\Observers;

use App\Models\Estoque;
use App\Modules\Estoque\Services\StockWriteAuditService;
use App\Modules\Estoque\Support\LegacyStockWriteGuard;

class EstoqueObserver
{
    public function __construct(
        private readonly LegacyStockWriteGuard $guard,
        private readonly StockWriteAuditService $auditService,
    ) {
    }

    public function creating(Estoque $estoque): void
    {
        $this->inspect('creating', null, $estoque);
    }

    public function updating(Estoque $estoque): void
    {
        $this->inspect('updating', $estoque->getOriginal(), $estoque);
    }

    public function deleting(Estoque $estoque): void
    {
        $this->inspect('deleting', $estoque->getOriginal(), null, $estoque);
    }

    private function inspect(string $event, ?array $before = null, ?Estoque $afterModel = null, ?Estoque $contextModel = null): void
    {
        $guardAllowed = $this->guard->isAllowed();
        $model = $afterModel ?? $contextModel;

        $payload = [
            'empresa_id' => $model?->empresa_id,
            'filial_id' => $model?->filial_id,
            'produto_id' => $model?->produto_id,
            'event' => $event,
            'legacy_stock_id' => $contextModel?->id,
            'before_state' => $before,
            'after_state' => $afterModel?->getAttributes(),
            'guard_source' => $this->guard->source(),
            'guard_allowed' => $guardAllowed,
            'performed_by' => function_exists('get_id_user') ? (int) get_id_user() : null,
            'request_path' => request()?->path(),
            'notes' => $guardAllowed
                ? 'Escrita em estoque legado permitida por compatibilidade do ledger.'
                : 'Escrita direta detectada fora do fluxo oficial do ledger.',
        ];

        $this->auditService->record($payload);

        if (!$guardAllowed && config('stock_governance.block_direct_legacy_writes', false)) {
            throw new \DomainException('Escrita direta na tabela de estoque legado bloqueada pela governança do ledger.');
        }
    }
}
