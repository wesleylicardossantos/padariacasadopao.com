<?php

namespace App\Modules\Fiscal\Adapters;

interface LegacyFiscalGatewayInterface
{
    public function transmit(array $payload): array;

    public function cancel(array $document, string $reason): array;

    public function status(array $document): array;
}
