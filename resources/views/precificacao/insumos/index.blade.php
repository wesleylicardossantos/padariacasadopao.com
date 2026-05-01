@extends('default.layout',['title' => 'Insumos'])
@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <h4 class="mb-1">Insumos</h4>
                <div class="text-muted">Base de matérias-primas usada nas fichas técnicas e na produção.</div>
            </div>
            <div class="d-flex gap-2"><form action="{{ route('precificacao.kit-padaria.instalar') }}" method="POST" onsubmit="return confirm('Implantar ou atualizar o kit avançado de padaria?')">@csrf<button type="submit" class="btn btn-primary">Implantar kit avançado</button></form><a href="{{ route('precificacao.index') }}" class="btn btn-light">Voltar ao Painel</a></div>
        </div>

        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

        @if(!$estruturaOk)
            <div class="alert alert-warning">A tabela <strong>precificacao_insumos</strong> não foi encontrada no banco.</div>
        @endif


        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <div class="fw-semibold mb-1">Kit padrão padaria implementado no projeto</div>
                <div class="text-muted">Este atalho recria insumos e fichas técnicas de broa, pão, coxinha, bolo e biscoito, além das bases de massa e recheio.</div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small text-uppercase">Total de insumos</div><div class="fs-3 fw-bold">{{ $insumos->count() }}</div></div></div></div>
            <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small text-uppercase">Com vínculo legado</div><div class="fs-3 fw-bold">{{ $colunas['produto_legado_id'] ? $insumos->whereNotNull('produto_legado_id')->count() : 0 }}</div></div></div></div>
            <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small text-uppercase">Ativos</div><div class="fs-3 fw-bold">{{ $colunas['ativo'] ? $insumos->where('ativo',1)->count() : $insumos->count() }}</div></div></div></div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Insumo</th>
                                <th>Categoria</th>
                                <th>Unidade</th>
                                <th>Custo unitário</th>
                                <th>Vínculo legado</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($insumos as $insumo)
                                <tr>
                                    <td class="fw-semibold">{{ $insumo->nome }}</td>
                                    <td>{{ $colunas['categoria'] ? ($insumo->categoria ?? '-') : '-' }}</td>
                                    <td>{{ $colunas['unidade'] ? ($insumo->unidade ?? '-') : '-' }}</td>
                                    <td>R$ {{ number_format((float)($colunas['custo_unitario'] ? ($insumo->custo_unitario ?? 0) : 0), 4, ',', '.') }}</td>
                                    <td>
                                        @if($colunas['produto_legado_id'] && $insumo->produto_legado_id)
                                            <span class="badge bg-success-subtle text-success">#{{ $insumo->produto_legado_id }}</span>
                                        @else
                                            <span class="badge bg-warning-subtle text-warning">Pendente</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($colunas['ativo'] && (int)$insumo->ativo !== 1)
                                            <span class="badge bg-secondary">Inativo</span>
                                        @else
                                            <span class="badge bg-primary">Ativo</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted py-4">Nenhum insumo encontrado.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
