@extends('rh.portal_funcionario.layout_externo',['title' => 'Consulta de produtos'])
@section('content')
<div class="page-content">
    <div class="card">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                <div>
                    <h4 class="mb-1">Consulta de produtos</h4>
                    <div class="text-muted">Produtos disponíveis para consulta de {{ $funcionario->nome }}</div>
                    <div class="small text-secondary mt-1">Listagem somente leitura com ID, nome e valor de venda.</div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('rh.portal_externo.dashboard') }}" class="btn btn-light">
                        <i class="bx bx-arrow-back"></i> Voltar ao portal
                    </a>
                    <a href="{{ route('rh.portal_externo.logout') }}" class="btn btn-outline-secondary">
                        <i class="bx bx-log-out"></i> Sair
                    </a>
                </div>
            </div>

            <div class="card border mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3 align-items-end">
                        <div class="col-md-8">
                            <label class="form-label">Pesquisar por ID ou nome do produto</label>
                            <input type="text" name="busca" value="{{ request('busca') }}" class="form-control" placeholder="Ex.: 15 ou Caneta Azul">
                        </div>
                        <div class="col-md-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary"><i class="bx bx-search"></i> Consultar</button>
                            <a href="{{ route('rh.portal_externo.produtos') }}" class="btn btn-outline-secondary">Limpar</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card border shadow-none mb-0 h-100">
                        <div class="card-body">
                            <div class="text-muted">Produtos nesta consulta</div>
                            <h3 class="mb-0">{{ method_exists($produtos, 'total') ? $produtos->total() : count($produtos) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border shadow-none mb-0 h-100">
                        <div class="card-body">
                            <div class="text-muted">Acesso ao relatório</div>
                            <h3 class="mb-0 text-success">Liberado</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border shadow-none mb-0 h-100">
                        <div class="card-body">
                            <div class="text-muted">Perfil de consulta</div>
                            <h3 class="mb-0">Somente leitura</h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border">
                <div class="card-header bg-light">
                    <strong>Relatório de produtos cadastrados</strong>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th style="width:120px;">ID do produto</th>
                                    <th>Nome do produto</th>
                                    <th class="text-end" style="width:180px;">Valor de venda</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($produtos as $produto)
                                    <tr>
                                        <td>{{ $produto->id }}</td>
                                        <td>{{ $produto->nome }}</td>
                                        <td class="text-end">{{ __moeda($produto->valor_venda) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-4">Nenhum produto encontrado para o filtro informado.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if(method_exists($produtos, 'links'))
                    <div class="card-footer">
                        {{ $produtos->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
