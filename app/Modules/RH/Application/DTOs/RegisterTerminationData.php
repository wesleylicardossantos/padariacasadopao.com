<?php

namespace App\Modules\RH\Application\DTOs;

use App\Models\Funcionario;

final class RegisterTerminationData
{
    public function __construct(
        public int $empresaId,
        public Funcionario $funcionario,
        public string $dataDesligamento,
        public string $motivo,
        public string $tipo,
        public ?string $tipoAviso = null,
        public int $dependentesIrrf = 0,
        public float $descontosExtras = 0.0,
        public ?string $observacao = null,
        public bool $gerarTrct = true,
        public bool $gerarTqrct = true,
        public bool $gerarHomologacao = true,
        public bool $bloquearPortal = true,
        public bool $arquivoMorto = true,
        public ?int $usuarioId = null,
    ) {
    }

    public function rescisaoPayload(): array
    {
        return [
            'data_rescisao' => $this->dataDesligamento,
            'motivo' => $this->motivo,
            'tipo_aviso' => (string) ($this->tipoAviso ?: 'indenizado'),
            'dependentes_irrf' => $this->dependentesIrrf,
            'descontos_extras' => $this->descontosExtras,
            'observacao' => (string) ($this->observacao ?? ''),
            'gerar_trct' => $this->gerarTrct,
            'gerar_tqrct' => $this->gerarTqrct,
            'gerar_homologacao' => $this->gerarHomologacao,
            'bloquear_portal' => $this->bloquearPortal,
            'arquivo_morto' => $this->arquivoMorto,
        ];
    }
}
