@extends('default.layout',['title' => 'RH - Movimentações'])
@section('content')
<style>.rh-card{border:1px solid #e9edf5;border-radius:14px;box-shadow:0 8px 24px rgba(15,23,42,.04)}</style>
<div class="page-content">
    <div class="card rh-card">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <div>
                    <h6 class="mb-0 text-uppercase">RH - Movimentações</h6>
                    <small class="text-muted">Promoções, reajustes, mudanças de cargo, demissões e ocorrências.</small>
                </div>
                <a href="{{ route('rh.movimentacoes.create') }}" class="btn btn-success"><i class="bx bx-plus"></i> Nova movimentação</a>
            </div>

            @if(!empty($semTabela))
                <div class="alert alert-danger">A estrutura RH V2 ainda não foi instalada. Execute o SQL do patch e recarregue.</div>
            @endif

            <form method="GET" action="{{ route('rh.movimentacoes.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Pesquisar por nome</label>
                        <input type="text" class="form-control" name="nome" value="{{ $nome ?? '' }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tipo</label>
                        <select class="form-select" name="tipo">
                            <option value="">Todos</option>
                            @foreach(\App\Models\RHMovimentacao::tipos() as $key => $label)
                                <option value="{{ $key }}" @if(($tipo ?? '') == $key) selected @endif>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary w-100">Filtrar</button>
                    </div>
                </div>
            </form>

            <hr>

            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Funcionário</th>
                            <th>Tipo</th>
                            <th>Descrição</th>
                            <th>Cargo</th>
                            <th>Valores</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(!empty($semTabela))
                        <tr><td colspan="7" class="text-center">Estrutura RH V2 pendente.</td></tr>
                        @else
                            @forelse($data as $item)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($item->data_movimentacao)->format('d/m/Y') }}</td>
                                <td>{{ optional($item->funcionario)->nome }}</td>
                                <td>{{ \App\Models\RHMovimentacao::tipos()[$item->tipo] ?? ucfirst($item->tipo) }}</td>
                                <td>{{ $item->descricao }}</td>
                                <td>{{ $item->cargo_anterior ?: '-' }}{{ $item->cargo_novo ? ' → '.$item->cargo_novo : '' }}</td>
                                <td>
                                    @if($item->valor_anterior !== null || $item->valor_novo !== null)
                                        R$ {{ number_format((float)$item->valor_anterior,2,',','.') }} → R$ {{ number_format((float)$item->valor_novo,2,',','.') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td><a href="{{ route('rh.movimentacoes.edit', $item->id) }}" class="btn btn-sm btn-warning">Editar</a></td>
                            </tr>
                            @empty
                            <tr><td colspan="7" class="text-center">Nenhuma movimentação encontrada.</td></tr>
                            @endforelse
                        @endif
                    </tbody>
                </table>
            </div>

            @if(empty($semTabela))
            {!! $data->appends(request()->all())->links() !!}
            @endif
        </div>
    </div>
</div>
@endsection
