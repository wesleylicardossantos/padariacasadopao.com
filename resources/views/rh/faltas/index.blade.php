@extends('default.layout',['title' => 'RH - Absenteísmo'])
@section('content')
<div class="page-content">
    <div class="card">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <div>
                    <h6 class="mb-0 text-uppercase">RH - Absenteísmo</h6>
                    <small class="text-muted">Controle de faltas, atrasos, atestados e saídas antecipadas.</small>
                </div>
                <a href="{{ route('rh.faltas.create') }}" class="btn btn-success">Nova ocorrência</a>
            </div>

            @if(!empty($semTabela))
                <div class="alert alert-danger">Tabela de absenteísmo ainda não instalada. Execute o SQL do patch RH V4.</div>
            @endif

            <form method="GET" action="{{ route('rh.faltas.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4"><label class="form-label">Funcionário</label><input type="text" class="form-control" name="funcionario" value="{{ $funcionario ?? '' }}"></div>
                    <div class="col-md-3">
                        <label class="form-label">Tipo</label>
                        <select class="form-select" name="tipo">
                            <option value="">Todos</option>
                            @foreach(\App\Models\RHFalta::tipos() as $key => $label)
                                <option value="{{ $key }}" @if(($tipo ?? '') == $key) selected @endif>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2"><button class="btn btn-primary w-100">Filtrar</button></div>
                </div>
            </form>

            <hr>

            <div class="table-responsive">
                <table class="table table-striped">
                    <thead><tr><th>Funcionário</th><th>Tipo</th><th>Data</th><th>Horas</th><th>Descrição</th></tr></thead>
                    <tbody>
                        @if(!empty($semTabela))
                            <tr><td colspan="5" class="text-center">Estrutura RH V4 pendente.</td></tr>
                        @else
                            @forelse($data as $item)
                            <tr>
                                <td>{{ optional($item->funcionario)->nome }}</td>
                                <td>{{ \App\Models\RHFalta::tipos()[$item->tipo] ?? ucfirst($item->tipo) }}</td>
                                <td>{{ \Carbon\Carbon::parse($item->data_referencia)->format('d/m/Y') }}</td>
                                <td>{{ $item->quantidade_horas ?? '-' }}</td>
                                <td>{{ $item->descricao }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center">Nenhuma ocorrência encontrada.</td></tr>
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
