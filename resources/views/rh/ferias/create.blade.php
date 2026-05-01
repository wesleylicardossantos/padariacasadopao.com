@extends('default.layout',['title' => 'RH - Nova Férias'])
@section('content')
<div class="page-content">
    <div class="card">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0 text-uppercase">Nova programação de férias</h6>
                <a href="/rh/ferias" class="btn btn-secondary">Voltar</a>
            </div>

            @if(!empty($schemaLegado))
                <div class="alert alert-warning">
                    Estrutura antiga de férias detectada no banco. Os campos de período aquisitivo não serão gravados nesta base.
                </div>
            @endif

            <form method="POST" action="/rh/ferias">
                @csrf
                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label">Funcionário</label>
                        <select class="form-select" name="funcionario_id" required>
                            <option value="">Selecione</option>
                            @foreach($funcionarios as $item)
                                <option value="{{ $item->id }}">{{ $item->nome }}</option>
                            @endforeach
                        </select>
                    </div>

                    @if(empty($schemaLegado))
                    <div class="col-md-2">
                        <label class="form-label">Dias</label>
                        <input type="number" class="form-control" name="dias" value="30" required>
                    </div>
                    @endif

                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <option value="programada">Programada</option>
                            <option value="pendente">Pendente</option>
                            <option value="gozo">Em gozo</option>
                            <option value="concluida">Concluída</option>
                        </select>
                    </div>

                    @if(empty($schemaLegado))
                    <div class="col-md-3">
                        <label class="form-label">Período aquisitivo início</label>
                        <input type="date" class="form-control" name="periodo_aquisitivo_inicio" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Período aquisitivo fim</label>
                        <input type="date" class="form-control" name="periodo_aquisitivo_fim" required>
                    </div>
                    @endif

                    <div class="col-md-3">
                        <label class="form-label">Início do gozo</label>
                        <input type="date" class="form-control" name="data_inicio" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Fim do gozo</label>
                        <input type="date" class="form-control" name="data_fim" required>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Observação</label>
                        <input type="text" class="form-control" name="observacao">
                    </div>
                </div>
                <div class="mt-4">
                    <button class="btn btn-success">Salvar férias</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
