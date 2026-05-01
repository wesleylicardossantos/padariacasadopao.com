@extends('default.layout')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1">Financeiro Enterprise</h3>
            <small class="text-muted">Atualizado em {{ $updated_at }}</small>
        </div>
        <div>
            <a href="{{ route('enterprise.financeiro.kpis', ['empresa_id' => $empresa_id, 'filial_id' => $filial_id]) }}" class="btn btn-sm btn-outline-primary">API KPI</a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3"><div class="card"><div class="card-body"><h6>Saldo previsto</h6><h4>R$ {{ number_format($snapshot['saldo_previsto'], 2, ',', '.') }}</h4><small>Risco: {{ strtoupper($snapshot['risco_caixa']) }}</small></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><h6>A receber pendente</h6><h4>R$ {{ number_format($snapshot['contas_receber']['pendente_valor'], 2, ',', '.') }}</h4><small>{{ $snapshot['contas_receber']['qtd_pendente'] }} títulos</small></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><h6>A pagar pendente</h6><h4>R$ {{ number_format($snapshot['contas_pagar']['pendente_valor'], 2, ',', '.') }}</h4><small>{{ $snapshot['contas_pagar']['qtd_pendente'] }} títulos</small></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><h6>Fluxo previsto 6 meses</h6><h4>R$ {{ number_format($cash_flow['saldo_previsto'], 2, ',', '.') }}</h4><small>{{ count($cash_flow['series']) }} períodos</small></div></div></div>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card"><div class="card-body">
                <h5>Aging</h5>
                <table class="table table-sm">
                    <tr><th></th><th>Vencido</th><th>Até 7 dias</th><th>8 a 30 dias</th></tr>
                    <tr><td>Receber</td><td>R$ {{ number_format($aging['receber_vencido'], 2, ',', '.') }}</td><td>R$ {{ number_format($aging['receber_ate_7'], 2, ',', '.') }}</td><td>R$ {{ number_format($aging['receber_8_30'], 2, ',', '.') }}</td></tr>
                    <tr><td>Pagar</td><td>R$ {{ number_format($aging['pagar_vencido'], 2, ',', '.') }}</td><td>R$ {{ number_format($aging['pagar_ate_7'], 2, ',', '.') }}</td><td>R$ {{ number_format($aging['pagar_8_30'], 2, ',', '.') }}</td></tr>
                </table>
            </div></div>
        </div>
        <div class="col-md-6">
            <div class="card"><div class="card-body">
                <h5>Fluxo de caixa projetado</h5>
                <table class="table table-sm">
                    <tr><th>Período</th><th>Entradas</th><th>Saídas</th><th>Saldo</th></tr>
                    @foreach($cash_flow['series'] as $linha)
                        <tr>
                            <td>{{ $linha['periodo'] }}</td>
                            <td>R$ {{ number_format($linha['entradas_previstas'], 2, ',', '.') }}</td>
                            <td>R$ {{ number_format($linha['saidas_previstas'], 2, ',', '.') }}</td>
                            <td>R$ {{ number_format($linha['saldo_mes'], 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </table>
            </div></div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card"><div class="card-body">
                <h5>Top devedores</h5>
                <table class="table table-sm"><tr><th>Referência</th><th>Qtde</th><th>Valor</th></tr>
                    @forelse($overview['top_devedores'] as $item)
                    <tr><td>{{ $item['nome'] }}</td><td>{{ $item['quantidade'] }}</td><td>R$ {{ number_format($item['valor'], 2, ',', '.') }}</td></tr>
                    @empty
                    <tr><td colspan="3">Sem dados pendentes.</td></tr>
                    @endforelse
                </table>
            </div></div>
        </div>
        <div class="col-md-6">
            <div class="card"><div class="card-body">
                <h5>Top fornecedores a pagar</h5>
                <table class="table table-sm"><tr><th>Referência</th><th>Qtde</th><th>Valor</th></tr>
                    @forelse($overview['top_fornecedores'] as $item)
                    <tr><td>{{ $item['nome'] }}</td><td>{{ $item['quantidade'] }}</td><td>R$ {{ number_format($item['valor'], 2, ',', '.') }}</td></tr>
                    @empty
                    <tr><td colspan="3">Sem dados pendentes.</td></tr>
                    @endforelse
                </table>
            </div></div>
        </div>
    </div>
</div>
@endsection
