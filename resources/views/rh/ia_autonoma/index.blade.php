@extends('default.layout',['title' => 'RH - IA Autônoma'])
@section('content')
<style>
.rh-card{border:1px solid #e8edf5;border-radius:16px;box-shadow:0 8px 24px rgba(15,23,42,.04)}
.rh-kpi{border:1px solid #e8edf5;border-radius:14px;background:#fff}
.rh-kpi .card-body{padding:1rem}
.rh-kpi .label{font-size:.78rem;text-transform:uppercase;font-weight:700;color:#64748b}
.rh-kpi .value{font-size:1.35rem;font-weight:800;color:#0f172a}
.acao{border-left:4px solid #94a3b8;border-radius:10px;padding:.9rem 1rem;background:#fff}
.acao.positivo{border-left-color:#16a34a;background:#f0fdf4}
.acao.alerta{border-left-color:#f59e0b;background:#fff7ed}
.acao.critico{border-left-color:#dc2626;background:#fef2f2}
</style>

<div class="page-content">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h5 class="mb-0">IA Autônoma RH</h5>
            <small class="text-muted">Camada de decisão automática com recomendações executivas e simulação de impacto.</small>
        </div>
        <div class="d-flex gap-2">
            <a href="/rh/ia-decisao" class="btn btn-dark">IA de Decisão</a>
            <a href="/rh/enterprise-total" class="btn btn-secondary">Enterprise Total</a>
        </div>
    </div>

    <form method="GET" action="/rh/ia-autonoma">
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
                        <label class="form-label">Qtd. contratação</label>
                        <input type="number" class="form-control" name="sim_qtd" value="{{ $simQtd }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Salário médio</label>
                        <input type="number" step="0.01" class="form-control" name="sim_salario" value="{{ $simSalario }}">
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary w-100">Rodar IA</button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <div class="row g-3 mb-3">
        <div class="col-lg-3"><div class="card rh-kpi"><div class="card-body"><div class="label">Receita</div><div class="value">R$ {{ number_format((float)$receita,2,',','.') }}</div></div></div></div>
        <div class="col-lg-3"><div class="card rh-kpi"><div class="card-body"><div class="label">Despesas</div><div class="value">R$ {{ number_format((float)$despesas,2,',','.') }}</div></div></div></div>
        <div class="col-lg-3"><div class="card rh-kpi"><div class="card-body"><div class="label">RH total</div><div class="value">R$ {{ number_format((float)$rh,2,',','.') }}</div></div></div></div>
        <div class="col-lg-3"><div class="card rh-kpi"><div class="card-body"><div class="label">Resultado</div><div class="value">R$ {{ number_format((float)$resultado,2,',','.') }}</div></div></div></div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-lg-3"><div class="card rh-kpi"><div class="card-body"><div class="label">% Folha/Faturamento</div><div class="value">{{ number_format((float)$pesoFolha,2,',','.') }}%</div></div></div></div>
        <div class="col-lg-3"><div class="card rh-kpi"><div class="card-body"><div class="label">Margem</div><div class="value">{{ number_format((float)$margem,2,',','.') }}%</div></div></div></div>
        <div class="col-lg-3"><div class="card rh-kpi"><div class="card-body"><div class="label">Receita por colaborador</div><div class="value">R$ {{ number_format((float)$receitaPorFuncionario,2,',','.') }}</div></div></div></div>
        <div class="col-lg-3"><div class="card rh-kpi"><div class="card-body"><div class="label">Custo por colaborador</div><div class="value">R$ {{ number_format((float)$custoPorFuncionario,2,',','.') }}</div></div></div></div>
    </div>

    <div class="card rh-card mb-3">
        <div class="card-header bg-transparent"><h6 class="mb-0">Ações recomendadas pela IA</h6></div>
        <div class="card-body">
            <div class="d-grid gap-2">
                @foreach($acoes as $acao)
                    <div class="acao {{ $acao['nivel'] }}">
                        <strong>{{ $acao['titulo'] }}</strong><br>
                        <span><strong>Ação:</strong> {{ $acao['acao'] }}</span><br>
                        <span><strong>Motivo:</strong> {{ $acao['motivo'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="card rh-card">
        <div class="card-header bg-transparent"><h6 class="mb-0">Simulação autônoma</h6></div>
        <div class="card-body">
            <p class="mb-2">Impacto total da simulação: <strong>R$ {{ number_format((float)$impacto,2,',','.') }}</strong></p>
            <p class="mb-2">Resultado simulado: <strong>R$ {{ number_format((float)$resultadoSimulado,2,',','.') }}</strong></p>
            <p class="mb-2">Margem simulada: <strong>{{ number_format((float)$margemSimulada,2,',','.') }}%</strong></p>
            @if($parecer)
                <div class="acao {{ ($resultadoSimulado < 0 ? 'critico' : ($margemSimulada < 5 ? 'alerta' : 'positivo')) }}">
                    {{ $parecer }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
