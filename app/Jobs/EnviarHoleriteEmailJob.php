<?php

namespace App\Jobs;

use App\Models\RHHoleriteEnvio;
use App\Services\RHHoleritePdfService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

class EnviarHoleriteEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(public int $envioId)
    {
    }

    private function envioUpdates(array $attributes): array
    {
        $table = (new RHHoleriteEnvio())->getTable();
        $allowed = [];

        foreach ($attributes as $column => $value) {
            if (Schema::hasColumn($table, $column)) {
                $allowed[$column] = $value;
            }
        }

        if (array_key_exists('ultima_falha', $attributes) && Schema::hasColumn($table, 'erro')) {
            $allowed['erro'] = $attributes['ultima_falha'];
        }

        if (array_key_exists('erro', $attributes) && Schema::hasColumn($table, 'ultima_falha')) {
            $allowed['ultima_falha'] = $attributes['erro'];
        }

        return $allowed;
    }

    private function loteStatusFila($lote): string
    {
        return 'fila';
    }

    public function handle(RHHoleritePdfService $holeritePdfService): void
    {
        $envio = RHHoleriteEnvio::with(['lote', 'funcionario'])->find($this->envioId);
        if (!$envio || !$envio->lote || !$envio->funcionario) {
            return;
        }

        if ($envio->status === 'cancelado' || $envio->lote->status === 'cancelado') {
            $envio->update($this->envioUpdates([
                'status' => 'cancelado',
                'ultima_falha' => 'Envio não processado porque o lote foi cancelado.',
            ]));
            $envio->lote->recalculateStatus();
            return;
        }

        $envio->update($this->envioUpdates([
            'status' => 'processando',
            'tentativas' => (int) $envio->tentativas + 1,
            'ultima_tentativa_em' => now(),
            'ultima_falha' => null,
        ]));
        $envio->lote->recalculateStatus();

        $mes = (int) $envio->lote->mes;
        $ano = (int) $envio->lote->ano;
        $empresaId = (int) ($envio->empresa_id ?: optional($envio->lote)->empresa_id ?: optional($funcionario)->empresa_id ?: 0);
        $funcionario = $envio->funcionario;
        $email = trim((string) $envio->email);

        if ($email === '') {
            $envio->update($this->envioUpdates([
                'status' => 'sem_email',
                'ultima_falha' => 'Funcionário sem e-mail cadastrado.',
            ]));
            $envio->lote->recalculateStatus();
            return;
        }

        $pdf = $holeritePdfService->gerarPdfPorFuncionarioEmpresa($empresaId, (int) $funcionario->id, $mes, $ano);

        Mail::send('mail.holerite_competencia', [
            'funcionario' => $funcionario,
            'mes' => $mes,
            'ano' => $ano,
        ], function ($m) use ($email, $funcionario, $pdf, $mes, $ano) {
            $assunto = sprintf('Holerite %02d/%04d - %s', $mes, $ano, $funcionario->nome ?? 'Funcionário');
            $emailEnvio = env('MAIL_USERNAME') ?: env('MAIL_FROM_ADDRESS');
            $nomeEnvio = config('app.name', 'Sistema RH');

            if (!empty($emailEnvio)) {
                $m->from($emailEnvio, $nomeEnvio);
            }

            $m->to($email, $funcionario->nome ?? 'Funcionário');
            $m->subject($assunto);
            $m->attachData($pdf['content'], $pdf['filename'], ['mime' => 'application/pdf']);
        });

        $envio->update($this->envioUpdates([
            'status' => 'enviado',
            'enviado_em' => now(),
            'ultima_falha' => null,
        ]));
        $envio->lote->recalculateStatus();
    }

    public function failed(\Throwable $e): void
    {
        $envio = RHHoleriteEnvio::with('lote')->find($this->envioId);
        if (!$envio) {
            return;
        }

        if ($envio->status === 'cancelado' || optional($envio->lote)->status === 'cancelado') {
            return;
        }

        $envio->update($this->envioUpdates([
            'status' => 'falha',
            'ultima_falha' => mb_substr($e->getMessage(), 0, 1000),
            'ultima_tentativa_em' => now(),
        ]));

        if ($envio->lote) {
            $envio->lote->recalculateStatus();
        }

        if (function_exists('__saveLogError')) {
            __saveLogError($e, (int) ($envio->empresa_id ?: optional($envio->lote)->empresa_id ?: optional($envio->funcionario)->empresa_id ?: 0));
        }
    }
}
