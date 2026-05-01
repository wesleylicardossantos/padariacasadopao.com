@extends('default.layout',['title' => 'ERP Enterprise Center'])
@section('content')
@php $resumo = collect($resumo ?? []); @endphp
<style>
.enterprise-shell{background:linear-gradient(180deg,#f8fbff 0%,#eef4ff 100%);padding:24px;border-radius:28px}.enterprise-card{background:#fff;border:1px solid #e7eefb;border-radius:24px;box-shadow:0 18px 55px rgba(15,23,42,.06)}
.kpi-label{font-size:.75rem;text-transform:uppercase;letter-spacing:.08em;color:#6b7280;font-weight:700}.kpi-value{font-size:1.65rem;font-weight:800;color:#0f172a}.kpi-hint{font-size:.86rem;color:#64748b}
.glass-pill{display:inline-flex;padding:.45rem .9rem;border-radius:999px;font-size:.8rem;font-weight:700;background:#edf4ff;color:#1d4ed8}.alert-box{border:1px solid #fde68a;background:#fffdf3;color:#92400e;border-radius:16px;padding:.85rem 1rem}
.soft-table thead th{font-size:.77rem;text-transform:uppercase;color:#6b7280}.soft-table td,.soft-table th{padding:.85rem 1rem}
</style>
<div class="page-content enterprise-shell">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
        <div>
            <h3 class="mb-1">Enterprise Center</h3>
            <div class="text-muted">Painel executivo unificado de RH, portal do funcionário, financeiro e integrações.</div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a class="btn btn-primary" href="/rh/folha/resumo-financeiro?mes={{ $mes }}&ano={{ $ano }}">RH + Financeiro</a>
            <a class="btn btn-outline-secondary" href="/portal">Portal funcionário</a>
        </div>
    </div>

    <form class="enterprise-card card mb-4" method="GET">
        <div class="card-body p-4">
            <div class="row g-3 align-items-end">
                <div class="col-md-2"><label class="form-label">Mês</label><input class="form-control" type="number" name="mes" min="1" max="12" value="{{ $mes }}"></div>
                <div class="col-md-2"><label class="form-label">Ano</label><input class="form-control" type="number" name="ano" value="{{ $ano }}"></div>
                <div class="col-md-3"><button class="btn btn-dark w-100">Atualizar</button></div>
                <div class="col-md-5 text-md-end"><span class="glass-pill">Cobertura da folha: {{ number_format((float)$coberturaFolha,2,',','.') }}x</span></div>
            </div>
        </div>
    </form>

    <div class="row g-3 mb-3">
        <div class="col-lg-3 col-md-6"><div class="enterprise-card card"><div class="card-body"><div class="kpi-label">Funcionários ativos</div><div class="kpi-value">{{ $funcionariosAtivos }}</div><div class="kpi-hint">Portal ativos: {{ $portalAtivos }}</div></div></div></div>
        <div class="col-lg-3 col-md-6"><div class="enterprise-card card"><div class="card-body"><div class="kpi-label">Folha líquida</div><div class="kpi-value">R$ {{ number_format((float)$folhaTotal,2,',','.') }}</div><div class="kpi-hint">Peso: {{ number_format((float)$pesoFolha,2,',','.') }}%</div></div></div></div>
        <div class="col-lg-3 col-md-6"><div class="enterprise-card card"><div class="card-body"><div class="kpi-label">Resultado após folha</div><div class="kpi-value">R$ {{ number_format((float)$resultadoAposFolha,2,',','.') }}</div><div class="kpi-hint">Caixa: R$ {{ number_format((float)$resultadoCaixa,2,',','.') }}</div></div></div></div>
        <div class="col-lg-3 col-md-6"><div class="enterprise-card card"><div class="card-body"><div class="kpi-label">Integrações hoje</div><div class="kpi-value">{{ $integracoesHoje }}</div><div class="kpi-hint">Auditoria portal hoje: {{ $auditCount }}</div></div></div></div>
    </div>

    @if(!empty($alertasFinanceiros))
    <div class="row g-3 mb-3">
        @foreach($alertasFinanceiros as $alerta)
            <div class="col-lg-6"><div class="alert-box">{{ $alerta }}</div></div>
        @endforeach
    </div>
    @endif

    <div class="row g-3 mb-3">
        <div class="col-lg-4"><div class="enterprise-card card"><div class="card-body"><div class="kpi-label">Salário base</div><div class="kpi-value">R$ {{ number_format((float)$totalSalarioBase,2,',','.') }}</div><div class="kpi-hint">Eventos: R$ {{ number_format((float)$totalEventos,2,',','.') }}</div></div></div></div>
        <div class="col-lg-4"><div class="enterprise-card card"><div class="card-body"><div class="kpi-label">Descontos</div><div class="kpi-value">R$ {{ number_format((float)$totalDescontos,2,',','.') }}</div><div class="kpi-hint">Capital comprometido: {{ number_format((float)$capitalComprometido,2,',','.') }}%</div></div></div></div>
        <div class="col-lg-4"><div class="enterprise-card card"><div class="card-body"><div class="kpi-label">Financeiro</div><div class="kpi-value">{{ $contasReceber }}</div><div class="kpi-hint">Contas a pagar: {{ $contasPagar }}</div></div></div></div>
    </div>

    <div class="enterprise-card card">
        <div class="card-header bg-transparent border-0 pt-4 px-4">
            <h5 class="mb-0">Top impacto na folha</h5>
        </div>
        <div class="card-body pt-0 px-0 pb-3">
            <div class="table-responsive">
                <table class="table soft-table mb-0">
                    <thead>
                        <tr>
                            <th>Funcionário</th>
                            <th>Base</th>
                            <th>Eventos</th>
                            <th>Descontos</th>
                            <th>Líquido</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($resumo->sortByDesc('liquido')->take(15) as $linha)
                        <tr>
                            <td>{{ $linha['funcionario']->nome }}</td>
                            <td>R$ {{ number_format((float)$linha['salario_base'],2,',','.') }}</td>
                            <td>R$ {{ number_format((float)$linha['eventos'],2,',','.') }}</td>
                            <td>R$ {{ number_format((float)$linha['descontos'],2,',','.') }}</td>
                            <td><strong>R$ {{ number_format((float)$linha['liquido'],2,',','.') }}</strong></td>
                            <td><a class="btn btn-sm btn-primary" href="/rh/holerite/{{ $linha['funcionario']->id }}?mes={{ $mes }}&ano={{ $ano }}" target="_blank">Holerite</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">Sem dados para a competência selecionada.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
