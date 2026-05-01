@extends('default.layout',['title' => 'RH - Nova Movimentação'])
@section('content')
<div class="page-content">
    <div class="card">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0 text-uppercase">Nova movimentação RH</h6>
                <a href="{{ route('rh.movimentacoes.index') }}" class="btn btn-secondary">Voltar</a>
            </div>

            <form method="POST" action="{{ route('rh.movimentacoes.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Funcionário</label>
                        <select class="form-select" name="funcionario_id" required>
                            <option value="">Selecione</option>
                            @foreach($funcionarios as $item)
                                <option value="{{ $item->id }}">{{ $item->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tipo</label>
                        <select class="form-select" name="tipo" required>
                            <option value="">Selecione</option>
                            @foreach($tipos as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Data</label>
                        <input type="date" class="form-control" name="data_movimentacao" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Descrição</label>
                        <input type="text" class="form-control" name="descricao" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Cargo anterior</label>
                        <input type="text" class="form-control" name="cargo_anterior">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Cargo novo</label>
                        <input type="text" class="form-control" name="cargo_novo">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Valor anterior</label>
                        <input type="text" class="form-control moeda" name="valor_anterior">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Valor novo</label>
                        <input type="text" class="form-control moeda" name="valor_novo">
                    </div>
                </div>

                <div class="mt-4">
                    <button class="btn btn-success">Salvar movimentação</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
