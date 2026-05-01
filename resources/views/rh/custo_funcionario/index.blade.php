@extends('default.layout',['title' => 'RH - Custo por Funcionário'])
@section('content')
<style>
.cardx{border:1px solid #e8edf5;border-radius:14px}
.kpi .label{font-size:.8rem;color:#64748b;text-transform:uppercase}
.kpi .value{font-weight:800;font-size:1.4rem}
</style>

<div class="page-content">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="mb-0">Custo por Funcionário</h5>
            <small class="text-muted">Ranking por custo (salário + encargos + benefícios)</small>
        </div>
        <div class="d-flex gap-2">
            <a href="/rh/dre-folha" class="btn btn-dark">DRE com Folha</a>
            <a href="/rh/folha/resumo-financeiro" class="btn btn-secondary">Resumo</a>
        </div>
    </div>

    <form method="GET" action="/rh/custo-funcionario">
        <div class="card cardx mb-3">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label">Mês</label>
                        <input type="number" name="mes" class="form-control" min="1" max="12" value="{{ $mes }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Ano</label>
                        <input type="number" name="ano" class="form-control" value="{{ $ano }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Encargos %</label>
                        <input type="number" step="0.1" name="encargos" class="form-control" value="{{ $encargosPerc }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Benefícios %</label>
                        <input type="number" step="0.1" name="beneficios" class="form-control" value="{{ $beneficiosPerc }}">
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary w-100">Atualizar</button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card cardx kpi"><div class="card-body">
                <div class="label">Folha (Custo Total)</div>
                <div class="value">R$ {{ number_format((float)$totalFolha,2,',','.') }}</div>
            </div></div>
        </div>
    </div>

    <div class="card cardx">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Funcionário</th>
                            <th>Base</th>
                            <th>Eventos</th>
                            <th>Descontos</th>
                            <th>Encargos</th>
                            <th>Benefícios</th>
                            <th>Custo Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($linhas as $i => $l)
                        <tr>
                            <td>{{ $i+1 }}</td>
                            <td>{{ $l['funcionario']->nome }}</td>
                            <td>R$ {{ number_format((float)$l['base'],2,',','.') }}</td>
                            <td>R$ {{ number_format((float)$l['eventos'],2,',','.') }}</td>
                            <td>R$ {{ number_format((float)$l['descontos'],2,',','.') }}</td>
                            <td>R$ {{ number_format((float)$l['encargos'],2,',','.') }}</td>
                            <td>R$ {{ number_format((float)$l['beneficios'],2,',','.') }}</td>
                            <td><strong>R$ {{ number_format((float)$l['custo'],2,',','.') }}</strong></td>
                        </tr>
                        @endforeach
                        @if(empty($linhas))
                        <tr><td colspan="8" class="text-center py-4">Sem dados</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
