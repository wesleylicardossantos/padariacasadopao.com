@extends('default.layout', ['title' => 'RH Modular'])
@section('content')
<div class="container-fluid">
    <div class="card mt-3 border-0 shadow-sm" style="border-radius: 18px;">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                <div>
                    <h4 class="mb-1">RH modular iniciado</h4>
                    <p class="text-muted mb-0">Estrutura nova criada sem remover o legado. Este painel usa Service + Repository.</p>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="/rh" class="btn btn-outline-secondary">Dashboard legado</a>
                    <a href="/rh/modular/folha" class="btn btn-primary">Resumo da folha modular</a>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-3"><div class="card h-100 border-0 bg-light"><div class="card-body"><small class="text-muted d-block">Funcionários</small><strong>{{ $totalFuncionarios }}</strong></div></div></div>
                <div class="col-md-3"><div class="card h-100 border-0 bg-light"><div class="card-body"><small class="text-muted d-block">Ativos</small><strong>{{ $ativos }}</strong></div></div></div>
                <div class="col-md-3"><div class="card h-100 border-0 bg-light"><div class="card-body"><small class="text-muted d-block">Inativos</small><strong>{{ $inativos }}</strong></div></div></div>
                <div class="col-md-3"><div class="card h-100 border-0 bg-light"><div class="card-body"><small class="text-muted d-block">Folha mensal</small><strong>R$ {{ number_format((float) $folhaMensal, 2, ',', '.') }}</strong></div></div></div>
            </div>

            <div class="row g-3 mt-1">
                <div class="col-md-4"><div class="card h-100 border-0 bg-light"><div class="card-body"><small class="text-muted d-block">Admissões no mês</small><strong>{{ $admissoesMes }}</strong></div></div></div>
                <div class="col-md-4"><div class="card h-100 border-0 bg-light"><div class="card-body"><small class="text-muted d-block">Pagamentos do mês</small><strong>R$ {{ number_format((float) $pagamentosMes, 2, ',', '.') }}</strong></div></div></div>
                <div class="col-md-4"><div class="card h-100 border-0 bg-light"><div class="card-body"><small class="text-muted d-block">Empresa</small><strong>{{ $empresaId ?: '-' }}</strong></div></div></div>
            </div>

            <div class="card mt-4 border-0 shadow-sm">
                <div class="card-header bg-white"><strong>Movimentações recentes</strong></div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Funcionário</th>
                                <th>Tipo</th>
                                <th>Descrição</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($movimentacoesRecentes as $mov)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($mov->data_movimentacao)->format('d/m/Y') }}</td>
                                    <td>{{ optional($mov->funcionario)->nome }}</td>
                                    <td>{{ ucfirst($mov->tipo) }}</td>
                                    <td>{{ $mov->descricao }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">Sem movimentações para exibir.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
