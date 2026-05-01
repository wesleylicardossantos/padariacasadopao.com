@extends('default.layout')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1">PDV Enterprise · Produção</h3>
            <small class="text-muted">Atualizado em {{ $updated_at }}</small>
        </div>
        <div>
            <a href="{{ route('enterprise.pdv.audit', ['empresa_id' => $empresa_id]) }}" class="btn btn-sm btn-outline-primary">API Audit</a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-2"><div class="card"><div class="card-body"><h6>Total</h6><h4>{{ $audit['total'] }}</h4></div></div></div>
        <div class="col-md-2"><div class="card"><div class="card-body"><h6>Sincronizados</h6><h4>{{ $audit['sincronizados'] }}</h4></div></div></div>
        <div class="col-md-2"><div class="card"><div class="card-body"><h6>Pendentes</h6><h4>{{ $audit['pendentes'] }}</h4></div></div></div>
        <div class="col-md-2"><div class="card"><div class="card-body"><h6>Com erro</h6><h4>{{ $audit['erro'] }}</h4></div></div></div>
        <div class="col-md-2"><div class="card"><div class="card-body"><h6>Tentativas abertas</h6><h4>{{ $audit['tentativas_abertas'] }}</h4></div></div></div>
        <div class="col-md-2"><div class="card"><div class="card-body"><h6>Health score</h6><h4>{{ number_format($audit['health_score'], 2, ',', '.') }}%</h4><small>{{ strtoupper($audit['status_operacao']) }}</small></div></div></div>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card"><div class="card-body">
                <h5>Itens pendentes</h5>
                <table class="table table-sm">
                    <tr><th>UUID</th><th>Status</th><th>Tentativas</th><th>Erro</th></tr>
                    @forelse($pending_items as $item)
                    <tr>
                        <td>{{ $item->uuid_local }}</td>
                        <td>{{ $item->status }}</td>
                        <td>{{ (int) ($item->tentativas ?? 0) }}</td>
                        <td>{{ strlen((string) $item->erro) > 60 ? substr((string) $item->erro, 0, 60).'...' : $item->erro }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4">Sem pendências.</td></tr>
                    @endforelse
                </table>
            </div></div>
        </div>
        <div class="col-md-6">
            <div class="card"><div class="card-body">
                <h5>Divergências críticas</h5>
                <table class="table table-sm">
                    <tr><th>UUID</th><th>Status</th><th>Tentativas</th><th>Venda caixa</th></tr>
                    @forelse($divergences as $item)
                    <tr>
                        <td>{{ $item['uuid_local'] }}</td>
                        <td>{{ $item['status'] }}</td>
                        <td>{{ $item['tentativas'] }}</td>
                        <td>{{ $item['venda_caixa_id'] ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4">Sem divergências críticas.</td></tr>
                    @endforelse
                </table>
            </div></div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card"><div class="card-body">
                <h5>Schema validado</h5>
                <div class="row">
                    @foreach($schema as $campo => $disponivel)
                        <div class="col-md-2 mb-2"><span class="badge {{ $disponivel ? 'badge-success' : 'badge-secondary' }}">{{ $campo }}: {{ $disponivel ? 'ok' : 'ausente' }}</span></div>
                    @endforeach
                </div>
            </div></div>
        </div>
    </div>
</div>
@endsection
