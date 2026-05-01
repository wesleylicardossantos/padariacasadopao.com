@extends('default.layout',['title' => 'RH V5 - Dashboard'])
@section('content')
@php
    $totalFuncionarios = (int) ($totalFuncionarios ?? 0);
    $ativos = (int) ($ativos ?? 0);
    $inativos = (int) ($inativos ?? 0);
    $folhaBase = (float) ($folhaBase ?? 0);
    $turnover = (float) ($turnover ?? 0);
    $admissoesMes = (int) ($admissoesMes ?? 0);
    $desligamentosMes = (int) ($desligamentosMes ?? 0);
    $faltasMes = (int) ($faltasMes ?? 0);
    $atestadosMes = (int) ($atestadosMes ?? 0);
    $atrasosMes = (int) ($atrasosMes ?? 0);
    $alertas = $alertas ?? [];
@endphp
<style>
.rh-kpi-card{border:1px solid #e8edf5;border-radius:16px;box-shadow:0 10px 28px rgba(15,23,42,.04)}
.rh-kpi-card .card-body{padding:1rem 1.1rem}
.rh-kpi-label{font-size:.78rem;color:#64748b;text-transform:uppercase;font-weight:700;letter-spacing:.04em}
.rh-kpi-value{font-size:1.5rem;font-weight:800;color:#0f172a;line-height:1.15;margin-top:.2rem}
.rh-kpi-sub{font-size:.78rem;color:#94a3b8}
.rh-hero{background:linear-gradient(135deg,#1d4ed8,#6d28d9);border-radius:18px;color:#fff;box-shadow:0 18px 40px rgba(37,99,235,.18)}
.rh-hero .btn{border-radius:10px}
.rh-panel{border:1px solid #e8edf5;border-radius:16px;box-shadow:0 8px 24px rgba(15,23,42,.04)}
.rh-panel .card-header{background:#fff;border-bottom:1px solid #eef2f7;border-radius:16px 16px 0 0}
.rh-chip{display:inline-flex;align-items:center;padding:.28rem .55rem;border-radius:999px;font-size:.75rem;font-weight:700}
.rh-chip-primary{background:#eff6ff;color:#1d4ed8}
.rh-chip-warning{background:#fff7ed;color:#c2410c}
.rh-chip-danger{background:#fef2f2;color:#b91c1c}
.rh-chip-success{background:#ecfdf3;color:#047857}
.rh-grid-actions a{border-radius:14px;padding:1rem;text-decoration:none;display:block;border:1px solid #e9edf5;background:#fff;box-shadow:0 6px 20px rgba(15,23,42,.03);height:100%}
.rh-grid-actions a:hover{transform:translateY(-1px)}
.rh-grid-actions .title{font-weight:700;color:#0f172a}
.rh-grid-actions .desc{font-size:.82rem;color:#64748b}
</style>

<div class="page-content">
    <div class="card rh-hero border-0 mb-3">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div>
                    <h4 class="mb-1 text-white">RH V5 - Painel Executivo</h4>
                    <div class="text-white-50">Visão estratégica de pessoas, férias, movimentações, desligamentos e absenteísmo.</div>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="/funcionarios" class="btn btn-light text-dark">Funcionários</a>
                    <a href="/rh/ferias" class="btn btn-outline-light">Férias</a>
                    <a href="/rh/faltas" class="btn btn-outline-light">Absenteísmo</a>
                    <a href="/rh/desligamentos" class="btn btn-outline-light">Desligamentos</a>
                    <a href="/rh/alertas" class="btn btn-outline-light">Alertas</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-xl-3 col-md-6"><div class="card rh-kpi-card"><div class="card-body"><div class="rh-kpi-label">Funcionários</div><div class="rh-kpi-value">{{ $totalFuncionarios }}</div><div class="rh-kpi-sub">Base total cadastrada</div></div></div></div>
        <div class="col-xl-3 col-md-6"><div class="card rh-kpi-card"><div class="card-body"><div class="rh-kpi-label">Ativos / Inativos</div><div class="rh-kpi-value">{{ $ativos }} / {{ $inativos }}</div><div class="rh-kpi-sub">Situação atual da equipe</div></div></div></div>
        <div class="col-xl-3 col-md-6"><div class="card rh-kpi-card"><div class="card-body"><div class="rh-kpi-label">Folha Base</div><div class="rh-kpi-value">R$ {{ number_format((float)$folhaBase,2,',','.') }}</div><div class="rh-kpi-sub">Soma salarial atual</div></div></div></div>
        <div class="col-xl-3 col-md-6"><div class="card rh-kpi-card"><div class="card-body"><div class="rh-kpi-label">Turnover</div><div class="rh-kpi-value">{{ number_format((float)$turnover,2,',','.') }}%</div><div class="rh-kpi-sub">Admissões + desligamentos / ativos</div></div></div></div>

        <div class="col-xl-3 col-md-6"><div class="card rh-kpi-card"><div class="card-body"><div class="rh-kpi-label">Admissões no mês</div><div class="rh-kpi-value">{{ $admissoesMes }}</div><div class="rh-kpi-sub">Entradas registradas</div></div></div></div>
        <div class="col-xl-3 col-md-6"><div class="card rh-kpi-card"><div class="card-body"><div class="rh-kpi-label">Desligamentos no mês</div><div class="rh-kpi-value">{{ $desligamentosMes }}</div><div class="rh-kpi-sub">Saídas registradas</div></div></div></div>
        <div class="col-xl-3 col-md-6"><div class="card rh-kpi-card"><div class="card-body"><div class="rh-kpi-label">Faltas / Atrasos</div><div class="rh-kpi-value">{{ $faltasMes }} / {{ $atrasosMes }}</div><div class="rh-kpi-sub">Absenteísmo do mês</div></div></div></div>
        <div class="col-xl-3 col-md-6"><div class="card rh-kpi-card"><div class="card-body"><div class="rh-kpi-label">Atestados</div><div class="rh-kpi-value">{{ $atestadosMes }}</div><div class="rh-kpi-sub">Ocorrências médicas do mês</div></div></div></div>
    </div>

    <div class="row g-3 mt-1 rh-grid-actions">
        <div class="col-xl-2 col-md-4 col-6"><a href="/funcionarios"><div class="title">Funcionários</div><div class="desc">Cadastro principal e gestão da equipe</div></a></div>
        <div class="col-xl-2 col-md-4 col-6"><a href="/rh/salarios"><div class="title">Salários</div><div class="desc">Reajustes e custo da folha</div></a></div>
        <div class="col-xl-2 col-md-4 col-6"><a href="/rh/movimentacoes"><div class="title">Movimentações</div><div class="desc">Promoções, cargos e histórico</div></a></div>
        <div class="col-xl-2 col-md-4 col-6"><a href="/rh/ferias"><div class="title">Férias</div><div class="desc">Programações e períodos</div></a></div>
        <div class="col-xl-2 col-md-4 col-6"><a href="/rh/faltas"><div class="title">Absenteísmo</div><div class="desc">Faltas, atrasos e atestados</div></a></div>
        <div class="col-xl-2 col-md-4 col-6"><a href="/rh/alertas"><div class="title">Alertas</div><div class="desc">CNH, ASO e férias próximas</div></a></div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-lg-7">
            <div class="card rh-panel">
                <div class="card-header p-3"><h6 class="mb-0">Admissões x Desligamentos no Ano</h6></div>
                <div class="card-body">
                    <canvas id="rh-chart-flow" height="120"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card rh-panel h-100">
                <div class="card-header p-3"><h6 class="mb-0">Alertas Prioritários</h6></div>
                <div class="card-body">
                    @forelse($alertas as $item)
                    <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                        <div>
                            <div class="fw-bold">{{ $item['funcionario'] }}</div>
                            <div class="small text-muted">{{ $item['tipo'] }} — {{ $item['descricao'] }}</div>
                        </div>
                        <span class="rh-chip @if($item['gravidade']=='danger') rh-chip-danger @elseif($item['gravidade']=='warning') rh-chip-warning @elseif($item['gravidade']=='primary') rh-chip-primary @else rh-chip-success @endif">
                            {{ $item['dias'] }} dias
                        </span>
                    </div>
                    @empty
                    <div class="text-center text-muted py-4">Nenhum alerta crítico encontrado.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card rh-panel">
                <div class="card-header p-3"><h6 class="mb-0">Férias Próximas</h6></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead><tr><th>Funcionário</th><th>Início</th><th>Fim</th><th>Status</th></tr></thead>
                            <tbody>
                                @forelse($feriasProximas as $item)
                                <tr>
                                    <td>{{ optional($item->funcionario)->nome }}</td>
                                    <td>{{ \Carbon\Carbon::parse($item->data_inicio)->format('d/m/Y') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($item->data_fim)->format('d/m/Y') }}</td>
                                    <td>
                                        @php $st = strtolower($item->status); @endphp
                                        <span class="rh-chip @if($st=='programada') rh-chip-primary @elseif($st=='pendente') rh-chip-warning @elseif($st=='gozo') rh-chip-success @else rh-chip-danger @endif">{{ ucfirst($item->status) }}</span>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="text-center text-muted py-4">Nenhuma programação próxima.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card rh-panel">
                <div class="card-header p-3"><h6 class="mb-0">Últimas Movimentações</h6></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead><tr><th>Data</th><th>Funcionário</th><th>Tipo</th><th>Descrição</th></tr></thead>
                            <tbody>
                                @forelse($movimentacoes as $item)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($item->data_movimentacao)->format('d/m/Y') }}</td>
                                    <td>{{ optional($item->funcionario)->nome }}</td>
                                    <td>{{ \App\Models\RHMovimentacao::tipos()[$item->tipo] ?? ucfirst($item->tipo) }}</td>
                                    <td>{{ $item->descricao }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="text-center text-muted py-4">Nenhuma movimentação registrada.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="/assets/js/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function(){
    const ctx = document.getElementById('rh-chart-flow');
    if(!ctx) return;
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: @json($graficoMeses),
            datasets: [
                {
                    label: 'Admissões',
                    data: @json($graficoAdmissoes),
                    borderWidth: 1
                },
                {
                    label: 'Desligamentos',
                    data: @json($graficoDesligamentos),
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
})();
</script>
@endsection