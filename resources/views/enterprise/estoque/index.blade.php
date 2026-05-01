@extends('default.layout')
@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <h4>Governança de Estoque</h4>
            <p class="text-muted">Empresa {{ $empresaId }} · módulo profissional de estoque com razão de movimentações.</p>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-3"><div class="card"><div class="card-body"><strong>Registros</strong><br>{{ $snapshot['registros'] }}</div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><strong>Saldo total</strong><br>{{ number_format($snapshot['saldo_total'], 4, ',', '.') }}</div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><strong>Produtos zerados</strong><br>{{ $snapshot['produtos_zerados'] }}</div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><strong>Abaixo do mínimo</strong><br>{{ $snapshot['abaixo_minimo'] }}</div></div></div>
    </div>

    <div class="card">
        <div class="card-header">Snapshot do estoque</div>
        <div class="card-body table-responsive">
            <table class="table table-sm table-striped">
                <thead>
                    <tr>
                        <th>Produto</th>
                        <th>Referência</th>
                        <th>Filial</th>
                        <th>Saldo</th>
                        <th>Mínimo</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($snapshot['ultimas_movimentacoes'] as $item)
                        <tr>
                            <td>{{ $item['produto'] ?? 'Produto #' . $item['produto_id'] }}</td>
                            <td>{{ $item['referencia'] ?? '-' }}</td>
                            <td>{{ $item['filial_id'] ?? 'Matriz' }}</td>
                            <td>{{ number_format($item['saldo'], 4, ',', '.') }}</td>
                            <td>{{ number_format($item['estoque_minimo'], 4, ',', '.') }}</td>
                            <td>
                                @if($item['abaixo_minimo'])
                                    <span class="badge badge-warning">Abaixo do mínimo</span>
                                @else
                                    <span class="badge badge-success">OK</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6">Nenhum registro disponível.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
