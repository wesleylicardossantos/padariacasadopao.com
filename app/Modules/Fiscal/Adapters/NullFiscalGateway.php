<?php

namespace App\Modules\Fiscal\Adapters;

class NullFiscalGateway implements LegacyFiscalGatewayInterface
{
    public function transmit(array $payload): array
    {
        return [
            'success' => true,
            'mode' => 'simulado',
            'status' => 'prepared',
            'message' => 'Documento fiscal preparado. Integração externa não acionada nesta fase.',
            'reference' => $payload['venda']['id'] ?? null,
            'generated_at' => now()->toDateTimeString(),
        ];
    }

    public function cancel(array $document, string $reason): array
    {
        return [
            'success' => true,
            'mode' => 'simulado',
            'status' => 'cancelled',
            'reason' => $reason,
            'document_id' => $document['id'] ?? null,
            'generated_at' => now()->toDateTimeString(),
        ];
    }

    public function status(array $document): array
    {
        return [
            'success' => true,
            'mode' => 'simulado',
            'status' => $document['status'] ?? 'desconhecido',
            'document_id' => $document['id'] ?? null,
            'generated_at' => now()->toDateTimeString(),
        ];
    }
}
