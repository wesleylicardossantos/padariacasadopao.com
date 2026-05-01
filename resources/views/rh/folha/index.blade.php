@extends('default.layout',['title' => 'RH - Folha Básica'])
@section('content')
@php
    $mes = (int) ($mes ?? date('m'));
    $ano = (int) ($ano ?? date('Y'));
    $nome = $nome ?? '';
    $linhas = collect($linhas ?? []);
    $funcionarios = $funcionarios ?? new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
    $totalSalario = (float) ($totalSalario ?? 0);
    $totalEventos = (float) ($totalEventos ?? 0);
    $totalDescontos = (float) ($totalDescontos ?? 0);
    $totalLiquido = (float) ($totalLiquido ?? 0);
    $totalDescontosManuais = (float) ($totalDescontosManuais ?? 0);
    $totalDescontosLegais = (float) ($totalDescontosLegais ?? 0);
    $snapshotFinanceiro = $snapshotFinanceiro ?? [];
    $alertasFinanceiros = $alertasFinanceiros ?? [];
    $competenciaFechada = (bool) ($competenciaFechada ?? false);
    $fechamentoAtual = $fechamentoAtual ?? null;
@endphp
<style>
.ca-page{background:#f6f9fc;padding:18px;border-radius:24px}.ca-card{background:#fff;border:1px solid #e6edf7;border-radius:20px;box-shadow:0 10px 30px rgba(15,23,42,.05)}
.ca-kpi .label{font-size:.74rem;text-transform:uppercase;letter-spacing:.04em;color:#6b7a90;font-weight:700}.ca-kpi .value{font-size:1.45rem;font-weight:800;color:#0f172a}.ca-kpi .hint{font-size:.82rem;color:#6b7a90}
.soft-table thead th{font-size:.78rem;text-transform:uppercase;color:#6b7a90;border-bottom-color:#e6edf7}.soft-table td,.soft-table th{padding:.85rem 1rem;vertical-align:middle}.alert-chip{border-radius:14px;padding:.75rem .9rem;border:1px solid #fde68a;background:#fffaf0;color:#92400e}
</style>
<div class="page-content ca-page">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h4 class="mb-1">Folha básica · visão Conta Azul</h4>
            <small class="text-muted">Salário, eventos, descontos e impacto financeiro da competência.</small>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="/rh/folha/resumo-financeiro?mes={{ $mes }}&ano={{ $ano }}" class="btn btn-outline-primary">Resumo integrado</a>
            <a href="/rh/ia-decisao?mes={{ $mes }}&ano={{ $ano }}" class="btn btn-primary">IA de decisão</a>
        </div>
    </div>

    <form method="GET" action="/rh/folha" class="card ca-card mb-3">
        <div class="card-body p-4">
            <div class="row g-3 align-items-end">
                <div class="col-md-4"><label class="form-label">Funcionário</label><input type="text" class="form-control" name="nome" value="{{ $nome }}"></div>
                <div class="col-md-2"><label class="form-label">Mês</label><input type="number" class="form-control" name="mes" min="1" max="12" value="{{ $mes }}"></div>
                <div class="col-md-2"><label class="form-label">Ano</label><input type="number" class="form-control" name="ano" value="{{ $ano }}"></div>
                <div class="col-md-2"><button class="btn btn-primary w-100">Filtrar</button></div>
            </div>
        </div>
    </form>

    <div class="row g-3 mb-3">
        <div class="col-lg-3 col-md-6"><div class="card ca-card ca-kpi"><div class="card-body"><div class="label">Base salarial</div><div class="value">R$ {{ number_format((float)$totalSalario,2,',','.') }}</div></div></div></div>
        <div class="col-lg-3 col-md-6"><div class="card ca-card ca-kpi"><div class="card-body"><div class="label">Eventos</div><div class="value">R$ {{ number_format((float)$totalEventos,2,',','.') }}</div></div></div></div>
        <div class="col-lg-3 col-md-6"><div class="card ca-card ca-kpi"><div class="card-body"><div class="label">Descontos</div><div class="value">R$ {{ number_format((float)$totalDescontos,2,',','.') }}</div><div class="hint">Manuais R$ {{ number_format((float)$totalDescontosManuais,2,',','.') }} · Legais R$ {{ number_format((float)$totalDescontosLegais,2,',','.') }}</div></div></div></div>
        <div class="col-lg-3 col-md-6"><div class="card ca-card ca-kpi"><div class="card-body"><div class="label">Líquido</div><div class="value">R$ {{ number_format((float)$totalLiquido,2,',','.') }}</div><div class="hint">Peso da folha: {{ number_format((float)($snapshotFinanceiro['pesoFolhaReceita'] ?? 0),2,',','.') }}%</div></div></div></div>
    </div>

    @if(!empty($alertasFinanceiros))
    <div class="row g-3 mb-3">
        @foreach($alertasFinanceiros as $alerta)
        <div class="col-lg-6"><div class="alert-chip">{{ $alerta }}</div></div>
        @endforeach
    </div>
    @endif

    <div class="card ca-card mb-3">
        <div class="card-body p-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h6 class="mb-1">Fechamento mensal</h6>
                <small class="text-muted">Gera histórico e conta a pagar da folha para o Financeiro.</small>
            </div>
            @if($competenciaFechada)
                <div class="d-flex gap-2 flex-wrap align-items-center">
                    <div class="alert alert-success mb-0 py-2 px-3">Competência {{ str_pad($fechamentoAtual->mes,2,'0',STR_PAD_LEFT) }}/{{ $fechamentoAtual->ano }} fechada · Conta #{{ $fechamentoAtual->conta_pagar_id ?? '-' }}</div>
                    <form method="POST" action="{{ route('rh.folha.reabrir') }}">@csrf<input type="hidden" name="mes" value="{{ $mes }}"><input type="hidden" name="ano" value="{{ $ano }}"><button class="btn btn-warning" onclick="return confirm('Deseja reabrir a folha desta competência?')">Reabrir</button></form>
                </div>
            @else
                <form method="POST" action="{{ route('rh.folha.fechar') }}" class="d-flex gap-2 flex-wrap">@csrf<input type="hidden" name="mes" value="{{ $mes }}"><input type="hidden" name="ano" value="{{ $ano }}"><input type="text" class="form-control" name="observacao" placeholder="Observação do fechamento" style="min-width:260px"><button class="btn btn-success" onclick="return confirm('Deseja fechar a folha desta competência e gerar a conta a pagar no financeiro?')">Fechar folha mensal</button></form>
            @endif
        </div>
    </div>

    <div class="card ca-card mb-3">
        <div class="card-header bg-transparent border-0 pt-4 px-4"><h5 class="mb-0">Funcionários da competência</h5></div>
        <div class="card-body pt-0 px-0 pb-2">
            <div class="table-responsive">
                <table class="table soft-table mb-0">
                    <thead>
                        <tr><th>Funcionário</th><th>Função</th><th>Base</th><th>Eventos</th><th>Descontos</th><th>Detalhe</th><th>Líquido</th><th>Documentos</th></tr>
                    </thead>
                    <tbody>
                        @forelse($linhas as $linha)
                        <tr>
                            <td>{{ $linha['funcionario']->nome }}</td>
                            <td>{{ $linha['funcionario']->funcao ?? '-' }}</td>
                            <td>R$ {{ number_format((float)$linha['salario_base'],2,',','.') }}</td>
                            <td>R$ {{ number_format((float)$linha['eventos'],2,',','.') }}</td>
                            <td>R$ {{ number_format((float)$linha['descontos'],2,',','.') }}</td>
                            <td><small class="text-muted">Manuais: R$ {{ number_format((float)($linha['descontos_manuais'] ?? 0),2,',','.') }}<br>Legais: R$ {{ number_format((float)($linha['descontos_legais'] ?? 0),2,',','.') }}</small></td>
                            <td><strong>R$ {{ number_format((float)$linha['liquido'],2,',','.') }}</strong></td>
                            <td class="text-nowrap"><a href="/rh/recibo/{{ $linha['funcionario']->id }}?mes={{ $mes }}&ano={{ $ano }}" target="_blank" class="btn btn-sm btn-primary">Recibo</a> <a href="/rh/holerite/{{ $linha['funcionario']->id }}?mes={{ $mes }}&ano={{ $ano }}" target="_blank" class="btn btn-sm btn-outline-primary">Holerite</a></td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="text-center py-4 text-muted">Nenhum funcionário encontrado.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {!! $funcionarios->appends(request()->all())->links() !!}
        </div>
    </div>
</div>
@endsection