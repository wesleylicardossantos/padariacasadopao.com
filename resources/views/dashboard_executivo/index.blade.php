@extends('default.layout',['title' => 'Dashboard Executivo'])
@section('content')
<style>
.rh-card{border:1px solid #e8edf5;border-radius:16px;box-shadow:0 8px 24px rgba(15,23,42,.04)}
.rh-kpi{border:1px solid #e8edf5;border-radius:14px;background:#fff}
.rh-kpi .card-body{padding:1rem}
.rh-kpi .label{font-size:.78rem;text-transform:uppercase;font-weight:700;color:#64748b}
.rh-kpi .value{font-size:1.35rem;font-weight:800;color:#0f172a}
.alerta{border-left:4px solid #dc2626;background:#fef2f2;padding:.85rem 1rem;border-radius:10px}
.chart-box{height:320px}
</style>

<div class="page-content">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h5 class="mb-0">Dashboard Executivo</h5>
            <small class="text-muted">Painel executivo com indicadores, tendências e ranking de custos.</small>
        </div>
        <div class="d-flex gap-2">
            <a href="/rh/dre-inteligente" class="btn btn-dark">DRE Inteligente</a>
            <a href="/rh/dre-preditivo" class="btn btn-secondary">DRE Preditivo</a>
        </div>
    </div>

    <form method="GET" action="/rh/dashboard-executivo">
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
                        <button class="btn btn-primary w-100">Atualizar</button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <div class="row g-3 mb-3">
        <div class="col-lg-3"><div class="card rh-kpi"><div class="card-body"><div class="label">Receita atual</div><div class="value">R$ {{ number_format((float)$receitaAtual,2,',','.') }}</div></div></div></div>
        <div class="col-lg-3"><div class="card rh-kpi"><div class="card-body"><div class="label">RH atual</div><div class="value">R$ {{ number_format((float)$rhAtual,2,',','.') }}</div></div></div></div>
        <div class="col-lg-3"><div class="card rh-kpi"><div class="card-body"><div class="label">Lucro atual</div><div class="value">R$ {{ number_format((float)$lucroAtual,2,',','.') }}</div></div></div></div>
        <div class="col-lg-3"><div class="card rh-kpi"><div class="card-body"><div class="label">% Folha/Faturamento</div><div class="value">{{ number_format((float)$pesoFolhaAtual,2,',','.') }}%</div></div></div></div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-lg-6"><div class="card rh-kpi"><div class="card-body"><div class="label">Margem atual</div><div class="value">{{ number_format((float)$margemAtual,2,',','.') }}%</div></div></div></div>
        <div class="col-lg-6"><div class="card rh-kpi"><div class="card-body"><div class="label">Colaboradores ativos</div><div class="value">{{ $funcionariosAtivos ?? 0 }}</div></div></div></div>
    </div>


    @php($dossieStats = $dossieStats ?? [])
    <div class="row g-3 mb-3">
        <div class="col-lg-3"><div class="card rh-kpi"><div class="card-body"><div class="label">Dossiês ativos/total</div><div class="value">{{ (($dossieStats['total_dossies'] ?? 0) - ($dossieStats['dossies_arquivados'] ?? 0)) }}/{{ $dossieStats['total_dossies'] ?? 0 }}</div></div></div></div>
        <div class="col-lg-3"><div class="card rh-kpi"><div class="card-body"><div class="label">Docs vencidos</div><div class="value">{{ $dossieStats['documentos_vencidos'] ?? 0 }}</div></div></div></div>
        <div class="col-lg-3"><div class="card rh-kpi"><div class="card-body"><div class="label">Docs no mês</div><div class="value">{{ $dossieStats['documentos_mes'] ?? 0 }}</div></div></div></div>
        <div class="col-lg-3"><div class="card rh-kpi"><div class="card-body"><div class="label">Eventos automáticos</div><div class="value">{{ $dossieStats['eventos_automaticos_mes'] ?? 0 }}</div></div></div></div>
    </div>
    @if(!empty($alertas))
    <div class="card rh-card mb-3">
        <div class="card-body">
            <h6 class="mb-3">Alertas Executivos</h6>
            <div class="d-grid gap-2">
                @foreach($alertas as $alerta)
                    <div class="alerta">{{ $alerta }}</div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <div class="row g-3 mb-3">
        <div class="col-lg-8">
            <div class="card rh-card">
                <div class="card-header bg-transparent"><h6 class="mb-0">Receita x RH x Lucro (6 meses)</h6></div>
                <div class="card-body">
                    <div class="chart-box">
                        <canvas id="chartLinha"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card rh-card">
                <div class="card-header bg-transparent"><h6 class="mb-0">% Folha no faturamento</h6></div>
                <div class="card-body">
                    <div class="chart-box">
                        <canvas id="chartPeso"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-lg-6">
            <div class="card rh-card">
                <div class="card-header bg-transparent"><h6 class="mb-0">Automação do dossiê (6 meses)</h6></div>
                <div class="card-body">
                    <div class="chart-box">
                        <canvas id="chartAutomacao"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card rh-card">
                <div class="card-header bg-transparent"><h6 class="mb-0">Categorias de documentos do dossiê</h6></div>
                <div class="card-body">
                    <div class="chart-box">
                        <canvas id="chartCategoriasDossie"></canvas>
                    </div>
                    <div class="mt-3 small text-muted">Funcionários com pendência documental: <strong>{{ $dossieStats['funcionarios_pendencia_documental'] ?? 0 }}</strong></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card rh-card">
                <div class="card-header bg-transparent"><h6 class="mb-0">Top 10 custos por funcionário</h6></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr><th>Funcionário</th><th>Função</th><th class="text-end">Custo</th></tr>
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
        <div class="col-lg-6">
            <div class="card rh-card">
                <div class="card-header bg-transparent"><h6 class="mb-0">Top custos por setor/função</h6></div>
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
                                    <td class="text-end">R$ {{ number_format((float)($linha['custo'] ?? $linha['custo_total'] ?? 0),2,',','.') }}</td>
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
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const timeline = @json($timeline);
const labels = timeline.map(x => x.competencia);
const receita = timeline.map(x => Number(x.receita));
const rh = timeline.map(x => Number(x.rh));
const lucro = timeline.map(x => Number(x.lucro));
const peso = timeline.map(x => Number(x.peso_folha));
const automacao = @json($dossieStats['timeline_automacao'] ?? []);
const categoriasDossie = @json($dossieStats['categorias_documento'] ?? []);
const autoLabels = automacao.map(x => x.competencia);
const autoEventos = automacao.map(x => Number(x.eventos));
const autoDocumentos = automacao.map(x => Number(x.documentos));
const autoAutomaticos = automacao.map(x => Number(x.automaticos));
const categoriaLabels = categoriasDossie.map(x => x.categoria);
const categoriaValores = categoriasDossie.map(x => Number(x.total));

new Chart(document.getElementById('chartLinha'), {
    type: 'line',
    data: {
        labels: labels,
        datasets: [
            { label: 'Receita', data: receita, tension: 0.35 },
            { label: 'RH', data: rh, tension: 0.35 },
            { label: 'Lucro', data: lucro, tension: 0.35 }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});

new Chart(document.getElementById('chartPeso'), {
    type: 'bar',
    data: {
        labels: labels,
        datasets: [
            { label: '% Folha/Faturamento', data: peso }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});

new Chart(document.getElementById('chartAutomacao'), {
    type: 'bar',
    data: {
        labels: autoLabels,
        datasets: [
            { label: 'Documentos', data: autoDocumentos },
            { label: 'Eventos', data: autoEventos },
            { label: 'Automáticos', data: autoAutomaticos }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});

new Chart(document.getElementById('chartCategoriasDossie'), {
    type: 'doughnut',
    data: {
        labels: categoriaLabels,
        datasets: [{ label: 'Documentos', data: categoriaValores }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});
</script>
@endsection
