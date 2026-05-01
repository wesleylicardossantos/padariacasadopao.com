@extends('default.layout',['title' => 'RH - DRE com Folha'])
@section('content')
@php
    $mes = (int) ($mes ?? date('m'));
    $ano = (int) ($ano ?? date('Y'));
    $receitaBruta = (float) ($receitaBruta ?? 0);
    $despesasOperacionais = (float) ($despesasOperacionais ?? 0);
    $folha = (float) ($folha ?? 0);
    $margemOperacional = (float) ($margemOperacional ?? 0);
    $resultadoOperacional = (float) ($resultadoOperacional ?? 0);
    $linhas = collect($linhas ?? []);
@endphp
<style>
.rh-card{border:1px solid #e8edf5;border-radius:16px;box-shadow:0 8px 24px rgba(15,23,42,.04)}
.rh-kpi{border:1px solid #e8edf5;border-radius:14px;background:#fff}
.rh-kpi .card-body{padding:1rem}
.rh-kpi .label{font-size:.78rem;text-transform:uppercase;font-weight:700;color:#64748b}
.rh-kpi .value{font-size:1.35rem;font-weight:800;color:#0f172a}
.rh-table thead th{background:#f8fafc}
</style>

<div class="page-content">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h5 class="mb-0">DRE com Folha</h5>
            <small class="text-muted">Análise de resultado considerando receita, despesas operacionais e folha de pagamento.</small>
        </div>
        <div class="d-flex gap-2">
            <a href="/rh/folha/resumo-financeiro" class="btn btn-dark">Resumo Financeiro</a>
            <a href="/rh/folha" class="btn btn-secondary">Voltar para Folha</a>
        </div>
    </div>

    <form method="GET" action="/rh/dre-folha">
        <div class="card rh-card mb-3">
            <div class="card-body p-4">
                <div class="row g-3 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label">Mês</label>
                        <input type="number" class="form-control" name="mes" min="1" max="12" value="{{ $mes }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Ano</label>
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
        <div class="col-lg-3 col-md-6"><div class="card rh-kpi"><div class="card-body"><div class="label">Receita Bruta</div><div class="value">R$ {{ number_format((float)$receitaBruta,2,',','.') }}</div></div></div></div>
        <div class="col-lg-3 col-md-6"><div class="card rh-kpi"><div class="card-body"><div class="label">Despesas Operacionais</div><div class="value">R$ {{ number_format((float)$despesasOperacionais,2,',','.') }}</div></div></div></div>
        <div class="col-lg-3 col-md-6"><div class="card rh-kpi"><div class="card-body"><div class="label">Folha</div><div class="value">R$ {{ number_format((float)$folha,2,',','.') }}</div></div></div></div>
        <div class="col-lg-3 col-md-6"><div class="card rh-kpi"><div class="card-body"><div class="label">Margem Operacional</div><div class="value">{{ number_format((float)$margemOperacional,2,',','.') }}%</div></div></div></div>
    </div>

    <div class="card rh-card">
        <div class="card-header bg-transparent">
            <h6 class="mb-0">Demonstrativo do Resultado do Exercício — {{ str_pad($mes,2,'0',STR_PAD_LEFT) }}/{{ $ano }}</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0 rh-table">
                    <thead>
                        <tr>
                            <th>Grupo</th>
                            <th class="text-end">Valor</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($linhas as $linha)
                        <tr>
                            <td>{{ $linha['grupo'] }}</td>
                            <td class="text-end {{ $linha['valor'] < 0 ? 'text-danger' : '' }}">
                                <strong>R$ {{ number_format((float)$linha['valor'],2,',','.') }}</strong>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>Resultado Operacional</th>
                            <th class="text-end {{ $resultadoOperacional < 0 ? 'text-danger' : 'text-success' }}">
                                R$ {{ number_format((float)$resultadoOperacional,2,',','.') }}
                            </th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection