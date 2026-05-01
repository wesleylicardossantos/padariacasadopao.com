<?php

namespace App\Services\MercadoPago;

use App\Models\ContaReceber;
use App\Models\MercadoPagoWebhookEvent;
use App\Models\Payment;
use App\Models\PlanoEmpresa;
use App\Modules\SaaS\Models\SaasSubscriptionCycle;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class MercadoPagoWebhookService
{
    public function storeIncomingEvent(Request $request): MercadoPagoWebhookEvent
    {
        $payload = $request->all();
        $topic = (string) ($request->input('type') ?: $request->input('topic') ?: Arr::get($payload, 'type') ?: Arr::get($payload, 'topic') ?: 'unknown');
        $resourceId = (string) (Arr::get($payload, 'data.id') ?: $request->query('data.id') ?: $request->input('id') ?: '');
        $action = (string) ($request->input('action') ?: Arr::get($payload, 'action') ?: '');

        $eventHash = hash('sha256', json_encode([
            'topic' => $topic,
            'resource_id' => $resourceId,
            'action' => $action,
            'payload' => $payload,
        ], JSON_UNESCAPED_UNICODE));

        $event = MercadoPagoWebhookEvent::firstOrCreate(
            ['event_hash' => $eventHash],
            [
                'topic' => $topic,
                'resource_id' => $resourceId,
                'action' => $action,
                'headers' => $request->headers->all(),
                'payload' => $payload,
                'status' => 'received',
            ]
        );

        return $event;
    }

    public function processEvent(int $eventId): void
    {
        $event = MercadoPagoWebhookEvent::find($eventId);
        if (! $event || $event->processed_at) {
            return;
        }

        try {
            if (! $this->shouldProcess($event)) {
                $event->update([
                    'status' => 'ignored',
                    'processed_at' => now(),
                ]);
                return;
            }

            $paymentData = $this->fetchPayment((string) $event->resource_id);

            DB::transaction(function () use ($event, $paymentData) {
                $payment = $this->upsertPayment($paymentData);
                $this->syncPlanAndSubscription($payment, $paymentData);
                $this->syncContaReceber($payment, $paymentData);

                $event->update([
                    'status' => 'processed',
                    'processed_at' => now(),
                    'error_message' => null,
                ]);
            });
        } catch (\Throwable $e) {
            $this->markFailed($eventId, $e);
            throw $e;
        }
    }

    public function markFailed(int $eventId, \Throwable $e): void
    {
        $event = MercadoPagoWebhookEvent::find($eventId);
        if (! $event) {
            return;
        }

        $event->update([
            'status' => 'failed',
            'error_message' => mb_substr($e->getMessage(), 0, 1000),
        ]);

        if (function_exists('__saveLogError')) {
            __saveLogError($e, 0);
        }
    }

    public function syncByMercadoPagoResponse($mercadoPagoPayment, ?Payment $localPayment = null): ?Payment
    {
        $payload = json_decode(json_encode($mercadoPagoPayment), true) ?: [];

        return DB::transaction(function () use ($payload, $localPayment) {
            $payment = $this->upsertPayment($payload, $localPayment);
            $this->syncPlanAndSubscription($payment, $payload);
            $this->syncContaReceber($payment, $payload);

            return $payment;
        });
    }

    protected function shouldProcess(MercadoPagoWebhookEvent $event): bool
    {
        return in_array($event->topic, ['payment', 'merchant_order', 'order'], true) && ! empty($event->resource_id);
    }

    protected function fetchPayment(string $paymentId): array
    {
        $response = Http::withToken($this->resolveAccessToken())
            ->acceptJson()
            ->get(rtrim(config('services.mercadopago.base_url'), '/') . '/v1/payments/' . $paymentId);

        $response->throw();

        return $response->json();
    }

    protected function resolveAccessToken(): string
    {
        $token = (string) config('services.mercadopago.access_token');

        if ($token === '') {
            throw new \RuntimeException('MERCADOPAGO_ACCESS_TOKEN_PRODUCAO / MERCADOPAGO_ACCESS_TOKEN não configurado.');
        }

        return $token;
    }

    protected function upsertPayment(array $paymentData, ?Payment $existing = null): Payment
    {
        $transacaoId = (string) (Arr::get($paymentData, 'id') ?: '');
        $externalReference = (string) (Arr::get($paymentData, 'external_reference') ?: '');

        $payment = $existing;

        if (! $payment && $transacaoId !== '') {
            $payment = Payment::where('transacao_id', $transacaoId)->first();
        }

        $planData = $this->parseExternalReference($externalReference);
        $planoEmpresaId = $payment?->plano_id ?: ($planData['plano_empresa_id'] ?? null);
        $empresaId = $payment?->empresa_id ?: ($planData['empresa_id'] ?? null);

        if (! $payment && $planoEmpresaId) {
            $payment = Payment::where('plano_id', $planoEmpresaId)->latest('id')->first();
        }

        if (! $payment && $planoEmpresaId) {
            $planoEmpresa = PlanoEmpresa::find($planoEmpresaId);
            $empresaId = $empresaId ?: optional($planoEmpresa)->empresa_id;

            $payment = new Payment();
            $payment->plano_id = $planoEmpresaId;
            $payment->empresa_id = $empresaId ?: 0;
            $payment->valor = (float) (Arr::get($paymentData, 'transaction_amount') ?: 0);
            $payment->forma_pagamento = (string) (Arr::get($paymentData, 'payment_method_id') ?: 'Mercado Pago');
            $payment->link_boleto = '';
            $payment->qr_code_base64 = '';
            $payment->qr_code = '';
            $payment->descricao = (string) (Arr::get($paymentData, 'description') ?: 'Pagamento Mercado Pago');
        }

        if (! $payment) {
            throw new \RuntimeException('Não foi possível localizar ou criar o pagamento local para o Mercado Pago.');
        }

        $payment->transacao_id = $transacaoId !== '' ? $transacaoId : (string) $payment->transacao_id;
        $payment->status = (string) (Arr::get($paymentData, 'status') ?: $payment->status ?: 'pending');
        $payment->status_detalhe = (string) (Arr::get($paymentData, 'status_detail') ?: $payment->status_detalhe ?: '');
        $payment->descricao = (string) (Arr::get($paymentData, 'description') ?: $payment->descricao ?: 'Pagamento Mercado Pago');
        $payment->valor = (float) (Arr::get($paymentData, 'transaction_amount') ?: $payment->valor ?: 0);
        $payment->forma_pagamento = (string) (Arr::get($paymentData, 'payment_method_id') ?: $payment->forma_pagamento ?: 'Mercado Pago');
        $payment->external_reference = $externalReference !== '' ? $externalReference : ($payment->external_reference ?: null);
        $payment->notification_url = (string) ($payment->notification_url ?: config('services.mercadopago.webhook_url'));
        $payment->raw_response = $paymentData;
        $payment->mp_status_last_sync_at = now();

        $dateApproved = Arr::get($paymentData, 'date_approved');
        if (! empty($dateApproved)) {
            $payment->paid_at = date('Y-m-d H:i:s', strtotime((string) $dateApproved));
        }

        $payment->save();

        return $payment;
    }

    protected function syncPlanAndSubscription(Payment $payment, array $paymentData): void
    {
        $planoEmpresa = PlanoEmpresa::find($payment->plano_id);
        if (! $planoEmpresa) {
            return;
        }

        $status = (string) ($payment->status ?: 'pending');
        $approvedAt = $payment->paid_at ?: now();

        if (Schema::hasColumn('plano_empresas', 'status_pagamento')) {
            $planoEmpresa->status_pagamento = $status;
        }

        if (Schema::hasColumn('plano_empresas', 'data_pagamento') && $payment->paid_at) {
            $planoEmpresa->data_pagamento = $payment->paid_at;
        }

        if ($status === 'approved') {
            $intervalDays = (int) (optional($planoEmpresa->plano)->intervalo_dias ?: 30);
            $baseDate = $planoEmpresa->expiracao && $planoEmpresa->expiracao >= now()->toDateString()
                ? $planoEmpresa->expiracao
                : now()->toDateString();
            $planoEmpresa->expiracao = date('Y-m-d', strtotime('+' . $intervalDays . ' days', strtotime($baseDate)));
            $planoEmpresa->mensagem_alerta = '';
        }

        $planoEmpresa->save();

        if (! Schema::hasTable('saas_subscription_cycles')) {
            return;
        }

        $cycle = SaasSubscriptionCycle::query()
            ->where('plano_empresa_id', $planoEmpresa->id)
            ->latest('id')
            ->first();

        if (! $cycle) {
            $cycle = new SaasSubscriptionCycle();
            $cycle->empresa_id = (int) $planoEmpresa->empresa_id;
            $cycle->plano_empresa_id = (int) $planoEmpresa->id;
            $cycle->period_start = now()->toDateString();
            $cycle->period_end = $planoEmpresa->expiracao;
            $cycle->status = $status === 'approved' ? 'active' : $status;
            $cycle->meta = [];
        }

        $cycle->status = $status === 'approved' ? 'active' : $status;
        $cycle->period_end = $planoEmpresa->expiracao;

        if (Schema::hasColumn('saas_subscription_cycles', 'mp_payment_id')) {
            $cycle->mp_payment_id = (string) $payment->transacao_id;
        }
        if (Schema::hasColumn('saas_subscription_cycles', 'payment_status')) {
            $cycle->payment_status = $status;
        }
        if (Schema::hasColumn('saas_subscription_cycles', 'paid_at')) {
            $cycle->paid_at = $payment->paid_at;
        }

        $meta = is_array($cycle->meta) ? $cycle->meta : [];
        $meta['mercadopago'] = [
            'external_reference' => $payment->external_reference,
            'status_detail' => Arr::get($paymentData, 'status_detail'),
            'payment_method_id' => Arr::get($paymentData, 'payment_method_id'),
            'last_sync_at' => now()->toDateTimeString(),
        ];
        $cycle->meta = $meta;
        $cycle->save();
    }

    protected function syncContaReceber(Payment $payment, array $paymentData): void
    {
        if (! Schema::hasTable('conta_recebers')) {
            return;
        }

        $references = array_filter(array_unique([
            (string) $payment->external_reference,
            'plano_empresa:' . (string) $payment->plano_id,
            (string) $payment->plano_id,
            (string) $payment->transacao_id,
        ]));

        $query = ContaReceber::query();
        $query->where(function ($q) use ($references) {
            foreach ($references as $reference) {
                $q->orWhere('referencia', $reference);
            }

            if (Schema::hasColumn('conta_recebers', 'mp_payment_id')) {
                foreach ($references as $reference) {
                    $q->orWhere('mp_payment_id', $reference);
                }
            }
        });

        $contas = $query->get();
        if ($contas->isEmpty()) {
            return;
        }

        foreach ($contas as $conta) {
            if (Schema::hasColumn('conta_recebers', 'mp_payment_id')) {
                $conta->mp_payment_id = (string) $payment->transacao_id;
            }
            if (Schema::hasColumn('conta_recebers', 'status_pagamento')) {
                $conta->status_pagamento = (string) $payment->status;
            }
            if (Schema::hasColumn('conta_recebers', 'valor_pago')) {
                $conta->valor_pago = (float) $payment->valor;
            }
            if (Schema::hasColumn('conta_recebers', 'data_pagamento') && $payment->paid_at) {
                $conta->data_pagamento = $payment->paid_at;
            }

            if ($payment->status === 'approved') {
                if (Schema::hasColumn('conta_recebers', 'status')) {
                    $conta->status = true;
                }
                if (Schema::hasColumn('conta_recebers', 'valor_recebido')) {
                    $conta->valor_recebido = (float) $payment->valor;
                }
                if (Schema::hasColumn('conta_recebers', 'data_recebimento') && $payment->paid_at) {
                    $conta->data_recebimento = date('Y-m-d', strtotime((string) $payment->paid_at));
                }
                if (Schema::hasColumn('conta_recebers', 'tipo_pagamento')) {
                    $conta->tipo_pagamento = (string) (Arr::get($paymentData, 'payment_method_id') ?: 'mercadopago');
                }
            }

            $conta->save();
        }
    }

    protected function parseExternalReference(?string $reference): array
    {
        $reference = trim((string) $reference);
        if ($reference === '') {
            return [];
        }

        if (preg_match('/^plano_empresa:(\d+)$/', $reference, $matches)) {
            return [
                'plano_empresa_id' => (int) $matches[1],
            ];
        }

        if (preg_match('/^empresa:(\d+)\|plano_empresa:(\d+)$/', $reference, $matches)) {
            return [
                'empresa_id' => (int) $matches[1],
                'plano_empresa_id' => (int) $matches[2],
            ];
        }

        return [];
    }
}
