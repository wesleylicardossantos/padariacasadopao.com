@extends('default.layout',['title' => 'RH V4 - Dashboard'])
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
    $feriasProximas = $feriasProximas ?? collect();
@endphp
<style>
.rh-kpi{border:1px solid #e9edf5;border-radius:14px;box-shadow:0 8px 24px rgba(15,23,42,.04)}
.rh-kpi .card-body{padding:1rem 1.1rem}
.rh-kpi .label{font-size:.82rem;color:#64748b;text-transform:uppercase;font-weight:700;letter-spacing:.03em}
.rh-kpi .value{font-size:1.45rem;font-weight:800;color:#0f172a;margin-top:.2rem}
.rh-kpi .sub{font-size:.78rem;color:#94a3b8}
</style>
<div class="page-content">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h5 class="mb-0">RH V4 - Dashboard Avançado</h5>
            <small class="text-muted">Férias, alertas, absenteísmo e turnover.</small>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="/rh/ferias" class="btn btn-primary">Férias</a>
            <a href="/rh/alertas" class="btn btn-dark">Alertas</a>
            <a href="/rh/faltas" class="btn btn-warning">Absenteísmo</a>
            <a href="/rh/desligamentos" class="btn btn-danger">Desligamentos</a>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-3 col-md-6"><div class="card rh-kpi"><div class="card-body"><div class="label">Funcionários</div><div class="value">{{ $totalFuncionarios }}</div><div class="sub">Base total</div></div></div></div>
        <div class="col-lg-3 col-md-6"><div class="card rh-kpi"><div class="card-body"><div class="label">Ativos</div><div class="value">{{ $ativos }}</div><div class="sub">Equipe ativa</div></div></div></div>
        <div class="col-lg-3 col-md-6"><div class="card rh-kpi"><div class="card-body"><div class="label">Folha Base</div><div class="value">R$ {{ number_format((float)$folhaBase,2,',','.') }}</div><div class="sub">Soma salarial atual</div></div></div></div>
        <div class="col-lg-3 col-md-6"><div class="card rh-kpi"><div class="card-body"><div class="label">Turnover</div><div class="value">{{ number_format((float)$turnover,2,',','.') }}%</div><div class="sub">Movimentação do mês</div></div></div></div>
        <div class="col-lg-3 col-md-6"><div class="card rh-kpi"><div class="card-body"><div class="label">Admissões mês</div><div class="value">{{ $admissoesMes }}</div><div class="sub">Entradas</div></div></div></div>
        <div class="col-lg-3 col-md-6"><div class="card rh-kpi"><div class="card-body"><div class="label">Desligamentos mês</div><div class="value">{{ $desligamentosMes }}</div><div class="sub">Saídas</div></div></div></div>
        <div class="col-lg-3 col-md-6"><div class="card rh-kpi"><div class="card-body"><div class="label">Faltas mês</div><div class="value">{{ $faltasMes }}</div><div class="sub">Absenteísmo</div></div></div></div>
        <div class="col-lg-3 col-md-6"><div class="card rh-kpi"><div class="card-body"><div class="label">Atestados / Atrasos</div><div class="value">{{ $atestadosMes }} / {{ $atrasosMes }}</div><div class="sub">Ocorrências do mês</div></div></div></div>
    </div>

    <div class="card mt-3">
        <div class="card-header bg-transparent"><h6 class="mb-0">Férias próximas</h6></div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead><tr><th>Funcionário</th><th>Início</th><th>Fim</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse($feriasProximas as $item)
                        <tr>
                            <td>{{ optional($item->funcionario)->nome }}</td>
                            <td>{{ \Carbon\Carbon::parse($item->data_inicio)->format('d/m/Y') }}</td>
                            <td>{{ \Carbon\Carbon::parse($item->data_fim)->format('d/m/Y') }}</td>
                            <td>{{ ucfirst($item->status) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center">Nenhuma programação próxima.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection