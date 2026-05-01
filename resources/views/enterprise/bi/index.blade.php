@extends('default.layout')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1">BI Executivo + DRE Real</h3>
            <small class="text-muted">Período {{ sprintf('%02d/%04d', $mes, $ano) }} · Atualizado em {{ $updated_at }}</small>
        </div>
        <div>
            <a href="{{ route('enterprise.bi.dashboard', ['empresa_id' => $empresa_id, 'ano' => $ano, 'mes' => $mes]) }}" class="btn btn-sm btn-outline-primary">API Dashboard</a>
            <a href="{{ route('enterprise.bi.dre', ['empresa_id' => $empresa_id, 'ano' => $ano, 'mes' => $mes]) }}" class="btn btn-sm btn-outline-secondary">API DRE</a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3"><div class="card"><div class="card-body"><h6>Vendas no mês</h6><h4>R$ {{ number_format($summary['cards']['vendas_mes'] ?? 0, 2, ',', '.') }}</h4></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><h6>Vendas hoje</h6><h4>R$ {{ number_format($summary['cards']['vendas_hoje'] ?? 0, 2, ',', '.') }}</h4></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><h6>Lucro líquido</h6><h4>R$ {{ number_format($dre['lucro_liquido'] ?? 0, 2, ',', '.') }}</h4></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><h6>Margem líquida</h6><h4>{{ number_format($dre['margem_liquida_percentual'] ?? 0, 2, ',', '.') }}%</h4><small>{{ $dre['status_resultado_label'] ?? '' }}</small></div></div></div>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card"><div class="card-body">
                <h5>DRE Real</h5>
                <table class="table table-sm">
                    @foreach($dre['detalhes'] ?? [] as $linha)
                    <tr><td>{{ $linha['nome'] }}</td><td class="text-right">R$ {{ number_format($linha['valor'], 2, ',', '.') }}</td></tr>
                    @endforeach
                </table>
            </div></div>
        </div>
        <div class="col-md-6">
            <div class="card"><div class="card-body">
                <h5>Série anual de vendas</h5>
                <table class="table table-sm">
                    <tr><th>Mês</th><th>Valor</th></tr>
                    @foreach(($summary['series']['meses'] ?? []) as $i => $mesLabel)
                    <tr><td>{{ $mesLabel }}</td><td class="text-right">R$ {{ number_format($summary['series']['somaVendas'][$i] ?? 0, 2, ',', '.') }}</td></tr>
                    @endforeach
                </table>
            </div></div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card"><div class="card-body">
                <h5>Top produtos</h5>
                <table class="table table-sm"><tr><th>Produto</th><th>Qtde</th><th>Valor</th></tr>
                    @forelse(($summary['overview']['top_produtos'] ?? []) as $item)
                    <tr><td>{{ $item['nome'] ?? '-' }}</td><td>{{ $item['quantidade'] ?? 0 }}</td><td>R$ {{ number_format($item['valor'] ?? 0, 2, ',', '.') }}</td></tr>
                    @empty
                    <tr><td colspan="3">Sem dados.</td></tr>
                    @endforelse
                </table>
            </div></div>
        </div>
        <div class="col-md-6">
            <div class="card"><div class="card-body">
                <h5>Top clientes</h5>
                <table class="table table-sm"><tr><th>Cliente</th><th>Qtde</th><th>Valor</th></tr>
                    @forelse(($summary['overview']['top_clientes'] ?? []) as $item)
                    <tr><td>{{ $item['nome'] ?? '-' }}</td><td>{{ $item['quantidade'] ?? 0 }}</td><td>R$ {{ number_format($item['valor'] ?? 0, 2, ',', '.') }}</td></tr>
                    @empty
                    <tr><td colspan="3">Sem dados.</td></tr>
                    @endforelse
                </table>
            </div></div>
        </div>
    </div>
</div>
@endsection
