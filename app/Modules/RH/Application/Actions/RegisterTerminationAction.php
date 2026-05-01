<?php

namespace App\Modules\RH\Application\Actions;

use App\Models\Funcionario;
use App\Models\RHDesligamento;
use App\Modules\RH\Application\DTOs\RegisterTerminationData;
use App\Services\RHDesligamentoStatusSyncService;
use App\Services\RHRescisaoService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class RegisterTerminationAction
{
    public function __construct(
        private RHDesligamentoStatusSyncService $statusSync,
        private RHRescisaoService $rescisaoService,
    ) {
    }

    public function execute(RegisterTerminationData $data): array
    {
        return DB::transaction(function () use ($data) {
            $desligamentoExistente = RHDesligamento::query()
                ->where('empresa_id', $data->empresaId)
                ->where('funcionario_id', $data->funcionario->id)
                ->whereDate('data_desligamento', $data->dataDesligamento)
                ->first();

            if ($desligamentoExistente) {
                throw new \RuntimeException('Já existe desligamento registrado para este funcionário na mesma data.');
            }

            $desligamento = RHDesligamento::create([
                'empresa_id' => $data->empresaId,
                'funcionario_id' => $data->funcionario->id,
                'data_desligamento' => $data->dataDesligamento,
                'motivo' => $data->motivo,
                'tipo' => $data->tipo,
                'observacao' => $data->observacao,
                'usuario_id' => $data->usuarioId,
            ]);

            $rescisao = $this->rescisaoService->processar($data->funcionario, array_merge(
                ['desligamento_id' => $desligamento->id],
                $data->rescisaoPayload()
            ));

            if ($rescisao && Schema::hasColumn('rh_desligamentos', 'rescisao_id')) {
                $desligamento->update(['rescisao_id' => $rescisao->id]);
            }

            $this->syncFuncionarioStatus($data->funcionario, $data->empresaId, $data->dataDesligamento);

            return compact('desligamento', 'rescisao');
        });
    }

    private function syncFuncionarioStatus(Funcionario $funcionario, int $empresaId, string $dataDesligamento): void
    {
        $devePermanecerAtivo = Carbon::parse($dataDesligamento)->startOfDay()->isFuture();
        $payload = [];

        if (Schema::hasColumn('funcionarios', 'ativo')) {
            $payload['ativo'] = $devePermanecerAtivo ? 1 : 0;
        }
        if (Schema::hasColumn('funcionarios', 'status')) {
            $payload['status'] = $devePermanecerAtivo ? 'Ativo' : 'Inativo';
        }
        if (Schema::hasColumn('funcionarios', 'data_desligamento') && !$devePermanecerAtivo) {
            $payload['data_desligamento'] = $dataDesligamento;
        }

        if ($payload !== []) {
            Funcionario::query()
                ->withoutGlobalScope('rh_status_visibility')
                ->where('empresa_id', $empresaId)
                ->where('id', $funcionario->id)
                ->update($payload);
            $funcionario->forceFill($payload);
        }

        $this->statusSync->syncFuncionario($funcionario, $empresaId);
    }
}
