@extends('default.layout',['title' => 'RH - DRE Preditivo'])
@section('content')
<style>
.rh-card{border:1px solid #e8edf5;border-radius:16px;box-shadow:0 8px 24px rgba(15,23,42,.04)}
.rh-kpi{border:1px solid #e8edf5;border-radius:14px;background:#fff}
.rh-kpi .card-body{padding:1rem}
.rh-kpi .label{font-size:.78rem;text-transform:uppercase;font-weight:700;color:#64748b}
.rh-kpi .value{font-size:1.35rem;font-weight:800;color:#0f172a}
.alerta{border-left:4px solid #dc2626;background:#fef2f2;padding:.85rem 1rem;border-radius:10px}
</style>

<div class="page-content">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h5 class="mb-0">DRE Preditivo</h5>
            <small class="text-muted">Projeção do próximo mês com base nas últimas competências e simulador de contratação.</small>
        </div>
        <div class="d-flex gap-2">
            <a href="/rh/dre-inteligente" class="btn btn-dark">DRE Inteligente</a>
            <a href="/rh/folha" class="btn btn-secondary">Voltar para Folha</a>
        </div>
    </div>

    <form method="GET" action="/rh/dre-preditivo">
        <div class="card rh-card mb-3">
            <div class="card-body p-4">
                <div class="row g-3 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label">Mês base</label>
                        <input type="number" class="form-control" name="mes" min="1" max="12" value="{{ $mes }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Ano base</label>
                        <input type="number" class="form-control" name="ano" value="{{ $ano }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Novas contratações</label>
                        <input type="number" class="form-control" name="sim_contratacoes" value="{{ $simContratacoes }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Salário médio</label>
                        <input type="number" step="0.01" class="form-control" name="sim_salario" value="{{ $simSalario }}">
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary w-100">Projetar</button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <div class="row g-3 mb-3">
        <div class="col-lg-3"><div class="card rh-kpi"><div class="card-body"><div class="label">Receita projetada</div><div class="value">R$ {{ number_format((float)$receitaProjetada,2,',','.') }}</div></div></div></div>
        <div class="col-lg-3"><div class="card rh-kpi"><div class="card-body"><div class="label">RH projetado</div><div class="value">R$ {{ number_format((float)$rhProjetado,2,',','.') }}</div></div></div></div>
        <div class="col-lg-3"><div class="card rh-kpi"><div class="card-body"><div class="label">Resultado projetado</div><div class="value">R$ {{ number_format((float)$resultadoProjetado,2,',','.') }}</div></div></div></div>
        <div class="col-lg-3"><div class="card rh-kpi"><div class="card-body"><div class="label">Margem projetada</div><div class="value">{{ number_format((float)$margemProjetada,2,',','.') }}%</div></div></div></div>
    </div>

    @if(!empty($alertas))
    <div class="card rh-card mb-3">
        <div class="card-body">
            <h6 class="mb-3">Alertas Preditivos</h6>
            <div class="d-grid gap-2">
                @foreach($alertas as $alerta)
                    <div class="alerta">{{ $alerta }}</div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <div class="row g-3 mb-3">
        <div class="col-lg-6">
            <div class="card rh-card">
                <div class="card-header bg-transparent"><h6 class="mb-0">Histórico usado na projeção</h6></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr><th>Competência</th><th>Receita</th><th>Desp. Op.</th><th>RH</th><th>Resultado</th></tr>
                            </thead>
                            <tbody>
                                @foreach($historico as $linha)
                                <tr>
                                    <td>{{ str_pad($linha['mes'],2,'0',STR_PAD_LEFT) }}/{{ $linha['ano'] }}</td>
                                    <td>R$ {{ number_format((float)$linha['receita'],2,',','.') }}</td>
                                    <td>R$ {{ number_format((float)$linha['despesas_operacionais'],2,',','.') }}</td>
                                    <td>R$ {{ number_format((float)$linha['total_rh'],2,',','.') }}</td>
                                    <td>R$ {{ number_format((float)$linha['resultado'],2,',','.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card rh-card">
                <div class="card-header bg-transparent"><h6 class="mb-0">Simulador de contratação</h6></div>
                <div class="card-body">
                    <p class="mb-2">Colaboradores ativos atuais: <strong>{{ $funcionariosAtivos ?? 0 }}</strong></p>
                    <p class="mb-2">Impacto total das novas contratações: <strong>R$ {{ number_format((float)$simImpactoTotal,2,',','.') }}</strong></p>
                    <p class="mb-2">Resultado simulado: <strong>R$ {{ number_format((float)$resultadoSimulado,2,',','.') }}</strong></p>
                    <p class="mb-0">Margem simulada: <strong>{{ number_format((float)$margemSimulada,2,',','.') }}%</strong></p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
