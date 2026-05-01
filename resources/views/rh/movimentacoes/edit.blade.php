@extends('default.layout',['title' => 'RH - Editar Movimentação'])
@section('content')
<div class="page-content">
    <div class="card">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0 text-uppercase">Editar movimentação RH</h6>
                <a href="{{ route('rh.movimentacoes.index') }}" class="btn btn-secondary">Voltar</a>
            </div>

            <form method="POST" action="{{ route('rh.movimentacoes.update', $item->id) }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Funcionário</label>
                        <select class="form-select" name="funcionario_id" required>
                            @foreach($funcionarios as $funcionario)
                                <option value="{{ $funcionario->id }}" @if($item->funcionario_id == $funcionario->id) selected @endif>{{ $funcionario->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tipo</label>
                        <select class="form-select" name="tipo" required>
                            @foreach($tipos as $key => $label)
                                <option value="{{ $key }}" @if($item->tipo == $key) selected @endif>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Data</label>
                        <input type="date" class="form-control" name="data_movimentacao" value="{{ \Carbon\Carbon::parse($item->data_movimentacao)->format('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Descrição</label>
                        <input type="text" class="form-control" name="descricao" value="{{ $item->descricao }}" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Cargo anterior</label>
                        <input type="text" class="form-control" name="cargo_anterior" value="{{ $item->cargo_anterior }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Cargo novo</label>
                        <input type="text" class="form-control" name="cargo_novo" value="{{ $item->cargo_novo }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Valor anterior</label>
                        <input type="text" class="form-control moeda" name="valor_anterior" value="{{ $item->valor_anterior !== null ? number_format((float)$item->valor_anterior,2,',','.') : '' }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Valor novo</label>
                        <input type="text" class="form-control moeda" name="valor_novo" value="{{ $item->valor_novo !== null ? number_format((float)$item->valor_novo,2,',','.') : '' }}">
                    </div>
                </div>

                <div class="mt-4">
                    <button class="btn btn-success">Salvar alterações</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
