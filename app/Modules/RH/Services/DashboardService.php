<?php

namespace App\Modules\RH\Services;

use App\Modules\RH\Repositories\ApuracaoMensalRepository;
use App\Modules\RH\Repositories\FuncionarioRepository;
use App\Modules\RH\Repositories\MovimentacaoRepository;

class DashboardService
{
    protected $funcionarios;
    protected $apuracoes;
    protected $movimentacoes;

    public function __construct(
        FuncionarioRepository $funcionarios,
        ApuracaoMensalRepository $apuracoes,
        MovimentacaoRepository $movimentacoes
    ) {
        $this->funcionarios = $funcionarios;
        $this->apuracoes = $apuracoes;
        $this->movimentacoes = $movimentacoes;
    }

    public function getIndicadores(int $empresaId): array
    {
        return [
            'empresaId' => $empresaId,
            'totalFuncionarios' => $this->funcionarios->countAll($empresaId),
            'ativos' => $this->funcionarios->countAtivos($empresaId),
            'inativos' => $this->funcionarios->countInativos($empresaId),
            'folhaMensal' => $this->funcionarios->sumFolhaAtiva($empresaId),
            'admissoesMes' => $this->funcionarios->countAdmissoesMes($empresaId),
            'pagamentosMes' => $this->apuracoes->sumPagamentosMes($empresaId),
            'movimentacoesRecentes' => $this->movimentacoes->recentes($empresaId),
        ];
    }
}
