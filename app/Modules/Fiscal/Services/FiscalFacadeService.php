<?php

namespace App\Modules\Fiscal\Services;

use App\Modules\Fiscal\Adapters\LegacyFiscalGatewayInterface;
use App\Modules\Fiscal\Adapters\NullFiscalGateway;
use App\Modules\Fiscal\Models\FiscalDocument;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class FiscalFacadeService
{
    public function __construct(
        private readonly InvoiceService $invoiceService,
        private readonly FiscalDocumentRepository $repository,
        private readonly FiscalAuditService $auditService,
        private readonly ?LegacyFiscalGatewayInterface $gateway = null,
    ) {
    }

    public function prepare(int $empresaId, array $payload, ?int $usuarioId = null): FiscalDocument
    {
        return DB::transaction(function () use ($empresaId, $payload, $usuarioId) {
            $prepared = $this->invoiceService->preparePayload(
                (array) ($payload['sale'] ?? []),
                (array) ($payload['company'] ?? []),
                (array) ($payload['customer'] ?? [])
            );

            $document = $this->repository->create([
                'empresa_id' => $empresaId,
                'venda_id' => Arr::get($payload, 'sale.id'),
                'tipo_documento' => (string) ($payload['document_type'] ?? 'nfce'),
                'numero_referencia' => (string) ($payload['reference'] ?? ('PREP-'.now()->format('YmdHis'))),
                'status' => 'prepared',
                'payload_preparado' => $prepared,
                'prepared_at' => now(),
                'created_by' => $usuarioId,
                'updated_by' => $usuarioId,
            ]);

            $this->auditService->record([
                'empresa_id' => $empresaId,
                'usuario_id' => $usuarioId,
                'documento_id' => $document->id,
                'acao' => 'prepared',
                'depois' => $document->toArray(),
                'meta' => ['document_type' => $document->tipo_documento],
            ]);

            return $document;
        });
    }

    public function transmit(FiscalDocument $document, ?int $usuarioId = null): FiscalDocument
    {
        return DB::transaction(function () use ($document, $usuarioId) {
            $before = $document->toArray();
            $gateway = $this->gateway ?? app(NullFiscalGateway::class);
            $response = $gateway->transmit((array) $document->payload_preparado);

            $document = $this->repository->update($document, [
                'status' => (string) ($response['status'] ?? 'prepared'),
                'retorno_integracao' => $response,
                'chave_acesso' => $response['access_key'] ?? $document->chave_acesso,
                'transmitted_at' => now(),
                'updated_by' => $usuarioId,
            ]);

            $this->auditService->record([
                'empresa_id' => $document->empresa_id,
                'usuario_id' => $usuarioId,
                'documento_id' => $document->id,
                'acao' => 'transmitted',
                'antes' => $before,
                'depois' => $document->toArray(),
                'meta' => $response,
            ]);

            return $document;
        });
    }

    public function cancel(FiscalDocument $document, string $reason, ?int $usuarioId = null): FiscalDocument
    {
        return DB::transaction(function () use ($document, $reason, $usuarioId) {
            $before = $document->toArray();
            $gateway = $this->gateway ?? app(NullFiscalGateway::class);
            $response = $gateway->cancel($document->toArray(), $reason);

            $document = $this->repository->update($document, [
                'status' => 'cancelled',
                'retorno_integracao' => $response,
                'motivo' => $reason,
                'cancelled_at' => now(),
                'updated_by' => $usuarioId,
            ]);

            $this->auditService->record([
                'empresa_id' => $document->empresa_id,
                'usuario_id' => $usuarioId,
                'documento_id' => $document->id,
                'acao' => 'cancelled',
                'antes' => $before,
                'depois' => $document->toArray(),
                'meta' => $response,
            ]);

            return $document;
        });
    }

    public function status(FiscalDocument $document): array
    {
        $gateway = $this->gateway ?? app(NullFiscalGateway::class);

        return $gateway->status($document->toArray());
    }
}
