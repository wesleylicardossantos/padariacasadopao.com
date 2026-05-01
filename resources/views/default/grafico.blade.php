@extends('default.layout', ['title' => 'Gráficos'])
@section('content')
<div class="page-content">
    <div class="card card-custom gutter-b">
        <div class="card-body">
            @if(empresaComFilial() && sizeof(getLocaisUsarioLogado()) > 0)
            <div class="row">
                {!! __view_locais_select_home() !!}
                <div class="col-12 col-lg-4" style="margin-top: 38px">
                    <button id="set-location" class="btn btn-info">Definir como padrão</button>
                </div>
            </div>
            @endif
            <!-- <div class="row mt-4" style="margin-bottom: 7px;">
                <div class="col-12">
                    <div class="border-bottom-0 bg-transparent">
                        <button onclick="filtroBox(1)" class="btn btn-white">Hoje</button>
                        <button onclick="filtroBox(7)" class="btn btn-white">Semana</button>
                        <button onclick="filtroBox(30)" class="btn btn-white">Mês</button>
                        <button onclick="filtroBox(60)" class="btn btn-white">60 Dias</button>
                    </div>
                </div>
            </div> -->

            <style>
.dashboard-kpi-card{
    border:0;
    border-radius:16px;
    overflow:hidden;
    box-shadow:0 10px 25px rgba(15,23,42,.08);
}
.dashboard-kpi-card .card-body{ padding:1rem 1.1rem; }
.dashboard-kpi-card .kpi-label{
    font-size:.85rem;
    color:#ffffff;
    opacity:.92;
    margin-bottom:.15rem;
}
.dashboard-kpi-card .kpi-value{
    font-size:1.65rem;
    line-height:1.05;
    color:#fff;
    font-weight:700;
    margin-bottom:.2rem;
}
.dashboard-kpi-card .kpi-sub{
    font-size:.72rem;
    color:rgba(255,255,255,.85);
}
.dashboard-kpi-card .kpi-icon{
    font-size:2rem;
    opacity:.95;
}
.dashboard-section-title{
    font-size:1rem;
    font-weight:700;
    color:#0f172a;
}

.audit-panel{border:1px solid #e2e8f0;border-radius:18px;overflow:hidden;background:linear-gradient(180deg,#f8fafc 0%,#ffffff 100%);box-shadow:0 10px 25px rgba(15,23,42,.05);} 
.audit-panel .audit-header{padding:1rem 1.2rem;border-bottom:1px solid #e2e8f0;background:linear-gradient(135deg,#0f172a,#1e293b);color:#fff;}
.audit-panel .audit-title{font-size:1rem;font-weight:700;margin:0;}
.audit-panel .audit-subtitle{font-size:.78rem;opacity:.85;margin:0.2rem 0 0;}
.audit-badge{display:inline-flex;align-items:center;gap:.35rem;border-radius:999px;padding:.35rem .7rem;font-size:.75rem;font-weight:700;}
.audit-badge.saudavel{background:#dcfce7;color:#166534;}
.audit-badge.atencao{background:#fef3c7;color:#92400e;}
.audit-badge.critico{background:#fee2e2;color:#991b1b;}
.audit-mini-card{border:1px solid #e2e8f0;border-radius:14px;padding:.9rem 1rem;background:#fff;height:100%;}
.audit-mini-card .label{font-size:.75rem;color:#64748b;margin-bottom:.2rem;display:block;}
.audit-mini-card .value{font-size:1.15rem;font-weight:700;color:#0f172a;margin-bottom:.2rem;}
.audit-mini-card .hint{font-size:.72rem;color:#64748b;}
.audit-table-wrap{padding:1rem 1.2rem 1.2rem;}
.audit-table td{padding:.75rem 0.25rem;border-color:#edf2f7;}
.audit-table td:first-child{color:#64748b;font-weight:600;}
.audit-highlight{font-weight:700;color:#0f172a;}
.audit-diff-positive{color:#b91c1c;}
.audit-diff-negative{color:#166534;}


.dre-panel,.bi-panel,.pdv-panel{border:1px solid #e2e8f0;border-radius:18px;background:#fff;box-shadow:0 10px 25px rgba(15,23,42,.05);}
.section-chip{display:inline-flex;align-items:center;gap:.35rem;border-radius:999px;padding:.35rem .7rem;font-size:.75rem;font-weight:700;background:#eef2ff;color:#3730a3;}
.dre-status{display:inline-flex;align-items:center;border-radius:999px;padding:.35rem .75rem;font-size:.75rem;font-weight:700;}
.dre-status.lucro{background:#dcfce7;color:#166534;}
.dre-status.atencao{background:#fef3c7;color:#92400e;}
.dre-status.prejuizo{background:#fee2e2;color:#991b1b;}
.metric-grid-card{border:1px solid #edf2f7;border-radius:14px;padding:1rem;background:#fff;height:100%;}
.metric-grid-card .metric-label{font-size:.74rem;color:#64748b;display:block;margin-bottom:.2rem;}
.metric-grid-card .metric-value{font-size:1.15rem;font-weight:700;color:#0f172a;}
.ranking-table td,.ranking-table th,.pdv-table td,.pdv-table th{vertical-align:middle;}
.pdv-status{display:inline-flex;align-items:center;border-radius:999px;padding:.35rem .75rem;font-size:.75rem;font-weight:700;}
.pdv-status.saudavel{background:#dcfce7;color:#166534;}
.pdv-status.atencao{background:#fef3c7;color:#92400e;}
.pdv-status.critico{background:#fee2e2;color:#991b1b;}

</style>

            <div class="row @if (env('ANIMACAO')) animate__animated @endif animate__backInRight mt-3 g-3">
                <div class="col-12 col-lg-3 col-md-6">
                    <div class="card dashboard-kpi-card bg-gradient-burning">
                        <div class="card-body">
                            <div class="d-flex align-items-start">
                                <div>
                                    <p class="kpi-label">Vendas históricas</p>
                                    <h5 class="kpi-value total_vendas">R$ {{ __moeda(0) }}</h5>
                                    <div class="kpi-sub">Total acumulado de vendas + frente de caixa</div>
                                </div>
                                <div class="ms-auto text-white">
                                    <i class='bx bx-line-chart kpi-icon'></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-3 col-md-6">
                    <div class="card dashboard-kpi-card bg-gradient-cosmic">
                        <div class="card-body">
                            <div class="d-flex align-items-start">
                                <div>
                                    <p class="kpi-label">Vendas no mês</p>
                                    <h5 class="kpi-value total_vendas_mes">R$ {{ __moeda(0) }}</h5>
                                    <div class="kpi-sub">Somatório do mês atual</div>
                                </div>
                                <div class="ms-auto text-white">
                                    <i class='bx bx-calendar-alt kpi-icon'></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-3 col-md-6">
                    <div class="card dashboard-kpi-card bg-gradient-Ohhappiness">
                        <div class="card-body">
                            <div class="d-flex align-items-start">
                                <div>
                                    <p class="kpi-label">Vendas hoje</p>
                                    <h5 class="kpi-value total_vendas_hoje">R$ {{ __moeda(0) }}</h5>
                                    <div class="kpi-sub">Movimento do dia atual</div>
                                </div>
                                <div class="ms-auto text-white">
                                    <i class='bx bx-sun kpi-icon'></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-3 col-md-6">
                    <div class="card dashboard-kpi-card bg-gradient-moonlit">
                        <div class="card-body">
                            <div class="d-flex align-items-start">
                                <div>
                                    <p class="kpi-label">Produtos cadastrados</p>
                                    <h5 class="kpi-value total_produtos">0</h5>
                                    <div class="kpi-sub">Cadastro ativo por empresa/filial</div>
                                </div>
                                <div class="ms-auto text-white">
                                    <i class='bx bx-package kpi-icon'></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-3 col-md-6">
                    <div class="card dashboard-kpi-card" style="background:linear-gradient(135deg,#0f766e,#22c55e);">
                        <div class="card-body">
                            <div class="d-flex align-items-start">
                                <div>
                                    <p class="kpi-label">Contas a receber</p>
                                    <h5 class="kpi-value total_receber">R$ {{ __moeda(0) }}</h5>
                                    <div class="kpi-sub">Total pendente em aberto</div>
                                </div>
                                <div class="ms-auto text-white">
                                    <i class='bx bx-money kpi-icon'></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-3 col-md-6 dash-conta">
                    <div class="card dashboard-kpi-card" style="background:linear-gradient(135deg,#0f172a,#334155);">
                        <div class="card-body">
                            <div class="d-flex align-items-start">
                                <div>
                                    <p class="kpi-label">Contas a pagar</p>
                                    <h5 class="kpi-value total_pagar">R$ {{ __moeda(0) }}</h5>
                                    <div class="kpi-sub">Total pendente a liquidar</div>
                                </div>
                                <div class="ms-auto text-white">
                                    <i class='bx bx-credit-card kpi-icon'></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-3 col-md-6">
                    <div class="card dashboard-kpi-card" style="background:linear-gradient(135deg,#7c3aed,#4f46e5);">
                        <div class="card-body">
                            <div class="d-flex align-items-start">
                                <div>
                                    <p class="kpi-label">Ticket médio (mês)</p>
                                    <h5 class="kpi-value total_ticket_medio">R$ {{ __moeda(0) }}</h5>
                                    <div class="kpi-sub">Valor médio por venda do mês</div>
                                </div>
                                <div class="ms-auto text-white">
                                    <i class='bx bx-bar-chart-alt-2 kpi-icon'></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-3 col-md-6">
                    <div class="card dashboard-kpi-card" style="background:linear-gradient(135deg,#1d4ed8,#0ea5e9);">
                        <div class="card-body">
                            <div class="d-flex align-items-start">
                                <div>
                                    <p class="kpi-label">Qtd. vendas (mês)</p>
                                    <h5 class="kpi-value total_qtd_vendas">0</h5>
                                    <div class="kpi-sub">Volume de vendas do mês atual</div>
                                </div>
                                <div class="ms-auto text-white">
                                    <i class='bx bx-receipt kpi-icon'></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-12">
                    <div class="audit-panel">
                        <div class="audit-header d-flex flex-column flex-lg-row align-items-lg-center">
                            <div>
                                <h5 class="audit-title">Painel de auditoria financeira</h5>
                                <p class="audit-subtitle">Conciliação operacional do mês atual com foco em faturamento, recebimento, obrigações e saldo projetado.</p>
                            </div>
                            <div class="ms-lg-auto mt-3 mt-lg-0 d-flex align-items-center gap-2">
                                <span class="audit-badge audit-status saudavel">Status: carregando</span>
                                <small class="text-white-50 audit-updated-at">Atualizando...</small>
                            </div>
                        </div>
                        <div class="audit-table-wrap">
                            <div class="row g-3 mb-3">
                                <div class="col-12 col-md-6 col-xl-3">
                                    <div class="audit-mini-card">
                                        <span class="label">Recebido sobre faturado</span>
                                        <div class="value audit-percentual-recebido">0%</div>
                                        <div class="hint">Quanto do faturamento do mês já entrou em caixa.</div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6 col-xl-3">
                                    <div class="audit-mini-card">
                                        <span class="label">Pago sobre obrigações</span>
                                        <div class="value audit-percentual-pago">0%</div>
                                        <div class="hint">Percentual liquidado frente ao total pago + em aberto.</div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6 col-xl-3">
                                    <div class="audit-mini-card">
                                        <span class="label">Saldo projetado</span>
                                        <div class="value audit-saldo-projetado">R$ 0,00</div>
                                        <div class="hint">Contas a receber em aberto menos contas a pagar em aberto.</div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6 col-xl-3">
                                    <div class="audit-mini-card">
                                        <span class="label">Diferença faturado x recebido</span>
                                        <div class="value audit-diferenca-faturado">R$ 0,00</div>
                                        <div class="hint">Gap do mês entre o que foi faturado e o que efetivamente entrou.</div>
                                    </div>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table audit-table align-middle mb-0">
                                    <tbody>
                                        <tr>
                                            <td>Faturamento em vendas</td>
                                            <td class="text-end audit-highlight audit-faturamento-venda">R$ 0,00</td>
                                            <td>Faturamento frente de caixa</td>
                                            <td class="text-end audit-highlight audit-faturamento-caixa">R$ 0,00</td>
                                        </tr>
                                        <tr>
                                            <td>Faturamento total do mês</td>
                                            <td class="text-end audit-highlight audit-faturamento-total">R$ 0,00</td>
                                            <td>Recebido no mês</td>
                                            <td class="text-end audit-highlight audit-recebido-mes">R$ 0,00</td>
                                        </tr>
                                        <tr>
                                            <td>Pago no mês</td>
                                            <td class="text-end audit-highlight audit-pago-mes">R$ 0,00</td>
                                            <td>Contas a receber em aberto</td>
                                            <td class="text-end audit-highlight audit-receber-aberto">R$ 0,00</td>
                                        </tr>
                                        <tr>
                                            <td>Contas a pagar em aberto</td>
                                            <td class="text-end audit-highlight audit-pagar-aberto">R$ 0,00</td>
                                            <td>Leitura executiva</td>
                                            <td class="text-end audit-highlight audit-leitura">Saldo estável</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <div class="row mt-4 g-3">
                <div class="col-12">
                    <div class="dre-panel p-3 p-lg-4">
                        <div class="d-flex flex-column flex-lg-row align-items-lg-center mb-3 gap-2">
                            <div>
                                <h5 class="mb-1">DRE gerencial + lucro real</h5>
                                <small class="text-muted">Competência <span class="dre-periodo">--</span> com receita líquida, custos variáveis, despesas e lucro líquido.</small>
                            </div>
                            <div class="ms-lg-auto d-flex align-items-center gap-2">
                                <span class="section-chip">DRE</span>
                                <span class="dre-status lucro">Lucro real positivo</span>
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-12 col-md-3"><div class="metric-grid-card"><span class="metric-label">Receita bruta</span><div class="metric-value dre-receita-bruta">R$ 0,00</div></div></div>
                            <div class="col-12 col-md-3"><div class="metric-grid-card"><span class="metric-label">Deduções</span><div class="metric-value dre-deducoes">R$ 0,00</div></div></div>
                            <div class="col-12 col-md-3"><div class="metric-grid-card"><span class="metric-label">Receita líquida</span><div class="metric-value dre-receita-liquida">R$ 0,00</div></div></div>
                            <div class="col-12 col-md-3"><div class="metric-grid-card"><span class="metric-label">Lucro líquido</span><div class="metric-value dre-lucro-liquido">R$ 0,00</div></div></div>
                            <div class="col-12 col-md-3"><div class="metric-grid-card"><span class="metric-label">Custos variáveis</span><div class="metric-value dre-custos-variaveis">R$ 0,00</div></div></div>
                            <div class="col-12 col-md-3"><div class="metric-grid-card"><span class="metric-label">Despesas fixas</span><div class="metric-value dre-despesas-fixas">R$ 0,00</div></div></div>
                            <div class="col-12 col-md-2"><div class="metric-grid-card"><span class="metric-label">Margem líquida</span><div class="metric-value dre-margem-liquida">0%</div></div></div>
                            <div class="col-12 col-md-2"><div class="metric-grid-card"><span class="metric-label">Markup</span><div class="metric-value dre-markup">0%</div></div></div>
                            <div class="col-12 col-md-2"><div class="metric-grid-card"><span class="metric-label">Ponto equilíbrio</span><div class="metric-value dre-ponto-equilibrio">0%</div></div></div>
                            <div class="col-12 col-md-2"><div class="metric-grid-card"><span class="metric-label">Lucro bruto</span><div class="metric-value dre-lucro-bruto">R$ 0,00</div></div></div>
                        </div>
                        <div class="mt-3 text-muted small">Fonte de custo: <span class="dre-fonte-custo">--</span></div>
                    </div>
                </div>
            </div>

            <div class="row mt-4 g-3">
                <div class="col-12 col-xl-8">
                    <div class="bi-panel p-3 p-lg-4 h-100">
                        <div class="d-flex flex-column flex-lg-row align-items-lg-center mb-3 gap-2">
                            <div>
                                <h5 class="mb-1">BI comercial e financeiro</h5>
                                <small class="text-muted">Leitura avançada do período <span class="bi-periodo">--</span>.</small>
                            </div>
                            <div class="ms-lg-auto d-flex flex-column text-lg-end">
                                <span class="section-chip">BI completo</span>
                                <small class="text-muted bi-updated-at">Atualizando...</small>
                            </div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-4"><div class="metric-grid-card"><span class="metric-label">Receita líquida</span><div class="metric-value bi-receita-liquida">R$ 0,00</div></div></div>
                            <div class="col-md-4"><div class="metric-grid-card"><span class="metric-label">Lucro líquido</span><div class="metric-value bi-lucro-liquido">R$ 0,00</div></div></div>
                            <div class="col-md-4"><div class="metric-grid-card"><span class="metric-label">Margem líquida</span><div class="metric-value bi-margem-liquida">0%</div></div></div>
                        </div>
                        <div class="row g-3">
                            <div class="col-12"><div id="chart-bi-daily"></div></div>
                            <div class="col-12"><div id="chart-dre-year"></div></div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-xl-4">
                    <div class="bi-panel p-3 p-lg-4 h-100">
                        <h6 class="mb-3">Mix de formas de pagamento</h6>
                        <div id="chart-bi-payment"></div>
                    </div>
                </div>
            </div>

            <div class="row mt-4 g-3">
                <div class="col-12 col-xl-6">
                    <div class="bi-panel p-3 p-lg-4 h-100">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h6 class="mb-0">Ranking de produtos</h6>
                            <span class="section-chip">Top 10</span>
                        </div>
                        <div class="table-responsive">
                            <table class="table ranking-table mb-0">
                                <thead><tr><th>#</th><th>Produto</th><th class="text-end">Qtd</th><th class="text-end">Faturamento</th></tr></thead>
                                <tbody class="bi-top-produtos"><tr><td colspan="4" class="text-center text-muted">Carregando...</td></tr></tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-xl-6">
                    <div class="bi-panel p-3 p-lg-4 h-100">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h6 class="mb-0">Ranking de clientes</h6>
                            <span class="section-chip">Top 10</span>
                        </div>
                        <div class="table-responsive">
                            <table class="table ranking-table mb-0">
                                <thead><tr><th>#</th><th>Cliente</th><th class="text-end">Qtd vendas</th><th class="text-end">Faturamento</th></tr></thead>
                                <tbody class="bi-top-clientes"><tr><td colspan="4" class="text-center text-muted">Carregando...</td></tr></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4 g-3">
                <div class="col-12">
                    <div class="pdv-panel p-3 p-lg-4">
                        <div class="d-flex flex-column flex-lg-row align-items-lg-center mb-3 gap-2">
                            <div>
                                <h5 class="mb-1">Auditoria automática de divergência PDV</h5>
                                <small class="text-muted">Conciliação entre venda de frente de caixa e financeiro vinculado no período <span class="pdv-audit-periodo">--</span>.</small>
                            </div>
                            <div class="ms-lg-auto d-flex flex-column text-lg-end">
                                <span class="pdv-status pdv-audit-status saudavel">Sem divergências relevantes</span>
                                <small class="text-muted pdv-updated-at">Atualizando...</small>
                            </div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-2"><div class="metric-grid-card"><span class="metric-label">Total PDV</span><div class="metric-value pdv-total-vendas">R$ 0,00</div></div></div>
                            <div class="col-md-2"><div class="metric-grid-card"><span class="metric-label">Total financeiro</span><div class="metric-value pdv-total-financeiro">R$ 0,00</div></div></div>
                            <div class="col-md-2"><div class="metric-grid-card"><span class="metric-label">Diferença</span><div class="metric-value pdv-diferenca-total">R$ 0,00</div></div></div>
                            <div class="col-md-2"><div class="metric-grid-card"><span class="metric-label">Qtd vendas PDV</span><div class="metric-value pdv-qtd-vendas">0</div></div></div>
                            <div class="col-md-2"><div class="metric-grid-card"><span class="metric-label">Sem conta</span><div class="metric-value pdv-sem-conta">0</div></div></div>
                            <div class="col-md-2"><div class="metric-grid-card"><span class="metric-label">Com divergência</span><div class="metric-value pdv-com-divergencia">0</div></div></div>
                        </div>
                        <div class="table-responsive">
                            <table class="table pdv-table mb-0">
                                <thead><tr><th>Venda</th><th>Data</th><th>Cliente</th><th class="text-end">Valor venda</th><th class="text-end">Financeiro</th><th class="text-end">Diferença</th></tr></thead>
                                <tbody class="pdv-audit-table-body"><tr><td colspan="6" class="text-center text-muted">Carregando auditoria...</td></tr></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>


            <div class="row @if (env('ANIMACAO')) animate__animated @endif animate__bounce">
                <div class="">
                    <div class="card-header border-bottom-0 bg-transparent">
                        <div class="card radius-10">
                            <div class="card-header border-bottom-0 bg-transparent">
                                <div class="d-lg-flex align-items-center">
                                    <div>
                                        <h5 class="font-weight-bold mb-2 mb-lg-0">Faturamento de Vendas Anual</h5>
                                    </div>
                                    <div class="ms-lg-auto mb-2 mb-lg-0">
                                        <div class="btn-group-round">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div id="chart1"></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-lg-12">
                                <div class="card radius-10">
                                    <div class="card-header border-bottom-0 bg-transparent">
                                        <div class="d-lg-flex align-items-center">
                                            <div>
                                                <h6 class="font-weight-bold mb-2 mb-lg-0">Movimentação de Produtos Anual</h6>
                                            </div>
                                            <div class="font-22 ms-auto"><i class=""></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-flex align-items-center ms-auto font-13 gap-2">
                                            <span class="border px-1 rounded cursor-pointer"><i class="bx bxs-circle text-danger me-1"></i>Cadastrados no Mês</span>
                                            <span class="border px-1 rounded cursor-pointer"><i class="bx bxs-circle text-success me-1"></i>Vendidos no Dia</span>
                                            <span class="border px-1 rounded cursor-pointer"><i class="bx bxs-circle text-info me-1"></i>Sem venda no Mês</span>
                                        </div>
                                        <div id="chart2"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-xl-6">
                                <div class="">
                                    <div class="card radius-10 w-100">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div>
                                                    <h6 class="font-weight-bold mb-0">Contas a Receber</h6>
                                                </div>
                                                <div class="dropdown ms-auto">
                                                    <div class="cursor-pointer text-dark font-24 dropdown-toggle dropdown-toggle-nocaret" data-bs-toggle="dropdown"><i class=""></i>
                                                    </div>
                                                </div>
                                            </div>
                                            <div id="chart4"></div>
                                            <div class="d-flex align-items-center justify-content-between text-center">
                                                <div>
                                                    <h5 class="mb-1 font-weight-bold cr-recebido"></h5>
                                                    <p class="mb-0 text-secondary">Recebido</p>
                                                </div>
                                                <!-- <div class="mb-1">
                                                        <h5 class="mb-1 font-weight-bold">300</h5>
                                                        <p class="mb-0 text-secondary">Vendido</p>
                                                    </div> -->
                                                <div>
                                                    <h5 class="mb-1 font-weight-bold cr-receber"></h5>
                                                    <p class="mb-0 text-secondary">A Receber</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 col-lg-6 d-flex">
                                    <div class="card w-100 radius-10 shadow-none bg-transparent">
                                        <div class="card-body p-0">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-xl-6">
                                <div class="">
                                    <div class="card radius-10 w-100">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div>
                                                    <h6 class="font-weight-bold mb-0">Contas a Pagar</h6>
                                                </div>
                                                <div class="dropdown ms-auto">
                                                    <div class="cursor-pointer text-dark font-24 dropdown-toggle dropdown-toggle-nocaret" data-bs-toggle="dropdown"><i class=""></i>
                                                    </div>
                                                </div>
                                            </div>
                                            <div id="chart9"></div>
                                            <div class="d-flex align-items-center justify-content-between text-center">
                                                <div>
                                                    <h5 class="mb-1 font-weight-bold cp-pago"></h5>
                                                    <p class="mb-0 text-secondary">Pago</p>
                                                </div>
                                                <!-- <div class="mb-1">
                                                        <h5 class="mb-1 font-weight-bold">348</h5>
                                                        <p class="mb-0 text-secondary">Compras</p>
                                                    </div> -->
                                                <div>
                                                    <h5 class="mb-1 font-weight-bold cp-pagar"></h5>
                                                    <p class="mb-0 text-secondary">A Pagar</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-lg-6 d-flex">
                                    <div class="card w-100 radius-10 shadow-none bg-transparent">
                                        <div class="card-body p-0">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@section('js')
<script src="/assets/js/apexcharts.min.js"></script>
<script src="/js/grafico.js"></script>
@endsection
@endsection
