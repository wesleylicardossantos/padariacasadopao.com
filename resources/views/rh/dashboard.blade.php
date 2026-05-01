@extends('default.layout',['title' => 'Dashboard RH Executivo'])
@section('content')
@php
    $totalFuncionarios = (int) ($totalFuncionarios ?? 0);
    $ativos = (int) ($ativos ?? 0);
    $inativos = (int) ($inativos ?? 0);
    $folhaMensal = (float) ($folhaMensal ?? $folhaBase ?? 0);
    $percentualAtivos = (float) ($percentualAtivos ?? 0);
    $custoMedioColaborador = (float) ($custoMedioColaborador ?? 0);
    $ticketMedioFolha = (float) ($ticketMedioFolha ?? 0);
    $admissoesMes = (int) ($admissoesMes ?? 0);
    $desligamentosMes = (int) ($desligamentosMes ?? 0);
    $faltasMes = (int) ($faltasMes ?? 0);
    $atrasosMes = (int) ($atrasosMes ?? 0);
    $atestadosMes = (int) ($atestadosMes ?? 0);
    $turnover = (float) ($turnover ?? 0);
    $apagamentosMes = (float) ($apagamentosMes ?? 0);
    $totalProventosMes = (float) ($totalProventosMes ?? 0);
    $totalDescontosMes = (float) ($totalDescontosMes ?? 0);
    $liquidoMes = (float) ($liquidoMes ?? 0);
    $ultimaCompetencia = $ultimaCompetencia ?? null;
    $competenciaLabel = $competenciaLabel ?? now()->format('m/Y');
    $competenciaStatus = strtolower((string) ($competenciaStatus ?? 'aberta'));
    $folhaProcessadaNoMes = (bool) ($folhaProcessadaNoMes ?? false);
    $alertasCount = (int) ($alertasCount ?? 0);
    $feriasPendentes = (int) ($feriasPendentes ?? 0);
    $movimentacoesMes = (int) ($movimentacoesMes ?? 0);
    $variacaoHeadcount = (int) ($variacaoHeadcount ?? 0);
    $saudeFolha = (float) ($saudeFolha ?? 0);
    $serieFolha = collect($serieFolha ?? []);
    $serieHeadcount = collect($serieHeadcount ?? []);
    $topSalarios = collect($topSalarios ?? []);
    $movimentacoesRecentes = collect($movimentacoesRecentes ?? []);
    $feriasProximas = collect($feriasProximas ?? []);
    $alertas = collect($alertas ?? []);

    $mesSelecionado = (int) ($mesSelecionado ?? now()->format('m'));
    $anoSelecionado = (int) ($anoSelecionado ?? now()->format('Y'));
    $absenteismoAnterior = $absenteismoAnterior ?? ['faltas' => 0, 'atrasos' => 0, 'atestados' => 0];
    $folhaAtualSerie = $serieFolha->last();
    $folhaAnteriorSerie = $serieFolha->count() > 1 ? $serieFolha->slice(-2, 1)->first() : null;
    $headcountAtualSerie = $serieHeadcount->last();
    $headcountAnteriorSerie = $serieHeadcount->count() > 1 ? $serieHeadcount->slice(-2, 1)->first() : null;

    $variacaoFolhaPercentual = $folhaAnteriorSerie ? round((((float) ($folhaAtualSerie['liquido'] ?? 0)) - ((float) ($folhaAnteriorSerie['liquido'] ?? 0))) / max(1, (float) ($folhaAnteriorSerie['liquido'] ?? 1)) * 100, 2) : 0.0;
    $variacaoHeadcountPainel = $headcountAnteriorSerie ? ((int) ($headcountAtualSerie['ativos'] ?? $ativos)) - ((int) ($headcountAnteriorSerie['ativos'] ?? 0)) : 0;
    $absenteismoAtualTotal = $faltasMes + $atrasosMes + $atestadosMes;
    $absenteismoAnteriorTotal = (int) ($absenteismoAnterior['faltas'] ?? 0) + (int) ($absenteismoAnterior['atrasos'] ?? 0) + (int) ($absenteismoAnterior['atestados'] ?? 0);
    $variacaoAbsenteismo = $absenteismoAtualTotal - $absenteismoAnteriorTotal;
    $movimentoLiquido = $admissoesMes - $desligamentosMes;
    $movimentoLiquidoAnterior = $headcountAnteriorSerie ? ((int) ($headcountAnteriorSerie['admissoes'] ?? 0) - (int) ($headcountAnteriorSerie['desligamentos'] ?? 0)) : 0;
    $variacaoMovimento = $movimentoLiquido - $movimentoLiquidoAnterior;
    $anosFiltro = range((int) now()->format('Y') - 3, (int) now()->format('Y') + 1);

    $statusLabel = match($competenciaStatus) {
        'processada' => 'Processada',
        'fechado', 'fechada' => 'Fechada',
        'reaberto', 'reaberta' => 'Reaberta',
        default => 'Aberta',
    };
    $statusClass = match($competenciaStatus) {
        'processada' => 'rh-badge-success',
        'fechado', 'fechada' => 'rh-badge-primary',
        'reaberto', 'reaberta' => 'rh-badge-warning',
        default => 'rh-badge-dark',
    };
@endphp
<style>
    .rh-dashboard{--rh-bg:#f8fafc;--rh-card:#ffffff;--rh-line:#e2e8f0;--rh-text:#0f172a;--rh-muted:#64748b;--rh-primary:#2563eb;--rh-success:#16a34a;--rh-warning:#ea580c;--rh-danger:#dc2626}

    .rh-dashboard{background:var(--rh-bg);transition:background-color .2s ease,color .2s ease}
    .rh-dashboard.rh-dark{--rh-bg:#020617;--rh-card:#0f172a;--rh-line:#1e293b;--rh-text:#e2e8f0;--rh-muted:#94a3b8;--rh-primary:#60a5fa;--rh-success:#4ade80;--rh-warning:#fb923c;--rh-danger:#f87171}
    .rh-dashboard.rh-dark .rh-card,.rh-dashboard.rh-dark .rh-shortcuts a{box-shadow:0 16px 36px rgba(2,6,23,.45)}
    .rh-dashboard.rh-dark .rh-table thead th{background:#0b1220;color:#cbd5e1;border-bottom-color:#1e293b}
    .rh-dashboard.rh-dark .rh-stat-sub,.rh-dashboard.rh-dark .rh-mini,.rh-dashboard.rh-dark .rh-shortcuts .desc{color:#94a3b8}
    .rh-dashboard.rh-dark .text-dark{color:#e2e8f0 !important}
    .rh-dashboard.rh-dark .btn-light{background:#e2e8f0;border-color:#e2e8f0}
    .rh-dashboard .rh-filter-card{padding:1rem;border:1px solid rgba(255,255,255,.14);border-radius:18px;background:rgba(255,255,255,.08);backdrop-filter:blur(4px)}
    .rh-dashboard .rh-filter-card label{font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:rgba(255,255,255,.7);margin-bottom:.35rem}
    .rh-dashboard .rh-filter-card .form-select{border:none;border-radius:12px;min-height:42px}
    .rh-dashboard .rh-kpi-trend{display:inline-flex;align-items:center;gap:.35rem;padding:.32rem .58rem;border-radius:999px;font-size:.72rem;font-weight:800}
    .rh-dashboard .rh-kpi-up{background:#dcfce7;color:#15803d}
    .rh-dashboard .rh-kpi-down{background:#fee2e2;color:#b91c1c}
    .rh-dashboard .rh-kpi-flat{background:#e2e8f0;color:#334155}
    .rh-dashboard .rh-hero{background:linear-gradient(135deg,#0f172a 0%,#1d4ed8 55%,#7c3aed 100%);border:none;border-radius:24px;color:#fff;overflow:hidden;box-shadow:0 24px 60px rgba(37,99,235,.22)}
    .rh-dashboard .rh-hero:before{content:"";position:absolute;inset:auto -80px -80px auto;width:220px;height:220px;background:rgba(255,255,255,.10);filter:blur(10px);border-radius:999px}
    .rh-dashboard .hero-grid{display:grid;grid-template-columns:minmax(0,2fr) minmax(280px,1fr);gap:1rem;align-items:start}
    .rh-dashboard .hero-chip{display:inline-flex;align-items:center;gap:.4rem;padding:.45rem .75rem;border:1px solid rgba(255,255,255,.2);border-radius:999px;background:rgba(255,255,255,.08);font-size:.78rem;font-weight:700}
    .rh-dashboard .hero-metric-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.75rem}
    .rh-dashboard .hero-metric{padding:1rem;border:1px solid rgba(255,255,255,.14);border-radius:18px;background:rgba(255,255,255,.08);backdrop-filter:blur(4px)}
    .rh-dashboard .hero-metric .label{font-size:.75rem;text-transform:uppercase;letter-spacing:.06em;color:rgba(255,255,255,.7);font-weight:700}
    .rh-dashboard .hero-metric .value{font-size:1.45rem;font-weight:800;margin-top:.25rem}
    .rh-dashboard .hero-metric .sub{font-size:.8rem;color:rgba(255,255,255,.72)}
    .rh-dashboard .rh-card{background:var(--rh-card);border:1px solid var(--rh-line);border-radius:20px;box-shadow:0 10px 28px rgba(15,23,42,.05);height:100%}
    .rh-dashboard .rh-card .card-header{background:transparent;border-bottom:1px solid #eef2f7;padding:1rem 1.2rem}
    .rh-dashboard .rh-card .card-body{padding:1.15rem 1.2rem}
    .rh-dashboard .rh-stat-card{position:relative;overflow:hidden}
    .rh-dashboard .rh-stat-card:after{content:"";position:absolute;right:-30px;bottom:-35px;width:110px;height:110px;border-radius:999px;background:linear-gradient(135deg,rgba(37,99,235,.07),rgba(124,58,237,.12))}
    .rh-dashboard .rh-stat-label{font-size:.78rem;font-weight:800;letter-spacing:.05em;text-transform:uppercase;color:var(--rh-muted)}
    .rh-dashboard .rh-stat-value{font-size:1.75rem;line-height:1.1;font-weight:800;color:var(--rh-text);margin-top:.4rem}
    .rh-dashboard .rh-stat-sub{font-size:.84rem;color:#94a3b8;margin-top:.35rem}
    .rh-dashboard .rh-stat-meta{display:flex;align-items:center;gap:.45rem;flex-wrap:wrap;margin-top:.8rem}
    .rh-dashboard .rh-badge{display:inline-flex;align-items:center;gap:.35rem;padding:.35rem .6rem;border-radius:999px;font-size:.75rem;font-weight:700}
    .rh-dashboard .rh-badge-primary{background:#dbeafe;color:#1d4ed8}
    .rh-dashboard .rh-badge-success{background:#dcfce7;color:#15803d}
    .rh-dashboard .rh-badge-warning{background:#ffedd5;color:#c2410c}
    .rh-dashboard .rh-badge-danger{background:#fee2e2;color:#b91c1c}
    .rh-dashboard .rh-badge-dark{background:#e2e8f0;color:#334155}
    .rh-dashboard .rh-toolbar a{border-radius:12px}
    .rh-dashboard .rh-shortcuts a{display:block;height:100%;padding:1rem 1rem;border-radius:18px;border:1px solid var(--rh-line);text-decoration:none;background:#fff;transition:all .18s ease;box-shadow:0 6px 18px rgba(15,23,42,.03)}
    .rh-dashboard .rh-shortcuts a:hover{transform:translateY(-2px);box-shadow:0 16px 30px rgba(15,23,42,.08)}
    .rh-dashboard .rh-shortcuts .title{font-weight:800;color:var(--rh-text);margin-bottom:.2rem}
    .rh-dashboard .rh-shortcuts .desc{font-size:.82rem;color:var(--rh-muted);line-height:1.4}
    .rh-dashboard .rh-progress{height:10px;border-radius:999px;background:#eef2ff}
    .rh-dashboard .rh-progress > div{height:10px;border-radius:999px;background:linear-gradient(90deg,#2563eb,#7c3aed)}
    .rh-dashboard .rh-table thead th{background:#f8fafc;color:#475569;font-size:.78rem;text-transform:uppercase;letter-spacing:.04em;border-bottom:1px solid #e2e8f0;white-space:nowrap}
    .rh-dashboard .rh-table td{vertical-align:middle}
    .rh-dashboard .rh-list-row{display:flex;justify-content:space-between;gap:.75rem;padding:.8rem 0;border-bottom:1px solid #eef2f7}
    .rh-dashboard .rh-list-row:last-child{border-bottom:none;padding-bottom:0}
    .rh-dashboard .rh-mini{font-size:.78rem;color:var(--rh-muted)}
    .rh-dashboard .rh-empty{padding:2rem 1rem;text-align:center;color:var(--rh-muted)}
    .rh-dashboard .rh-chart-wrap{position:relative;min-height:320px}
    .rh-dashboard .section-kicker{font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:#94a3b8}
    .rh-dashboard .hero-panel{padding:1rem;border:1px solid rgba(255,255,255,.14);border-radius:18px;background:rgba(255,255,255,.08)}
    .rh-dashboard .hero-panel .big{font-size:2rem;font-weight:800;line-height:1}
    .rh-dashboard .hero-panel .muted{font-size:.82rem;color:rgba(255,255,255,.72)}
    @media (max-width: 991px){.rh-dashboard .hero-grid{grid-template-columns:1fr}.rh-dashboard .hero-metric-grid{grid-template-columns:1fr 1fr}}
    @media (max-width: 576px){.rh-dashboard .hero-metric-grid{grid-template-columns:1fr}}
</style>

<div class="page-content rh-dashboard">
    <div class="card rh-hero mb-4 position-relative">
        <div class="card-body p-4 p-lg-5">
            <div class="hero-grid">
                <div>
                    <div class="hero-chip mb-3">Dashboard RH Executivo unificado</div>
                    <h3 class="mb-2 text-white">Dashboard RH Executivo</h3>
                    <div class="text-white-50" style="max-width:760px;">
                        Visão unificada do RH com indicadores operacionais, folha, alertas, headcount, férias e movimentações em uma única tela.
                    </div>
                    <div class="d-flex flex-wrap gap-2 mt-3">
                        <span class="hero-chip">{{ $ativos }} ativos</span>
                        <span class="hero-chip">{{ $inativos }} inativos</span>
                        <span class="hero-chip">Competência {{ $competenciaLabel }}</span>
                        <span class="hero-chip">Turnover {{ number_format($turnover,2,',','.') }}%</span>
                        @if($ultimaCompetencia)
                            <span class="hero-chip">Última apuração {{ $ultimaCompetencia }}</span>
                        @endif
                    </div>
                    <div class="d-flex flex-wrap gap-2 mt-3 rh-toolbar">
                        <a href="{{ route('rh.folha.processamento.index') }}" class="btn btn-light text-dark"><i class="bx bx-calculator"></i> Processar folha</a>
                        <a href="{{ route('apuracaoMensal.holerites_competencia') }}" class="btn btn-outline-light"><i class="bx bx-receipt"></i> Holerites</a>
                        <a href="{{ route('rh.portal_funcionario.index') }}" class="btn btn-outline-light"><i class="bx bx-user-circle"></i> Portal</a>
                        <button type="button" class="btn btn-outline-light" id="rhDarkModeToggle"><i class="bx bx-moon"></i> Modo escuro</button>
                    </div>
                <div class="hero-panel mt-3">
                    <div class="section-kicker text-white-50">Resumo executivo</div>
                    <div class="row g-3 align-items-center">
                        <div class="col-md-4">
                            <div class="big">{{ $totalFuncionarios }}</div>
                            <div class="muted">colaboradores no quadro total</div>
                        </div>
                        <div class="col-md-4">
                            <div class="big">R$ {{ number_format($folhaMensal,2,',','.') }}</div>
                            <div class="muted">base salarial ativa da empresa</div>
                        </div>
                        <div class="col-md-4">
                            <div class="big">{{ $alertasCount }}</div>
                            <div class="muted">alertas monitorados no ciclo atual</div>
                        </div>
                    </div>
                </div>

                </div>
                <div>
                    <div class="rh-filter-card mb-3">
                        <form method="GET" action="{{ url('/rh') }}" class="row g-2 align-items-end">
                            <div class="col-6">
                                <label>Competência</label>
                                <select name="mes" class="form-select">
                                    @for($mesOpcao = 1; $mesOpcao <= 12; $mesOpcao++)
                                        <option value="{{ $mesOpcao }}" @selected($mesSelecionado === $mesOpcao)>{{ str_pad($mesOpcao,2,'0',STR_PAD_LEFT) }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-6">
                                <label>Ano</label>
                                <select name="ano" class="form-select">
                                    @foreach($anosFiltro as $anoOpcao)
                                        <option value="{{ $anoOpcao }}" @selected($anoSelecionado === $anoOpcao)>{{ $anoOpcao }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 d-grid">
                                <button class="btn btn-light text-dark"><i class="bx bx-filter-alt"></i> Aplicar filtro</button>
                            </div>
                        </form>
                    </div>
                    <div class="hero-metric-grid">
                        <div class="hero-metric">
                            <div class="label">Status da competência</div>
                            <div class="value">{{ $statusLabel }}</div>
                            <div class="sub">{{ $folhaProcessadaNoMes ? 'Folha processada com dados reais.' : 'Sem apuração final, usando base disponível.' }}</div>
                        </div>
                        <div class="hero-metric">
                            <div class="label">Folha líquida</div>
                            <div class="value">R$ {{ number_format($liquidoMes,2,',','.') }}</div>
                            <div class="sub">Proventos R$ {{ number_format($totalProventosMes,2,',','.') }} · Descontos R$ {{ number_format($totalDescontosMes,2,',','.') }}</div>
                        </div>
                        <div class="hero-metric">
                            <div class="label">Alertas e férias</div>
                            <div class="value">{{ $alertasCount + $feriasPendentes }}</div>
                            <div class="sub">{{ $alertasCount }} alertas críticos · {{ $feriasPendentes }} férias pendentes</div>
                        </div>
                        <div class="hero-metric">
                            <div class="label">Saúde da folha</div>
                            <div class="value">{{ number_format($saudeFolha,2,',','.') }}%</div>
                            <div class="sub">Relação entre líquido processado e base salarial ativa.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3 mt-1">
        <div class="col-xl-3 col-md-6"><div class="card rh-card"><div class="card-body"><div class="rh-stat-label">Tendência da folha</div><div class="rh-stat-value">{{ $variacaoFolhaPercentual > 0 ? '+' : '' }}{{ number_format($variacaoFolhaPercentual,2,',','.') }}%</div><div class="rh-stat-sub">Comparação do líquido com a competência anterior</div><div class="rh-stat-meta"><span class="rh-kpi-trend {{ $variacaoFolhaPercentual > 0 ? 'rh-kpi-up' : ($variacaoFolhaPercentual < 0 ? 'rh-kpi-down' : 'rh-kpi-flat') }}">{{ $folhaAnteriorSerie ? 'vs. ' . ($folhaAnteriorSerie['competencia'] ?? 'anterior') : 'Sem histórico' }}</span></div></div></div></div>
        <div class="col-xl-3 col-md-6"><div class="card rh-card"><div class="card-body"><div class="rh-stat-label">Tendência do headcount</div><div class="rh-stat-value">{{ $variacaoHeadcountPainel > 0 ? '+' : '' }}{{ $variacaoHeadcountPainel }}</div><div class="rh-stat-sub">Diferença de ativos contra a competência anterior</div><div class="rh-stat-meta"><span class="rh-kpi-trend {{ $variacaoHeadcountPainel > 0 ? 'rh-kpi-up' : ($variacaoHeadcountPainel < 0 ? 'rh-kpi-down' : 'rh-kpi-flat') }}">{{ $headcountAnteriorSerie ? 'Ativos ' . ($headcountAnteriorSerie['label'] ?? 'anterior') : 'Sem histórico' }}</span></div></div></div></div>
        <div class="col-xl-3 col-md-6"><div class="card rh-card"><div class="card-body"><div class="rh-stat-label">Tendência do absenteísmo</div><div class="rh-stat-value">{{ $variacaoAbsenteismo > 0 ? '+' : '' }}{{ $variacaoAbsenteismo }}</div><div class="rh-stat-sub">Faltas + atrasos + atestados versus mês anterior</div><div class="rh-stat-meta"><span class="rh-kpi-trend {{ $variacaoAbsenteismo < 0 ? 'rh-kpi-up' : ($variacaoAbsenteismo > 0 ? 'rh-kpi-down' : 'rh-kpi-flat') }}">Atual {{ $absenteismoAtualTotal }} · anterior {{ $absenteismoAnteriorTotal }}</span></div></div></div></div>
        <div class="col-xl-3 col-md-6"><div class="card rh-card"><div class="card-body"><div class="rh-stat-label">Movimento líquido</div><div class="rh-stat-value">{{ $movimentoLiquido > 0 ? '+' : '' }}{{ $movimentoLiquido }}</div><div class="rh-stat-sub">Saldo entre admissões e desligamentos</div><div class="rh-stat-meta"><span class="rh-kpi-trend {{ $movimentoLiquido > 0 ? 'rh-kpi-up' : ($movimentoLiquido < 0 ? 'rh-kpi-down' : 'rh-kpi-flat') }}">Variação {{ $variacaoMovimento > 0 ? '+' : '' }}{{ $variacaoMovimento }}</span></div></div></div></div>
    </div>

    <div class="row g-3 mb-1">
        <div class="col-xl-3 col-md-6"><div class="card rh-card rh-stat-card"><div class="card-body"><div class="rh-stat-label">Quadro total</div><div class="rh-stat-value">{{ $totalFuncionarios }}</div><div class="rh-stat-sub">Colaboradores cadastrados</div><div class="rh-stat-meta"><span class="rh-badge rh-badge-primary">{{ number_format($percentualAtivos,2,',','.') }}% ativos</span><span class="rh-badge {{ $statusClass }}">{{ $statusLabel }}</span></div></div></div></div>
        <div class="col-xl-3 col-md-6"><div class="card rh-card rh-stat-card"><div class="card-body"><div class="rh-stat-label">Folha mensal</div><div class="rh-stat-value">R$ {{ number_format($folhaMensal,2,',','.') }}</div><div class="rh-stat-sub">Base salarial ativa</div><div class="rh-stat-meta"><span class="rh-badge rh-badge-dark">Médio por colaborador: R$ {{ number_format($custoMedioColaborador,2,',','.') }}</span></div></div></div></div>
        <div class="col-xl-3 col-md-6"><div class="card rh-card rh-stat-card"><div class="card-body"><div class="rh-stat-label">Líquido da competência</div><div class="rh-stat-value">R$ {{ number_format($liquidoMes,2,',','.') }}</div><div class="rh-stat-sub">Total líquido apurado em {{ $competenciaLabel }}</div><div class="rh-stat-meta"><span class="rh-badge rh-badge-success">Ticket médio: R$ {{ number_format($ticketMedioFolha,2,',','.') }}</span></div></div></div></div>
        <div class="col-xl-3 col-md-6"><div class="card rh-card rh-stat-card"><div class="card-body"><div class="rh-stat-label">Absenteísmo</div><div class="rh-stat-value">{{ $faltasMes }} / {{ $atrasosMes }}</div><div class="rh-stat-sub">Faltas e atrasos da competência</div><div class="rh-stat-meta"><span class="rh-badge rh-badge-warning">{{ $atestadosMes }} atestados</span></div></div></div></div>
    </div>

    <div class="row g-3 mb-1">
        <div class="col-xl-3 col-md-6"><div class="card rh-card"><div class="card-body"><div class="rh-stat-label">Admissões</div><div class="rh-stat-value">{{ $admissoesMes }}</div><div class="rh-stat-sub">Entradas em {{ $competenciaLabel }}</div></div></div></div>
        <div class="col-xl-3 col-md-6"><div class="card rh-card"><div class="card-body"><div class="rh-stat-label">Desligamentos</div><div class="rh-stat-value">{{ $desligamentosMes }}</div><div class="rh-stat-sub">Saídas registradas na competência</div></div></div></div>
        <div class="col-xl-3 col-md-6"><div class="card rh-card"><div class="card-body"><div class="rh-stat-label">Movimentações</div><div class="rh-stat-value">{{ $movimentacoesMes }}</div><div class="rh-stat-sub">Promoções, ajustes e eventos no período</div></div></div></div>
        <div class="col-xl-3 col-md-6"><div class="card rh-card"><div class="card-body"><div class="rh-stat-label">Variação do headcount</div><div class="rh-stat-value">{{ $variacaoHeadcount > 0 ? '+' : '' }}{{ $variacaoHeadcount }}</div><div class="rh-stat-sub">Saldo entre admissões e desligamentos</div></div></div></div>
    </div>

    <div class="row g-3 mt-1 rh-shortcuts">
        <div class="col-xl-2 col-md-4 col-6"><a href="/funcionarios"><div class="title">Funcionários</div><div class="desc">Cadastros, cargos e dados principais.</div></a></div>
        <div class="col-xl-2 col-md-4 col-6"><a href="/rh/salarios"><div class="title">Salários</div><div class="desc">Reajustes, custo base e histórico.</div></a></div>
        <div class="col-xl-2 col-md-4 col-6"><a href="/rh/movimentacoes"><div class="title">Movimentações</div><div class="desc">Promoções, mudanças e eventos internos.</div></a></div>
        <div class="col-xl-2 col-md-4 col-6"><a href="/rh/ferias"><div class="title">Férias</div><div class="desc">Programações, gozo e pendências.</div></a></div>
        <div class="col-xl-2 col-md-4 col-6"><a href="/rh/faltas"><div class="title">Absenteísmo</div><div class="desc">Faltas, atrasos e atestados.</div></a></div>
        <div class="col-xl-2 col-md-4 col-6"><a href="#ia-empresa-painel"><div class="title">IA da Empresa</div><div class="desc">Monitoramento automático de CNH, ASO, férias e vencimentos.</div></a></div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-lg-8">
            <div class="card rh-card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h6 class="mb-1">Fluxo de pessoas e variação do quadro</h6>
                        <div class="rh-mini">Admissões x desligamentos por mês</div>
                    </div>
                    <span class="rh-badge rh-badge-dark">Turnover {{ number_format($turnover,2,',','.') }}%</span>
                </div>
                <div class="card-body"><div class="rh-chart-wrap"><canvas id="rhChartFluxo"></canvas></div></div>
            </div>
        </div>
        <div class="col-lg-4" id="ia-empresa-painel">
            <div class="card rh-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1">IA da Empresa</h6>
                        <div class="rh-mini">Monitoramento automático integrado ao Dashboard RH Executivo</div>
                    </div>
                    <span class="rh-badge {{ $alertas->count() > 0 ? 'rh-badge-warning' : 'rh-badge-success' }}">{{ $alertas->count() }}</span>
                </div>
                <div class="card-body">
                    @forelse($alertas as $item)
                        @php
                            $badgeClass = 'rh-badge-success';
                            if (($item['gravidade'] ?? null) === 'danger') $badgeClass = 'rh-badge-danger';
                            elseif (($item['gravidade'] ?? null) === 'warning') $badgeClass = 'rh-badge-warning';
                            elseif (($item['gravidade'] ?? null) === 'primary') $badgeClass = 'rh-badge-primary';
                        @endphp
                        <div class="rh-list-row">
                            <div>
                                <div class="fw-bold">{{ $item['funcionario'] ?? 'Funcionário' }}</div>
                                <div class="rh-mini">{{ $item['tipo'] ?? 'Alerta' }} — {{ $item['descricao'] ?? '' }}</div>
                            </div>
                            <div class="text-end"><span class="rh-badge {{ $badgeClass }}">{{ $item['dias'] }} dias</span></div>
                        </div>
                    @empty
                        <div class="rh-empty">Nenhum alerta prioritário encontrado.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card rh-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1">Evolução real da folha</h6>
                        <div class="rh-mini">Últimas 6 competências com proventos, descontos e líquido</div>
                    </div>
                    <span class="rh-badge rh-badge-primary">{{ $serieFolha->count() }} períodos</span>
                </div>
                <div class="card-body"><div class="rh-chart-wrap"><canvas id="rhChartFolha"></canvas></div></div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card rh-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1">Headcount e movimentação</h6>
                        <div class="rh-mini">Ativos e variação mensal da equipe</div>
                    </div>
                    <span class="rh-badge rh-badge-dark">{{ $totalFuncionarios }} no cadastro</span>
                </div>
                <div class="card-body"><div class="rh-chart-wrap"><canvas id="rhChartHeadcount"></canvas></div></div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card rh-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1">Top salários ativos</h6>
                        <div class="rh-mini">Base salarial para análise rápida de concentração</div>
                    </div>
                    <span class="rh-badge rh-badge-dark">{{ $topSalarios->count() }} colaboradores</span>
                </div>
                <div class="card-body">
                    @forelse($topSalarios as $colaborador)
                        <div class="rh-list-row">
                            <div>
                                <div class="fw-bold">{{ $colaborador->nome }}</div>
                                <div class="rh-mini">{{ $colaborador->funcao ?: ($colaborador->cargo ?: 'Sem função definida') }}</div>
                            </div>
                            <div class="text-end"><strong>R$ {{ number_format((float) $colaborador->salario,2,',','.') }}</strong></div>
                        </div>
                    @empty
                        <div class="rh-empty">Nenhum salário encontrado para a empresa selecionada.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card rh-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1">Férias próximas</h6>
                        <div class="rh-mini">Programações mais próximas da execução</div>
                    </div>
                    <a href="/rh/ferias" class="btn btn-sm btn-outline-primary">Abrir módulo</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover rh-table mb-0">
                            <thead><tr><th>Funcionário</th><th>Início</th><th>Fim</th><th>Status</th></tr></thead>
                            <tbody>
                            @forelse($feriasProximas as $item)
                                @php $status = strtolower((string) $item->status); @endphp
                                <tr>
                                    <td>{{ optional($item->funcionario)->nome ?: '-' }}</td>
                                    <td>{{ $item->data_inicio ? \Carbon\Carbon::parse($item->data_inicio)->format('d/m/Y') : '-' }}</td>
                                    <td>{{ $item->data_fim ? \Carbon\Carbon::parse($item->data_fim)->format('d/m/Y') : '-' }}</td>
                                    <td><span class="rh-badge {{ $status === 'pendente' ? 'rh-badge-warning' : ($status === 'gozo' ? 'rh-badge-success' : 'rh-badge-primary') }}">{{ ucfirst($item->status) }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="rh-empty">Nenhuma programação de férias localizada.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-12">
            <div class="card rh-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1">Movimentações recentes</h6>
                        <div class="rh-mini">Últimos eventos relevantes do ciclo de pessoas</div>
                    </div>
                    <a href="/rh/movimentacoes" class="btn btn-sm btn-outline-primary">Abrir módulo</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover rh-table mb-0">
                            <thead><tr><th>Data</th><th>Funcionário</th><th>Tipo</th><th>Descrição</th></tr></thead>
                            <tbody>
                            @forelse($movimentacoesRecentes as $item)
                                <tr>
                                    <td>{{ $item->data_movimentacao ? \Carbon\Carbon::parse($item->data_movimentacao)->format('d/m/Y') : '-' }}</td>
                                    <td>{{ optional($item->funcionario)->nome ?: '-' }}</td>
                                    <td>{{ \App\Models\RHMovimentacao::tipos()[$item->tipo] ?? ucfirst((string) $item->tipo) }}</td>
                                    <td>{{ $item->descricao ?: '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="rh-empty">Nenhuma movimentação encontrada.</td></tr>
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
(function () {
    if (typeof Chart === 'undefined') return;


    const dashboardRoot = document.querySelector('.rh-dashboard');
    const darkModeToggle = document.getElementById('rhDarkModeToggle');
    const storageKey = 'rh-dashboard-theme';
    const applyTheme = (mode) => {
        if (!dashboardRoot) return;
        dashboardRoot.classList.toggle('rh-dark', mode === 'dark');
        if (darkModeToggle) {
            darkModeToggle.innerHTML = mode === 'dark'
                ? '<i class="bx bx-sun"></i> Modo claro'
                : '<i class="bx bx-moon"></i> Modo escuro';
        }
    };
    applyTheme(localStorage.getItem(storageKey) || 'light');
    if (darkModeToggle) {
        darkModeToggle.addEventListener('click', function () {
            const nextMode = dashboardRoot.classList.contains('rh-dark') ? 'light' : 'dark';
            localStorage.setItem(storageKey, nextMode);
            applyTheme(nextMode);
        });
    }

    const fluxoCanvas = document.getElementById('rhChartFluxo');
    if (fluxoCanvas) {
        new Chart(fluxoCanvas, {
            type: 'bar',
            data: {
                labels: @json($graficoMeses ?? []),
                datasets: [
                    {
                        label: 'Admissões',
                        data: @json($graficoAdmissoes ?? []),
                        borderRadius: 8,
                        borderSkipped: false,
                        backgroundColor: 'rgba(37, 99, 235, 0.85)'
                    },
                    {
                        label: 'Desligamentos',
                        data: @json($graficoDesligamentos ?? []),
                        borderRadius: 8,
                        borderSkipped: false,
                        backgroundColor: 'rgba(234, 88, 12, 0.85)'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: 'rgba(148, 163, 184, 0.15)' } },
                    x: { grid: { display: false } }
                },
                plugins: { legend: { position: 'bottom' } }
            }
        });
    }

    const folhaCanvas = document.getElementById('rhChartFolha');
    if (folhaCanvas) {
        const serieFolha = @json($serieFolha->values()->all());
        new Chart(folhaCanvas, {
            data: {
                labels: serieFolha.map(item => item.competencia),
                datasets: [
                    {
                        type: 'bar',
                        label: 'Proventos',
                        data: serieFolha.map(item => Number(item.proventos || 0)),
                        borderRadius: 10,
                        backgroundColor: 'rgba(37, 99, 235, 0.75)'
                    },
                    {
                        type: 'bar',
                        label: 'Descontos',
                        data: serieFolha.map(item => Number(item.descontos || 0)),
                        borderRadius: 10,
                        backgroundColor: 'rgba(234, 88, 12, 0.70)'
                    },
                    {
                        type: 'line',
                        label: 'Líquido',
                        data: serieFolha.map(item => Number(item.liquido || 0)),
                        tension: 0.35,
                        fill: false,
                        borderColor: 'rgba(22, 163, 74, 1)',
                        backgroundColor: 'rgba(22, 163, 74, 0.12)',
                        pointRadius: 4,
                        pointHoverRadius: 5,
                        yAxisID: 'y'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true, grid: { color: 'rgba(148, 163, 184, 0.12)' } },
                    x: { grid: { display: false } }
                },
                plugins: { legend: { position: 'bottom' } }
            }
        });
    }

    const headcountCanvas = document.getElementById('rhChartHeadcount');
    if (headcountCanvas) {
        const serie = @json($serieHeadcount->values()->all());
        new Chart(headcountCanvas, {
            data: {
                labels: serie.map(item => item.label),
                datasets: [
                    {
                        type: 'line',
                        label: 'Ativos',
                        data: serie.map(item => Number(item.ativos || 0)),
                        tension: 0.35,
                        borderColor: 'rgba(37, 99, 235, 1)',
                        backgroundColor: 'rgba(37, 99, 235, 0.10)',
                        pointRadius: 4,
                        fill: true
                    },
                    {
                        type: 'bar',
                        label: 'Admissões',
                        data: serie.map(item => Number(item.admissoes || 0)),
                        borderRadius: 8,
                        backgroundColor: 'rgba(22, 163, 74, 0.75)'
                    },
                    {
                        type: 'bar',
                        label: 'Desligamentos',
                        data: serie.map(item => Number(item.desligamentos || 0)),
                        borderRadius: 8,
                        backgroundColor: 'rgba(220, 38, 38, 0.70)'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: 'rgba(148, 163, 184, 0.12)' } },
                    x: { grid: { display: false } }
                },
                plugins: { legend: { position: 'bottom' } }
            }
        });
    }
})();
</script>
@endsection
