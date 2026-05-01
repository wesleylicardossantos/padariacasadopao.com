<?php

namespace App\Modules\Financeiro\Data;

use Illuminate\Http\Request;

class FinanceFilterData
{
    public function __construct(
        public readonly ?string $startDate,
        public readonly ?string $endDate,
        public readonly ?string $typeSearch,
        public readonly mixed $status,
        public readonly mixed $filialId,
        public readonly ?int $partyId,
    ) {
    }

    public static function fromRequest(Request $request, string $partyField): self
    {
        $filialId = $request->get('filial_id');
        $localPadrao = function_exists('__get_local_padrao') ? __get_local_padrao() : null;

        if (($filialId === null || $filialId === '') && $localPadrao) {
            $filialId = $localPadrao;
        }

        $partyId = $request->get($partyField);

        return new self(
            startDate: $request->get('start_date') ?: null,
            endDate: $request->get('end_date') ?: null,
            typeSearch: $request->get('type_search') ?: 'data_vencimento',
            status: $request->get('status'),
            filialId: $filialId ?? 'todos',
            partyId: filled($partyId) ? (int) $partyId : null,
        );
    }

    public function normalizedFilialId(): mixed
    {
        if ($this->filialId === -1 || $this->filialId === '-1') {
            return null;
        }

        return $this->filialId;
    }

    public function hasStatusFilter(): bool
    {
        return $this->status !== '' && $this->status !== null;
    }
}
