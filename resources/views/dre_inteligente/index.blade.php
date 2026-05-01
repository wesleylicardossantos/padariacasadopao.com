@extends('default.layout',['title' => 'RH - DRE Inteligente'])
@section('content')
<style>
.rh-card{border:1px solid #e8edf5;border-radius:16px;box-shadow:0 8px 24px rgba(15,23,42,.04)}
.rh-kpi{border:1px solid #e8edf5;border-radius:14px;background:#fff}
.rh-kpi .card-body{padding:1rem}
.rh-kpi .label{font-size:.78rem;text-transform:uppercase;font-weight:700;color:#64748b}
.rh-kpi .value{font-size:1.35rem;font-weight:800;color:#0f172a}
.alerta{border-left:4px solid #f59e0b;background:#fff7ed;padding:.85rem 1rem;border-radius:10px}
</style>

<div class="page-content">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h5 class="mb-0">DRE Inteligente da Folha</h5>
            <small class="text-muted">Visão executiva do custo de RH, impacto no faturamento e comparativo mensal.</small>
        </div>
        <div class="d-flex gap-2">
            <a href="/rh/dre-folha" class="btn btn-dark">DRE com Folha</a>
            <a href="/rh/folha" class="btn btn-secondary">Voltar para Folha</a>
        </div>
    </div>

    <form method="GET" action="/rh/dre-inteligente">
        <div class="card rh-card mb-3">
            <div class="card-body p-4">
                <div class="row g-3 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label">Mês</label>
                        <input type="number" class="form-control" name="mes" min="1" max="12" value="{{ $mes }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Ano</label>
                        <input type="number" class="form-control" name="ano" value="{{ $ano }}">
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary w-100">Atualizar</button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <div class="row g-3 mb-3">
        <div class="col-lg-3 col-md-6"><div class="card rh-kpi"><div class="card-body"><div class="label">Receita Bruta</div><div class="value">R$ {{ number_format((float)$receitaBruta,2,',','.') }}</div></div></div></div>
        <div class="col-lg-3 col-md-6"><div class="card rh-kpi"><div class="card-body"><div class="label">Total RH</div><div class="value">R$ {{ number_format((float)$totalRh,2,',','.') }}</div></div></div></div>
        <div class="col-lg-3 col-md-6"><div class="card rh-kpi"><div class="card-body"><div class="label">% Folha/Faturamento</div><div class="value">{{ number_format((float)$pesoFolha,2,',','.') }}%</div></div></div></div>
        <div class="col-lg-3 col-md-6"><div class="card rh-kpi"><div class="card-body"><div class="label">Margem Operacional</div><div class="value">{{ number_format((float)$margemOperacional,2,',','.') }}%</div></div></div></div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-lg-4"><div class="card rh-kpi"><div class="card-body"><div class="label">Custo médio por colaborador</div><div class="value">R$ {{ number_format((float)$custoMedioFuncionario,2,',','.') }}</div></div></div></div>
        <div class="col-lg-4"><div class="card rh-kpi"><div class="card-body"><div class="label">% Férias no RH</div><div class="value">{{ number_format((float)$pesoFerias,2,',','.') }}%</div></div></div></div>
        <div class="col-lg-4"><div class="card rh-kpi"><div class="card-body"><div class="label">Resultado Operacional</div><div class="value">R$ {{ number_format((float)$resultadoOperacional,2,',','.') }}</div></div></div></div>
    </div>

    @if(!empty($alertas))
    <div class="card rh-card mb-3">
        <div class="card-body">
            <h6 class="mb-3">Alertas Gerenciais</h6>
            <div class="d-grid gap-2">
                @foreach($alertas as $alerta)
                    <div class="alerta">{{ $alerta }}</div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <div class="row g-3 mb-3">
        <div class="col-lg-7">
            <div class="card rh-card">
                <div class="card-header bg-transparent"><h6 class="mb-0">Composição Inteligente do RH</h6></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <tbody>
                                <tr><th>Salários fixos</th><td class="text-end">R$ {{ number_format((float)$custos['salarios'],2,',','.') }}</td></tr>
                                <tr><th>Eventos variáveis</th><td class="text-end">R$ {{ number_format((float)$custos['eventos'],2,',','.') }}</td></tr>
                                <tr><th>Férias</th><td class="text-end">R$ {{ number_format((float)$custos['ferias'],2,',','.') }}</td></tr>
                                <tr><th>1/3 de férias</th><td class="text-end">R$ {{ number_format((float)$custos['um_terco_ferias'],2,',','.') }}</td></tr>
                                <tr><th>Encargos</th><td class="text-end">R$ {{ number_format((float)$custos['encargos'],2,',','.') }}</td></tr>
                                <tr><th>Benefícios</th><td class="text-end">R$ {{ number_format((float)$custos['beneficios'],2,',','.') }}</td></tr>
                                <tr><th>Provisões</th><td class="text-end">R$ {{ number_format((float)$custos['provisoes'],2,',','.') }}</td></tr>
                                <tr><th>Despesas operacionais fora RH</th><td class="text-end">R$ {{ number_format((float)$despesasOperacionais,2,',','.') }}</td></tr>
                                <tr><th><strong>Total RH</strong></th><td class="text-end"><strong>R$ {{ number_format((float)$totalRh,2,',','.') }}</strong></td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card rh-card">
                <div class="card-header bg-transparent"><h6 class="mb-0">Comparativo Mensal</h6></div>
                <div class="card-body">
                    <p class="mb-2">Receita: <strong>{{ number_format((float)$comparativo['receita'],2,',','.') }}%</strong></p>
                    <p class="mb-2">RH: <strong>{{ number_format((float)$comparativo['rh'],2,',','.') }}%</strong></p>
                    <p class="mb-0">Resultado: <strong>{{ number_format((float)$comparativo['resultado'],2,',','.') }}%</strong></p>
                </div>
            </div>

            <div class="card rh-card mt-3">
                <div class="card-header bg-transparent"><h6 class="mb-0">Top 10 custos por funcionário</h6></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Funcionário</th>
                                    <th>Função</th>
                                    <th class="text-end">Custo</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($ranking as $linha)
                                <tr>
                                    <td>{{ $linha['nome'] ?? data_get($linha, 'funcionario.nome') ?? '—' }}</td>
                                    <td>{{ $linha['funcao'] ?? data_get($linha, 'funcionario.funcao') ?? data_get($linha, 'funcionario.cargo') ?? 'Sem função' }}</td>
                                    <td class="text-end">R$ {{ number_format((float)($linha['custo'] ?? $linha['custo_total'] ?? 0),2,',','.') }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="3" class="text-center py-4">Sem dados</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
