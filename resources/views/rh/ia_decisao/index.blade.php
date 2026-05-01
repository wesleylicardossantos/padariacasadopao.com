@extends('default.layout',['title' => 'RH - IA de Decisão'])
@section('content')
@php
    $mes = (int) ($mes ?? date('m'));
    $ano = (int) ($ano ?? date('Y'));
    $scoreSaude = (float) ($scoreSaude ?? 0);
    $statusSaude = $statusSaude ?? 'atencao';
    $decisao = $decisao ?? ['titulo' => 'Sem decisão', 'descricao' => ''];
    $prioridades = $prioridades ?? [];
    $recomendacoes = $recomendacoes ?? [];
    $anomalias = $anomalias ?? [];
    $forecast = $forecast ?? ['horizonte' => [], 'resumo' => []];
    $drivers = $drivers ?? [];
    $cenarios = $cenarios ?? [];
    $radar = $radar ?? [];
@endphp
<style>
.ca-page{background:#f6f9fc;padding:18px;border-radius:24px}
.ca-card{background:#fff;border:1px solid #e6edf7;border-radius:20px;box-shadow:0 10px 30px rgba(15,23,42,.05)}
.ca-hero{background:linear-gradient(135deg,#0f172a 0%,#1d4ed8 100%);color:#fff;border:none}
.ca-kpi .label{font-size:.74rem;text-transform:uppercase;letter-spacing:.04em;color:#6b7a90;font-weight:700}
.ca-kpi .value{font-size:1.55rem;font-weight:800;color:#0f172a;line-height:1.1}
.ca-kpi .hint{font-size:.82rem;color:#6b7a90}
.ca-pill{display:inline-flex;align-items:center;padding:.38rem .75rem;border-radius:999px;font-size:.78rem;font-weight:700}
.ca-pill.saudavel,.status-bom{background:#ecfdf3;color:#067647}
.ca-pill.atencao,.status-atencao{background:#fff7e8;color:#b54708}
.ca-pill.critico,.status-critico{background:#fef3f2;color:#b42318}
.reco{border:1px solid #e6edf7;border-radius:16px;padding:1rem 1rem 1rem 1.1rem;background:#fff;position:relative;overflow:hidden}
.reco:before{content:"";position:absolute;left:0;top:0;bottom:0;width:5px;background:#94a3b8}
.reco.positivo:before{background:#22c55e}.reco.alerta:before{background:#f59e0b}.reco.critico:before{background:#ef4444}
.soft-table thead th{font-size:.78rem;text-transform:uppercase;color:#6b7a90;border-bottom-color:#e6edf7}
.soft-table td,.soft-table th{padding: .85rem 1rem;vertical-align:middle}
.mini-bar{height:8px;border-radius:999px;background:#eef4fb;overflow:hidden}.mini-bar>span{display:block;height:100%;border-radius:999px;background:#2563eb}
.driver{padding:1rem;border:1px solid #e6edf7;border-radius:18px;height:100%}
.driver small{color:#6b7a90}.chart-box{position:relative;height:300px}.small-chart{position:relative;height:240px}
</style>

<div class="page-content ca-page">
    <div class="card ca-card ca-hero mb-3">
        <div class="card-body p-4 p-lg-5 d-flex justify-content-between flex-wrap gap-3 align-items-center">
            <div>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="ca-pill {{ $statusSaude }}">{{ strtoupper($statusSaude) }}</span>
                    <span class="ca-pill" style="background:rgba(255,255,255,.12);color:#fff">Score {{ number_format((float)$scoreSaude,0,',','.') }}/100</span>
                </div>
                <h3 class="mb-1">{{ $decisao['titulo'] }}</h3>
                <div style="max-width:780px;opacity:.9">{{ $decisao['descricao'] }}</div>
            </div>
            <div class="text-end">
                <div class="small text-white-50">Competência</div>
                <div class="fs-5 fw-bold">{{ str_pad($mes,2,'0',STR_PAD_LEFT) }}/{{ $ano }}</div>
                <a href="/rh/painel-dono?mes={{ $mes }}&ano={{ $ano }}" class="btn btn-light btn-sm mt-2">Abrir painel do dono</a>
            </div>
        </div>
    </div>

    <form method="GET" action="/rh/ia-decisao" class="card ca-card mb-3">
        <div class="card-body p-4">
            <div class="row g-3 align-items-end">
                <div class="col-md-2"><label class="form-label">Mês</label><input type="number" class="form-control" name="mes" min="1" max="12" value="{{ $mes }}"></div>
                <div class="col-md-2"><label class="form-label">Ano</label><input type="number" class="form-control" name="ano" value="{{ $ano }}"></div>
                <div class="col-md-3"><button class="btn btn-primary w-100">Rodar análise avançada</button></div>
                <div class="col-md-5 text-md-end d-flex gap-2 justify-content-md-end flex-wrap">
                    <a href="/rh/folha/resumo-financeiro?mes={{ $mes }}&ano={{ $ano }}" class="btn btn-outline-primary">Resumo integrado RH + Financeiro</a>
                    <a href="/rh/painel-dono?mes={{ $mes }}&ano={{ $ano }}" class="btn btn-outline-secondary">Painel do dono</a>
                </div>
            </div>
        </div>
    </form>

    <div class="row g-3 mb-3">
        <div class="col-lg-3 col-md-6"><div class="card ca-card ca-kpi"><div class="card-body"><div class="label">Receita prevista</div><div class="value">R$ {{ number_format((float)$receita,2,',','.') }}</div><div class="hint">Recebido: R$ {{ number_format((float)$receitaRecebida,2,',','.') }}</div></div></div></div>
        <div class="col-lg-3 col-md-6"><div class="card ca-card ca-kpi"><div class="card-body"><div class="label">RH total</div><div class="value">R$ {{ number_format((float)$rh,2,',','.') }}</div><div class="hint">Peso sobre receita: {{ number_format((float)$pesoFolha,2,',','.') }}%</div></div></div></div>
        <div class="col-lg-3 col-md-6"><div class="card ca-card ca-kpi"><div class="card-body"><div class="label">Resultado previsto</div><div class="value">R$ {{ number_format((float)$resultado,2,',','.') }}</div><div class="hint">Resultado caixa: R$ {{ number_format((float)$resultadoCaixa,2,',','.') }}</div></div></div></div>
        <div class="col-lg-3 col-md-6"><div class="card ca-card ca-kpi"><div class="card-body"><div class="label">Capital comprometido</div><div class="value">{{ number_format((float)$capitalComprometido,2,',','.') }}%</div><div class="hint">Equipe ativa: {{ (int)$funcionariosAtivos }}</div></div></div></div>
    </div>

    <div class="row g-3 mb-3">
        @foreach($drivers as $driver)
        <div class="col-lg-3 col-md-6">
            <div class="card ca-card driver">
                <div class="d-flex justify-content-between align-items-start gap-3">
                    <div><div class="fw-bold">{{ $driver['titulo'] }}</div><small>{{ $driver['descricao'] }}</small></div>
                    <span class="ca-pill status-{{ $driver['status'] }}">{{ strtoupper($driver['status']) }}</span>
                </div>
                @php $meta=(float)($driver['meta'] ?? 0); $valor=(float)($driver['valor'] ?? 0); $percent=$meta>0?min(($valor/$meta)*100,100):0; @endphp
                <div class="mt-3 mb-2 d-flex justify-content-between"><strong>{{ number_format($valor,2,',','.') }}</strong><small>Meta {{ number_format($meta,2,',','.') }}</small></div>
                <div class="mini-bar"><span style="width:{{ $percent }}%"></span></div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="row g-3 mb-3">
        <div class="col-lg-7">
            <div class="card ca-card h-100">
                <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                    <div><h5 class="mb-0">Recomendações acionáveis</h5><small class="text-muted">Motor avançado com score, anomalias, previsões e simulações.</small></div>
                    <div class="text-end small text-muted">Tendências<br>Receita {{ number_format((float)$tendencias['receita'],2,',','.') }}% · RH {{ number_format((float)$tendencias['rh'],2,',','.') }}%</div>
                </div>
                <div class="card-body pt-0 px-4 pb-4"><div class="d-grid gap-2">@foreach($recomendacoes as $rec)<div class="reco {{ $rec['tipo'] }}"><div class="fw-bold mb-1">{{ $rec['titulo'] }}</div><div class="text-muted">{{ $rec['texto'] }}</div></div>@endforeach</div></div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card ca-card h-100">
                <div class="card-header bg-transparent border-0 pt-4 px-4"><h5 class="mb-0">Prioridades táticas</h5><small class="text-muted">O que fazer primeiro para melhorar resultado.</small></div>
                <div class="card-body pt-0 px-4 pb-4"><div class="d-grid gap-3">@foreach($prioridades as $item)<div class="driver"><div class="d-flex justify-content-between gap-2"><div><div class="fw-bold">{{ $item['titulo'] }}</div><small>{{ $item['descricao'] }}</small></div><span class="ca-pill {{ $item['impacto'] == 'alto' ? 'critico' : 'atencao' }}">{{ strtoupper($item['impacto']) }}</span></div><div class="small text-muted mt-2">Prazo: {{ $item['prazo'] }}</div></div>@endforeach</div></div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-lg-8"><div class="card ca-card"><div class="card-header bg-transparent border-0 pt-4 px-4"><h5 class="mb-0">Série histórica de decisão</h5></div><div class="card-body"><div class="chart-box"><canvas id="chartDecisionTimeline"></canvas></div></div></div></div>
        <div class="col-lg-4"><div class="card ca-card"><div class="card-header bg-transparent border-0 pt-4 px-4"><h5 class="mb-0">Radar de gestão</h5></div><div class="card-body"><div class="small-chart"><canvas id="chartDecisionRadar"></canvas></div></div></div></div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-lg-6">
            <div class="card ca-card">
                <div class="card-header bg-transparent border-0 pt-4 px-4"><h5 class="mb-0">Forecast de 3 meses</h5></div>
                <div class="card-body pt-0 px-0 pb-2"><div class="table-responsive"><table class="table soft-table mb-0"><thead><tr><th>Horizonte</th><th>Receita</th><th>RH</th><th>Resultado</th><th>Risco</th></tr></thead><tbody>@foreach($forecast['horizonte'] as $item)<tr><td>{{ $item['label'] }}</td><td>R$ {{ number_format((float)$item['receita'],2,',','.') }}</td><td>R$ {{ number_format((float)$item['rh'],2,',','.') }}</td><td>R$ {{ number_format((float)$item['resultado'],2,',','.') }}</td><td><span class="ca-pill {{ $item['status'] == 'bom' ? 'saudavel' : $item['status'] }}">{{ number_format((float)$item['risco'],0,',','.') }}</span></td></tr>@endforeach</tbody></table></div></div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card ca-card h-100">
                <div class="card-header bg-transparent border-0 pt-4 px-4"><h5 class="mb-0">Anomalias e desvios</h5></div>
                <div class="card-body pt-0 px-4 pb-4"><div class="d-grid gap-2">@forelse($anomalias as $anomalia)<div class="driver"><div class="d-flex justify-content-between gap-2 align-items-start"><div><div class="fw-bold">{{ $anomalia['titulo'] }}</div><small>{{ $anomalia['texto'] }}</small></div><span class="ca-pill {{ $anomalia['tipo'] == 'alerta' ? 'atencao' : 'critico' }}">{{ strtoupper($anomalia['tipo']) }}</span></div></div>@empty<div class="text-muted">Sem desvios relevantes contra a média recente.</div>@endforelse</div></div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-lg-6"><div class="card ca-card"><div class="card-header bg-transparent border-0 pt-4 px-4"><h5 class="mb-0">Maiores categorias a pagar</h5></div><div class="card-body pt-0 px-0 pb-2"><div class="table-responsive"><table class="table soft-table mb-0"><thead><tr><th>Categoria</th><th>Valor</th><th>Pago</th></tr></thead><tbody>@forelse($categoriasPagar as $item)<tr><td>{{ $item['categoria'] }}</td><td>R$ {{ number_format((float)$item['valor'],2,',','.') }}</td><td>R$ {{ number_format((float)$item['pago'],2,',','.') }}</td></tr>@empty<tr><td colspan="3" class="text-center py-4 text-muted">Sem dados de contas a pagar.</td></tr>@endforelse</tbody></table></div></div></div></div>
        <div class="col-lg-6"><div class="card ca-card"><div class="card-header bg-transparent border-0 pt-4 px-4"><h5 class="mb-0">Maiores categorias a receber</h5></div><div class="card-body pt-0 px-0 pb-2"><div class="table-responsive"><table class="table soft-table mb-0"><thead><tr><th>Categoria</th><th>Valor</th><th>Recebido</th></tr></thead><tbody>@forelse($categoriasReceber as $item)<tr><td>{{ $item['categoria'] }}</td><td>R$ {{ number_format((float)$item['valor'],2,',','.') }}</td><td>R$ {{ number_format((float)$item['recebido'],2,',','.') }}</td></tr>@empty<tr><td colspan="3" class="text-center py-4 text-muted">Sem dados de contas a receber.</td></tr>@endforelse</tbody></table></div></div></div></div>
    </div>

    <div class="row g-3">
        <div class="col-lg-12">
            <div class="card ca-card h-100">
                <div class="card-header bg-transparent border-0 pt-4 px-4"><h5 class="mb-0">Cenários simulados</h5><small class="text-muted">Expansão, reajuste, choque de receita e inadimplência.</small></div>
                <div class="card-body pt-0 px-4 pb-4"><div class="row g-3">@foreach($cenarios as $cenario)<div class="col-lg-4 col-md-6"><div class="driver"><div class="d-flex justify-content-between gap-3 align-items-start"><div><div class="fw-bold">{{ $cenario['titulo'] }}</div><small>{{ $cenario['descricao'] }}</small></div><span class="ca-pill {{ $cenario['status'] == 'bom' ? 'saudavel' : $cenario['status'] }}">{{ strtoupper($cenario['status']) }}</span></div><div class="row g-2 mt-2"><div class="col-4"><small>Impacto</small><div class="fw-bold">R$ {{ number_format((float)$cenario['impacto'],2,',','.') }}</div></div><div class="col-4"><small>Resultado</small><div class="fw-bold">R$ {{ number_format((float)$cenario['resultado'],2,',','.') }}</div></div><div class="col-4"><small>Margem</small><div class="fw-bold">{{ number_format((float)$cenario['margem'],2,',','.') }}%</div></div></div></div></div>@endforeach</div></div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const timeline = @json($serie);
const labels = timeline.map(x => x.label);
const receita = timeline.map(x => Number(x.receita_prevista));
const rh = timeline.map(x => Number(x.rh_total));
const resultado = timeline.map(x => Number(x.resultado_previsto));
const radar = @json($radar);

new Chart(document.getElementById('chartDecisionTimeline'), {
    type: 'line',
    data: { labels: labels, datasets: [
        { label: 'Receita', data: receita, tension: 0.35 },
        { label: 'RH', data: rh, tension: 0.35 },
        { label: 'Resultado', data: resultado, tension: 0.35 }
    ]},
    options: { responsive: true, maintainAspectRatio: false }
});

new Chart(document.getElementById('chartDecisionRadar'), {
    type: 'radar',
    data: { labels: radar.map(x => x.label), datasets: [{ label: 'Radar', data: radar.map(x => Number(x.valor)) }] },
    options: { responsive: true, maintainAspectRatio: false, scales: { r: { suggestedMin: 0, suggestedMax: 100 } } }
});
</script>
@endsection