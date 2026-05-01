@extends('default.layout',['title' => 'RH - Nova Ocorrência'])
@section('content')
<div class="page-content">
    <div class="card">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0 text-uppercase">Nova ocorrência de absenteísmo</h6>
                <a href="{{ route('rh.faltas.index') }}" class="btn btn-secondary">Voltar</a>
            </div>

            <form method="POST" action="{{ route('rh.faltas.store') }}">
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
                        <input type="date" class="form-control" name="data_referencia" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Horas</label>
                        <input type="text" class="form-control" name="quantidade_horas" placeholder="Ex: 02:00">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Descrição</label>
                        <input type="text" class="form-control" name="descricao">
                    </div>
                </div>
                <div class="mt-4">
                    <button class="btn btn-success">Salvar ocorrência</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
