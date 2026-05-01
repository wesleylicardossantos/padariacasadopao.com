@extends('default.layout',['title' => 'RH - DRE Completo com Custos de RH'])
@section('content')
@php
    $mes = (int) ($mes ?? date('m'));
    $ano = (int) ($ano ?? date('Y'));
    $encargosPerc = (float) ($encargosPerc ?? 28);
    $beneficiosPerc = (float) ($beneficiosPerc ?? 8);
    $provisoesPerc = (float) ($provisoesPerc ?? 11.11);
    $receitaBruta = (float) ($receitaBruta ?? 0);
    $despesasOperacionais = (float) ($despesasOperacionais ?? 0);
    $salarios = (float) ($salarios ?? 0);
    $encargos = (float) ($encargos ?? 0);
    $beneficios = (float) ($beneficios ?? 0);
    $provisoes = (float) ($provisoes ?? 0);
    $descontos = (float) ($descontos ?? 0);
    $folhaTotal = (float) ($folhaTotal ?? 0);
    $resultadoOperacional = (float) ($resultadoOperacional ?? 0);
    $margemOperacional = (float) ($margemOperacional ?? 0);
    $detalhes = $detalhes ?? [];
@endphp
<style>
.rh-card{border:1px solid #e8edf5;border-radius:16px;box-shadow:0 8px 24px rgba(15,23,42,.04)}
.rh-kpi{border:1px solid #e8edf5;border-radius:14px;background:#fff}
.rh-kpi .card-body{padding:1rem}
.rh-kpi .label{font-size:.78rem;text-transform:uppercase;font-weight:700;color:#64748b}
.rh-kpi .value{font-size:1.35rem;font-weight:800;color:#0f172a}
.rh-table thead th{background:#f8fafc;white-space:nowrap}
</style>

<div class="page-content">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h5 class="mb-0">DRE Completo com Custos de RH</h5>
            <small class="text-muted">Visão detalhada da DRE com separação de salários, encargos, benefícios, provisões e ranking de custo por funcionário.</small>
        </div>
        <div class="d-flex gap-2">
            <a href="/rh/custo-funcionario" class="btn btn-dark">Custo por Funcionário</a>
            <a href="/rh/folha/resumo-financeiro" class="btn btn-secondary">Resumo Financeiro</a>
        </div>
    </div>

    <form method="GET" action="/rh/dre-folha-detalhado">
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
                        <label class="form-label">Encargos %</label>
                        <input type="number" step="0.1" class="form-control" name="encargos" value="{{ $encargosPerc }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Benefícios %</label>
                        <input type="number" step="0.1" class="form-control" name="beneficios" value="{{ $beneficiosPerc }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Provisões %</label>
                        <input type="number" step="0.1" class="form-control" name="provisoes" value="{{ $provisoesPerc }}">
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
        <div class="col-lg-3 col-md-6"><div class="card rh-kpi"><div class="card-body"><div class="label">Folha Total RH</div><div class="value">R$ {{ number_format((float)$folhaTotal,2,',','.') }}</div></div></div></div>
        <div class="col-lg-3 col-md-6"><div class="card rh-kpi"><div class="card-body"><div class="label">Margem Operacional</div><div class="value">{{ number_format((float)$margemOperacional,2,',','.') }}%</div></div></div></div>
    </div>

    <div class="card rh-card mb-3">
        <div class="card-header bg-transparent"><h6 class="mb-0">Separação dos custos de RH</h6></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0 rh-table">
                    <thead>
                        <tr>
                            <th>Grupo</th>
                            <th class="text-end">Valor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>Salários + Eventos</td><td class="text-end">R$ {{ number_format((float)$salarios,2,',','.') }}</td></tr>
                        <tr><td>Encargos</td><td class="text-end">R$ {{ number_format((float)$encargos,2,',','.') }}</td></tr>
                        <tr><td>Benefícios</td><td class="text-end">R$ {{ number_format((float)$beneficios,2,',','.') }}</td></tr>
                        <tr><td>Provisões</td><td class="text-end">R$ {{ number_format((float)$provisoes,2,',','.') }}</td></tr>
                        <tr><td>(-) Descontos</td><td class="text-end text-danger">R$ {{ number_format((float)$descontos,2,',','.') }}</td></tr>
                        <tr class="table-light"><td><strong>Total RH</strong></td><td class="text-end"><strong>R$ {{ number_format((float)$folhaTotal,2,',','.') }}</strong></td></tr>
                        <tr class="table-light"><td><strong>Resultado Operacional</strong></td><td class="text-end {{ $resultadoOperacional < 0 ? 'text-danger' : 'text-success' }}"><strong>R$ {{ number_format((float)$resultadoOperacional,2,',','.') }}</strong></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card rh-card">
        <div class="card-header bg-transparent"><h6 class="mb-0">Ranking detalhado por funcionário</h6></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0 rh-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Funcionário</th>
                            <th>Subtotal</th>
                            <th>Encargos</th>
                            <th>Benefícios</th>
                            <th>Provisões</th>
                            <th>Descontos</th>
                            <th>Custo Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($detalhes as $i => $linha)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $linha['funcionario']->nome }}</td>
                            <td>R$ {{ number_format((float)$linha['subtotal'],2,',','.') }}</td>
                            <td>R$ {{ number_format((float)$linha['encargos'],2,',','.') }}</td>
                            <td>R$ {{ number_format((float)$linha['beneficios'],2,',','.') }}</td>
                            <td>R$ {{ number_format((float)$linha['provisoes'],2,',','.') }}</td>
                            <td>R$ {{ number_format((float)$linha['descontos'],2,',','.') }}</td>
                            <td><strong>R$ {{ number_format((float)$linha['custo'],2,',','.') }}</strong></td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="text-center py-4">Sem dados para o período informado.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection