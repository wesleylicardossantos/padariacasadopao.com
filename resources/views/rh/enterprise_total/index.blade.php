@extends('default.layout',['title' => 'RH - Enterprise Total'])
@section('content')
<style>
.rh-card{border:1px solid #e8edf5;border-radius:16px;box-shadow:0 8px 24px rgba(15,23,42,.04)}
.rh-kpi{border:1px solid #e8edf5;border-radius:14px;background:#fff}
.rh-kpi .card-body{padding:1rem}
.rh-kpi .label{font-size:.78rem;text-transform:uppercase;font-weight:700;color:#64748b}
.rh-kpi .value{font-size:1.35rem;font-weight:800;color:#0f172a}
.alerta{border-left:4px solid #94a3b8;border-radius:10px;padding:.9rem 1rem;background:#fff}
.alerta.ok{border-left-color:#16a34a;background:#f0fdf4}
.alerta.alerta{border-left-color:#f59e0b;background:#fff7ed}
.alerta.critico{border-left-color:#dc2626;background:#fef2f2}
.score{font-size:2.5rem;font-weight:900}
</style>
<div class="page-content">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h5 class="mb-0">Modo Enterprise Total</h5>
            <small class="text-muted">Score da empresa, alertas automáticos, risco de caixa e visão por setor.</small>
        </div>
        <div class="d-flex gap-2">
            <a href="/rh/ia-decisao" class="btn btn-dark">IA de Decisão</a>
            <a href="/rh/enterprise-alertas" class="btn btn-secondary">Alertas</a>
        </div>
    </div>

    <form method="GET" action="/rh/enterprise-total">
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
        <div class="col-lg-3"><div class="card rh-kpi"><div class="card-body"><div class="label">Score RH-Financeiro</div><div class="score">{{ $score }}/100</div></div></div></div>
        <div class="col-lg-3"><div class="card rh-kpi"><div class="card-body"><div class="label">Receita</div><div class="value">R$ {{ number_format((float)$receita,2,',','.') }}</div></div></div></div>
        <div class="col-lg-3"><div class="card rh-kpi"><div class="card-body"><div class="label">RH Total</div><div class="value">R$ {{ number_format((float)$rh,2,',','.') }}</div></div></div></div>
        <div class="col-lg-3"><div class="card rh-kpi"><div class="card-body"><div class="label">Resultado</div><div class="value">R$ {{ number_format((float)$resultado,2,',','.') }}</div></div></div></div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-lg-3"><div class="card rh-kpi"><div class="card-body"><div class="label">% Folha/Faturamento</div><div class="value">{{ number_format((float)$pesoFolha,2,',','.') }}%</div></div></div></div>
        <div class="col-lg-3"><div class="card rh-kpi"><div class="card-body"><div class="label">Margem</div><div class="value">{{ number_format((float)$margem,2,',','.') }}%</div></div></div></div>
        <div class="col-lg-3"><div class="card rh-kpi"><div class="card-body"><div class="label">Caixa Projetado</div><div class="value">R$ {{ number_format((float)$caixaProjetado,2,',','.') }}</div></div></div></div>
        <div class="col-lg-3"><div class="card rh-kpi"><div class="card-body"><div class="label">Risco de Caixa</div><div class="value">{{ strtoupper($riscoCaixa) }}</div></div></div></div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-lg-6"><div class="card rh-kpi"><div class="card-body"><div class="label">Receita por colaborador</div><div class="value">R$ {{ number_format((float)$receitaPorFuncionario,2,',','.') }}</div></div></div></div>
        <div class="col-lg-6"><div class="card rh-kpi"><div class="card-body"><div class="label">Custo por colaborador</div><div class="value">R$ {{ number_format((float)$custoPorFuncionario,2,',','.') }}</div></div></div></div>
    </div>

    <div class="card rh-card mb-3">
        <div class="card-header bg-transparent"><h6 class="mb-0">Alertas automáticos</h6></div>
        <div class="card-body">
            <div class="d-grid gap-2">
                @foreach($alertas as $alerta)
                    <div class="alerta {{ $alerta['nivel'] }}">{{ $alerta['texto'] }}</div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="card rh-card">
        <div class="card-header bg-transparent"><h6 class="mb-0">Top setores/funções por custo</h6></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr><th>Setor/Função</th><th class="text-end">Custo</th></tr>
                    </thead>
                    <tbody>
                        @forelse($setores as $linha)
                        <tr>
                            <td>{{ $linha['setor'] }}</td>
                            <td class="text-end">R$ {{ number_format((float)$linha['custo'],2,',','.') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="2" class="text-center py-4">Sem dados</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
