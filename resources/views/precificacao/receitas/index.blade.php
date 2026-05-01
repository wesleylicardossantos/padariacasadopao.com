@extends('default.layout',['title' => 'Fichas Técnicas'])
@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <h4 class="mb-1">Fichas Técnicas</h4>
                <div class="text-muted">Receitas e estruturas de custo usadas para formar preço e produção.</div>
            </div>
            <div class="d-flex gap-2"><a href="{{ route('precificacao.receitas.create') }}" class="btn btn-success"><i class="fa fa-plus"></i> Criar</a><form action="{{ route('precificacao.kit-padaria.instalar') }}" method="POST" onsubmit="return confirm('Implantar ou atualizar o kit avançado de padaria?')">@csrf<button type="submit" class="btn btn-primary">Implantar kit avançado</button></form><a href="{{ route('precificacao.index') }}" class="btn btn-light">Voltar ao Painel</a></div>
        </div>

        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

        @if(!$estruturaOk)
            <div class="alert alert-warning">A tabela <strong>precificacao_receitas</strong> não foi encontrada no banco.</div>
        @endif


        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <div class="fw-semibold mb-1">Kit padrão padaria implementado no projeto</div>
                <div class="text-muted">Este atalho recria insumos e fichas técnicas de broa, pão, coxinha, bolo e biscoito, além das bases de massa e recheio.</div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small text-uppercase">Total de fichas</div><div class="fs-3 fw-bold">{{ $receitas->count() }}</div></div></div></div>
            <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small text-uppercase">Com itens</div><div class="fs-3 fw-bold">{{ $itensOk ? $receitas->where('itens_count','>',0)->count() : 0 }}</div></div></div></div>
            <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small text-uppercase">Estrutura de itens</div><div class="fs-3 fw-bold">{{ $itensOk ? 'OK' : 'Parcial' }}</div></div></div></div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Ficha técnica</th>
                                <th>Rendimento</th>
                                <th>Itens</th>
                                <th>Status</th>
                                <th class="text-end">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($receitas as $receita)
                                <tr class="receita-row" data-href="{{ route('precificacao.receitas.edit', $receita->id) }}" style="cursor:pointer;">
                                    <td class="fw-semibold">{{ $receita->nome }}</td>
                                    <td>
                                        {{ $colunas['rendimento'] ? number_format((float)($receita->rendimento ?? 0), 2, ',', '.') : '0,00' }}
                                        {{ $colunas['unidade_rendimento'] ? ($receita->unidade_rendimento ?? '') : '' }}
                                    </td>
                                    <td>{{ $itensOk ? ($receita->itens_count ?? 0) : '-' }}</td>
                                    <td>
                                        @php($status = $colunas['status'] ? ($receita->status ?? 'ativo') : 'ativo')
                                        <span class="badge {{ $status === 'inativo' ? 'bg-secondary' : 'bg-success' }}">{{ ucfirst($status) }}</span>
                                    </td>
                                    <td class="text-end text-nowrap" onclick="event.stopPropagation();">
                                        <form action="{{ route('precificacao.receitas.duplicate', $receita->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-light" title="Criar cópia"><i class="fa fa-plus text-success"></i></button>
                                        </form>
                                        <a href="{{ route('precificacao.receitas.edit', $receita->id) }}" class="btn btn-sm btn-light" title="Editar"><i class="fa fa-pencil text-primary"></i></a>
                                        <form action="{{ route('precificacao.receitas.destroy', $receita->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Excluir esta ficha técnica?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-light" title="Excluir"><i class="fa fa-trash text-danger"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">Nenhuma ficha técnica encontrada.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.receita-row').forEach(function(row){
    row.addEventListener('click', function(e){
      if (e.target.closest('a, button, form')) return;
      window.location = row.dataset.href;
    });
  });
});
</script>
@endpush
