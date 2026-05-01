@extends('default.layout',['title' => 'Mapeamentos'])
@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <h4 class="mb-1">Mapeamentos</h4>
                <div class="text-muted">Vínculo entre insumos da precificação e produtos do estoque legado.</div>
            </div>
            <a href="{{ route('precificacao.index') }}" class="btn btn-light">Voltar ao Painel</a>
        </div>

        @if(!$estruturaOk)
            <div class="alert alert-warning">A tabela <strong>precificacao_insumos</strong> não foi encontrada no banco.</div>
        @elseif(!$produtoIdOk)
            <div class="alert alert-warning">A coluna <strong>produto_legado_id</strong> ainda não existe em <strong>precificacao_insumos</strong>. Rode o SQL corretivo antes de usar esta tela plenamente.</div>
        @endif

        <div class="row g-3 mb-3">
            <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small text-uppercase">Insumos avaliados</div><div class="fs-3 fw-bold">{{ $insumos->count() }}</div></div></div></div>
            <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small text-uppercase">Vinculados</div><div class="fs-3 fw-bold">{{ $vinculados }}</div></div></div></div>
            <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small text-uppercase">Pendentes</div><div class="fs-3 fw-bold">{{ max($insumos->count() - $vinculados, 0) }}</div></div></div></div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Insumo</th>
                                <th>Produto legado vinculado</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($insumos as $insumo)
                                @php($produto = $produtoIdOk ? ($produtosMap[$insumo->produto_legado_id] ?? null) : null)
                                <tr>
                                    <td class="fw-semibold">{{ $insumo->nome }}</td>
                                    <td>{{ $produto?->nome ?? ($produtoIdOk && $insumo->produto_legado_id ? '#'.$insumo->produto_legado_id : '-') }}</td>
                                    <td>
                                        @if($produtoIdOk && $insumo->produto_legado_id)
                                            <span class="badge bg-success">Vinculado</span>
                                        @else
                                            <span class="badge bg-warning text-dark">Pendente</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted py-4">Nenhum insumo encontrado para mapear.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
