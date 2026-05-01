@extends('default.layout')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1">Auditoria Financeira Enterprise</h3>
            <small class="text-muted">Empresa {{ $empresa_id }} · Atualizado em {{ $audit['gerado_em'] ?? now()->format('d/m/Y H:i:s') }}</small>
        </div>
        <div>
            <a href="{{ route('enterprise.financeiro.inconsistencias', ['empresa_id' => $empresa_id, 'filial_id' => $filial_id]) }}" class="btn btn-sm btn-outline-primary">API inconsistências</a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3"><div class="card"><div class="card-body"><h6>Status</h6><h4>{{ strtoupper($audit['resumo']['saude_financeira'] ?? '-') }}</h4></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><h6>Checks</h6><h4>{{ $audit['resumo']['checks'] ?? 0 }}</h4></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><h6>Críticos</h6><h4>{{ $audit['resumo']['criticos'] ?? 0 }}</h4></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><h6>Impacto estimado</h6><h4>R$ {{ number_format($audit['resumo']['impacto_total_estimado'] ?? 0, 2, ',', '.') }}</h4></div></div></div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card"><div class="card-body">
                <h5>Checks de inconsistência</h5>
                <table class="table table-sm">
                    <tr><th>Código</th><th>Descrição</th><th>Qtd</th><th>Impacto</th><th>Status</th><th>Recomendação</th></tr>
                    @foreach(($audit['checks'] ?? []) as $check)
                    <tr>
                        <td>{{ $check['code'] }}</td>
                        <td>{{ $check['title'] }}</td>
                        <td>{{ $check['count'] }}</td>
                        <td>R$ {{ number_format($check['impact_value'], 2, ',', '.') }}</td>
                        <td>{{ $check['status_label'] }}</td>
                        <td>{{ $check['recommendation'] }}</td>
                    </tr>
                    @endforeach
                </table>
            </div></div>
        </div>
    </div>
</div>
@endsection
