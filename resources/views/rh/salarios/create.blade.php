@extends('default.layout',['title' => 'RH - Novo Reajuste'])
@section('content')
<div class="page-content">
    <div class="card">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0 text-uppercase">Novo reajuste salarial</h6>
                <a href="{{ route('rh.salarios.index') }}" class="btn btn-secondary">Voltar</a>
            </div>

            <form method="POST" action="{{ route('rh.salarios.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label">Funcionário</label>
                        <select class="form-select" name="funcionario_id" required>
                            <option value="">Selecione</option>
                            @foreach($funcionarios as $item)
                                <option value="{{ $item->id }}">{{ $item->nome }} — R$ {{ number_format((float)$item->salario,2,',','.') }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Novo salário</label>
                        <input type="text" class="form-control moeda" name="salario_novo" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Data</label>
                        <input type="date" class="form-control" name="data_movimentacao" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Descrição</label>
                        <input type="text" class="form-control" name="descricao" placeholder="Ex: reajuste anual, promoção, reenquadramento..." required>
                    </div>
                </div>

                <div class="mt-4">
                    <button class="btn btn-success">Salvar reajuste</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
