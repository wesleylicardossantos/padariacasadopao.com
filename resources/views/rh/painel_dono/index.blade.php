@extends('default.layout',['title' => 'RH - Painel do Dono'])
@section('content')
@php
    $mes = (int) ($mes ?? date('m'));
    $ano = (int) ($ano ?? date('Y'));
    $scoreSaude = (float) ($scoreSaude ?? 0);
    $statusSaude = $statusSaude ?? 'atencao';
    $decisao = $decisao ?? ['titulo' => 'Sem decisão', 'descricao' => ''];
    $placar = $placar ?? [];
    $insights = $insights ?? [];
    $prioridades = $prioridades ?? [];
    $recomendacoes = $recomendacoes ?? [];
    $anomalias = $anomalias ?? [];
    $forecast = $forecast ?? ['horizonte' => [], 'resumo' => []];
    $serie = $serie ?? [];
    $drivers = $drivers ?? [];
    $cenarios = $cenarios ?? [];
    $radar = $radar ?? [];
    $mapaRisco = $mapaRisco ?? [];
    $topPagar = $topPagar ?? [];
    $topReceber = $topReceber ?? [];
    $dre = $dre ?? [];
@endphp
<style>
.owner-page{background:#f6f9fc;padding:18px;border-radius:24px}
.owner-card{background:#fff;border:1px solid #e6edf7;border-radius:20px;box-shadow:0 10px 30px rgba(15,23,42,.05)}
.owner-hero{background:linear-gradient(135deg,#0f172a 0%,#1d4ed8 100%);color:#fff;border:none}
.owner-kpi .label{font-size:.74rem;text-transform:uppercase;letter-spacing:.04em;color:#6b7a90;font-weight:700}
.owner-kpi .value{font-size:1.55rem;font-weight:800;color:#0f172a;line-height:1.1}
.owner-kpi .hint{font-size:.82rem;color:#6b7a90}
.owner-pill{display:inline-flex;align-items:center;padding:.38rem .75rem;border-radius:999px;font-size:.78rem;font-weight:700}
.owner-pill.saudavel{background:#ecfdf3;color:#067647}.owner-pill.atencao{background:#fff7e8;color:#b54708}.owner-pill.critico{background:#fef3f2;color:#b42318}
.priority{border:1px solid #e6edf7;border-radius:16px;padding:1rem;background:#fff;height:100%}
.priority .impacto{font-size:.74rem;text-transform:uppercase;color:#6b7a90;font-weight:700}
.risk-line{display:flex;justify-content:space-between;gap:12px;align-items:center;margin-bottom:14px}.risk-track{flex:1;height:10px;background:#eef4fb;border-radius:999px;overflow:hidden}.risk-track span{display:block;height:100%;background:#2563eb;border-radius:999px}
.soft-table thead th{font-size:.78rem;text-transform:uppercase;color:#6b7a90;border-bottom-color:#e6edf7}.soft-table td,.soft-table th{padding:.85rem 1rem;vertical-align:middle}
.chart-box{position:relative;height:320px}
.mini-chart{position:relative;height:240px}
</style>

<div class="page-content owner-page">
    <div class="card owner-card owner-hero mb-3">
        <div class="card-body p-4 p-lg-5 d-flex justify-content-between flex-wrap gap-3 align-items-center">
            <div>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="owner-pill {{ $statusSaude }}">{{ strtoupper($statusSaude) }}</span>
                    <span class="owner-pill" style="background:rgba(255,255,255,.12);color:#fff">Score {{ number_format((float)$scoreSaude,0,',','.') }}/100</span>
                </div>
                <h3 class="mb-1">{{ $decisao['titulo'] }}</h3>
                <div style="max-width:860px;opacity:.9">{{ $decisao['descricao'] }}</div>
            </div>
            <div class="text-end">
                <div class="small text-white-50">Competência</div>
                <div class="fs-5 fw-bold">{{ str_pad($mes,2,'0',STR_PAD_LEFT) }}/{{ $ano }}</div>
                <a href="/rh/ia-decisao?mes={{ $mes }}&ano={{ $ano }}" class="btn btn-light btn-sm mt-2">Abrir IA avançada</a>
            </div>
        </div>
    </div>

    <form method="GET" action="/rh/painel-dono" class="card owner-card mb-3">
        <div class="card-body p-4">
            <div class="row g-3 align-items-end">
                <div class="col-md-2"><label class="form-label">Mês</label><input type="number" class="form-control" name="mes" min="1" max="12" value="{{ $mes }}"></div>
                <div class="col-md-2"><label class="form-label">Ano</label><input type="number" class="form-control" name="ano" value="{{ $ano }}"></div>
                <div class="col-md-3"><button class="btn btn-primary w-100">Atualizar painel</button></div>
                <div class="col-md-5 text-md-end d-flex gap-2 justify-content-md-end flex-wrap">
                    <a href="/rh/dashboard-executivo?mes={{ $mes }}&ano={{ $ano }}" class="btn btn-outline-primary">Dashboard executivo</a>
                    <a href="/rh/folha/resumo-financeiro?mes={{ $mes }}&ano={{ $ano }}" class="btn btn-outline-secondary">Resumo RH + Financeiro</a>
                </div>
            </div>
        </div>
    </form>

    <div class="row g-3 mb-3">
        <div class="col-lg-3 col-md-6"><div class="card owner-card owner-kpi"><div class="card-body"><div class="label">Receita prevista</div><div class="value">R$ {{ number_format((float)$placar['receita'],2,',','.') }}</div><div class="hint">Recebido: R$ {{ number_format((float)$placar['recebido'],2,',','.') }}</div></div></div></div>
        <div class="col-lg-3 col-md-6"><div class="card owner-card owner-kpi"><div class="card-body"><div class="label">Resultado operacional</div><div class="value">R$ {{ number_format((float)$placar['lucro'],2,',','.') }}</div><div class="hint">Margem: {{ number_format((float)$placar['margem'],2,',','.') }}%</div></div></div></div>
        <div class="col-lg-3 col-md-6"><div class="card owner-card owner-kpi"><div class="card-body"><div class="label">Custo total RH</div><div class="value">R$ {{ number_format((float)$placar['rh'],2,',','.') }}</div><div class="hint">Folha líquida: R$ {{ number_format((float)$placar['folha_liquida'],2,',','.') }}</div></div></div></div>
        <div class="col-lg-3 col-md-6"><div class="card owner-card owner-kpi"><div class="card-body"><div class="label">Caixa do período</div><div class="value">R$ {{ number_format((float)$placar['caixa'],2,',','.') }}</div><div class="hint">Execução despesa: {{ number_format((float)$placar['execucao_despesa'],2,',','.') }}%</div></div></div></div>
    </div>

    <div class="row g-3 mb-3">
        @foreach($insights as $item)
        <div class="col-lg-3 col-md-6">
            <div class="card owner-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                        <div class="fw-bold">{{ $item['titulo'] }}</div>
                        <span class="owner-pill {{ $item['status'] }}">{{ strtoupper($item['status']) }}</span>
                    </div>
                    <div class="display-6 fw-bold mb-2">{{ number_format((float)$item['valor'],2,',','.') }}{{ $item['sufixo'] }}</div>
                    <div class="text-muted small">{{ $item['descricao'] }}</div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="row g-3 mb-3">
        <div class="col-lg-7">
            <div class="card owner-card h-100">
                <div class="card-header bg-transparent border-0 pt-4 px-4"><h5 class="mb-0">Placar do dono</h5><small class="text-muted">Visão resumida de operação, caixa e people cost.</small></div>
                <div class="card-body pt-0 px-4 pb-4">
                    <div class="row g-3">
                        <div class="col-md-6"><div class="priority"><div class="impacto">Conversão de receita</div><div class="h4 mb-1">{{ number_format((float)$placar['conversao_receita'],2,',','.') }}%</div><div class="text-muted small">Quanto da receita prevista realmente virou caixa.</div></div></div>
                        <div class="col-md-6"><div class="priority"><div class="impacto">Despesa prevista x paga</div><div class="h4 mb-1">{{ number_format((float)$placar['execucao_despesa'],2,',','.') }}%</div><div class="text-muted small">Ajuda a medir disciplina na execução financeira.</div></div></div>
                        <div class="col-md-6"><div class="priority"><div class="impacto">Top decisão</div><div class="h5 mb-1">{{ $decisao['titulo'] }}</div><div class="text-muted small">{{ $decisao['descricao'] }}</div></div></div>
                        <div class="col-md-6"><div class="priority"><div class="impacto">DRE do mês</div><div class="h4 mb-1">R$ {{ number_format((float)$dre['resultadoOperacional'],2,',','.') }}</div><div class="text-muted small">Margem operacional de {{ number_format((float)$dre['margemOperacional'],2,',','.') }}%.</div></div></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card owner-card h-100">
                <div class="card-header bg-transparent border-0 pt-4 px-4"><h5 class="mb-0">Mapa de risco</h5><small class="text-muted">Onde o negócio está mais pressionado hoje.</small></div>
                <div class="card-body pt-0 px-4 pb-4">
                    @foreach($mapaRisco as $risco)
                    <div class="risk-line">
                        <div style="min-width:120px" class="fw-semibold">{{ $risco['nome'] }}</div>
                        <div class="risk-track"><span style="width:{{ min(100,max(0,$risco['valor'])) }}%"></span></div>
                        <div class="fw-bold" style="min-width:52px;text-align:right">{{ number_format((float)$risco['valor'],0,',','.') }}</div>
                    </div>
                    @endforeach
                    <hr>
                    <div class="small text-muted">Riscos acima de 70 pedem ação imediata. Entre 45 e 70 pedem plano tático.</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-lg-6">
            <div class="card owner-card h-100">
                <div class="card-header bg-transparent border-0 pt-4 px-4"><h5 class="mb-0">Prioridades do dono</h5></div>
                <div class="card-body pt-0 px-4 pb-4">
                    <div class="row g-3">
                        @forelse($prioridades as $prioridade)
                        <div class="col-md-6">
                            <div class="priority">
                                <div class="impacto">{{ strtoupper($prioridade['impacto']) }} · {{ $prioridade['prazo'] }}</div>
                                <div class="fw-bold mt-1 mb-1">{{ $prioridade['titulo'] }}</div>
                                <div class="text-muted small">{{ $prioridade['descricao'] }}</div>
                            </div>
                        </div>
                        @empty
                        <div class="col-12"><div class="text-muted">Nenhuma prioridade crítica no período.</div></div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card owner-card h-100">
                <div class="card-header bg-transparent border-0 pt-4 px-4"><h5 class="mb-0">Anomalias e desvios</h5></div>
                <div class="card-body pt-0 px-4 pb-4">
                    <div class="d-grid gap-2">
                        @forelse($anomalias as $anomalia)
                        <div class="priority">
                            <div class="d-flex justify-content-between gap-2 align-items-start">
                                <div>
                                    <div class="fw-bold">{{ $anomalia['titulo'] }}</div>
                                    <div class="text-muted small">{{ $anomalia['texto'] }}</div>
                                </div>
                                <span class="owner-pill {{ $anomalia['tipo'] == 'positivo' ? 'saudavel' : ($anomalia['tipo'] == 'alerta' ? 'atencao' : 'critico') }}">{{ strtoupper($anomalia['tipo']) }}</span>
                            </div>
                        </div>
                        @empty
                        <div class="text-muted">Sem desvios relevantes contra a série recente.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-lg-7">
            <div class="card owner-card">
                <div class="card-header bg-transparent border-0 pt-4 px-4"><h5 class="mb-0">Receita, RH e resultado</h5></div>
                <div class="card-body"><div class="chart-box"><canvas id="chartOwnerTimeline"></canvas></div></div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card owner-card">
                <div class="card-header bg-transparent border-0 pt-4 px-4"><h5 class="mb-0">Radar de gestão</h5></div>
                <div class="card-body"><div class="mini-chart"><canvas id="chartOwnerRadar"></canvas></div></div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-lg-6">
            <div class="card owner-card">
                <div class="card-header bg-transparent border-0 pt-4 px-4"><h5 class="mb-0">Forecast de 3 meses</h5></div>
                <div class="card-body pt-0 px-0 pb-2">
                    <div class="table-responsive">
                        <table class="table soft-table mb-0">
                            <thead><tr><th>Horizonte</th><th>Receita</th><th>RH</th><th>Resultado</th><th>Risco</th></tr></thead>
                            <tbody>
                                @foreach($forecast['horizonte'] as $item)
                                <tr>
                                    <td>{{ $item['label'] }}</td>
                                    <td>R$ {{ number_format((float)$item['receita'],2,',','.') }}</td>
                                    <td>R$ {{ number_format((float)$item['rh'],2,',','.') }}</td>
                                    <td>R$ {{ number_format((float)$item['resultado'],2,',','.') }}</td>
                                    <td><span class="owner-pill {{ $item['status'] == 'bom' ? 'saudavel' : $item['status'] }}">{{ number_format((float)$item['risco'],0,',','.') }}</span></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card owner-card">
                <div class="card-header bg-transparent border-0 pt-4 px-4"><h5 class="mb-0">Cenários de decisão</h5></div>
                <div class="card-body pt-0 px-4 pb-4">
                    <div class="d-grid gap-2">
                        @foreach($cenarios as $cenario)
                        <div class="priority">
                            <div class="d-flex justify-content-between gap-2 align-items-start">
                                <div>
                                    <div class="fw-bold">{{ $cenario['titulo'] }}</div>
                                    <div class="text-muted small">{{ $cenario['descricao'] }}</div>
                                </div>
                                <span class="owner-pill {{ $cenario['status'] == 'bom' ? 'saudavel' : $cenario['status'] }}">{{ strtoupper($cenario['status']) }}</span>
                            </div>
                            <div class="row g-2 mt-2 small">
                                <div class="col-4"><strong>Impacto</strong><br>R$ {{ number_format((float)$cenario['impacto'],2,',','.') }}</div>
                                <div class="col-4"><strong>Resultado</strong><br>R$ {{ number_format((float)$cenario['resultado'],2,',','.') }}</div>
                                <div class="col-4"><strong>Margem</strong><br>{{ number_format((float)$cenario['margem'],2,',','.') }}%</div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card owner-card">
                <div class="card-header bg-transparent border-0 pt-4 px-4"><h5 class="mb-0">Top categorias a pagar</h5></div>
                <div class="card-body pt-0 px-0 pb-2"><div class="table-responsive"><table class="table soft-table mb-0"><thead><tr><th>Categoria</th><th>Valor</th><th>Pago</th></tr></thead><tbody>@forelse($topPagar as $item)<tr><td>{{ $item['categoria'] }}</td><td>R$ {{ number_format((float)$item['valor'],2,',','.') }}</td><td>R$ {{ number_format((float)$item['pago'],2,',','.') }}</td></tr>@empty<tr><td colspan="3" class="text-center py-4 text-muted">Sem dados.</td></tr>@endforelse</tbody></table></div></div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card owner-card">
                <div class="card-header bg-transparent border-0 pt-4 px-4"><h5 class="mb-0">Top categorias a receber</h5></div>
                <div class="card-body pt-0 px-0 pb-2"><div class="table-responsive"><table class="table soft-table mb-0"><thead><tr><th>Categoria</th><th>Valor</th><th>Recebido</th></tr></thead><tbody>@forelse($topReceber as $item)<tr><td>{{ $item['categoria'] }}</td><td>R$ {{ number_format((float)$item['valor'],2,',','.') }}</td><td>R$ {{ number_format((float)$item['recebido'],2,',','.') }}</td></tr>@empty<tr><td colspan="3" class="text-center py-4 text-muted">Sem dados.</td></tr>@endforelse</tbody></table></div></div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ownerSerie = @json($serie);
const ownerLabels = ownerSerie.map(x => x.label);
const ownerReceita = ownerSerie.map(x => Number(x.receita_prevista));
const ownerRh = ownerSerie.map(x => Number(x.rh_total));
const ownerResultado = ownerSerie.map(x => Number(x.resultado_previsto));
const ownerRadar = @json($radar);

new Chart(document.getElementById('chartOwnerTimeline'), {
    type: 'line',
    data: {
        labels: ownerLabels,
        datasets: [
            { label: 'Receita', data: ownerReceita, tension: 0.35 },
            { label: 'RH', data: ownerRh, tension: 0.35 },
            { label: 'Resultado', data: ownerResultado, tension: 0.35 }
        ]
    },
    options: { responsive: true, maintainAspectRatio: false }
});

new Chart(document.getElementById('chartOwnerRadar'), {
    type: 'radar',
    data: {
        labels: ownerRadar.map(x => x.label),
        datasets: [{ label: 'Radar', data: ownerRadar.map(x => Number(x.valor)) }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: { r: { suggestedMin: 0, suggestedMax: 100 } }
    }
});
</script>
@endsection