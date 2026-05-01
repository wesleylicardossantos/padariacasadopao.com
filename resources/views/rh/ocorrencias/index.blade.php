@extends('default.layout',['title' => 'RH - Ocorrências'])
@section('content')
<div class="page-content">
    @if(!$hasTable)
    <div class="alert alert-warning">A tabela <strong>rh_ocorrencias</strong> ainda não existe. Execute o SQL do módulo RH.</div>
    @endif
    <div class="card"><div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div><h5 class="mb-0">Ocorrências</h5><small class="text-muted">Advertências, elogios, suspensões e registros internos.</small></div>
            <a class="btn btn-success" href="{{ route('rh.ocorrencias.create') }}">Nova ocorrência</a>
        </div>

        {!! Form::open()->fill(request()->all())->get() !!}
        <div class="row g-3 align-items-end">
            <div class="col-md-5">{!! Form::select('funcionario_id', 'Funcionário', ['' => 'Todos'] + $funcionarios->pluck('nome','id')->all())->attrs(['class' => 'select2']) !!}</div>
            <div class="col-md-3">{!! Form::select('tipo', 'Tipo', ['' => 'Todos', 'ADVERTENCIA' => 'Advertência', 'ELOGIO' => 'Elogio', 'SUSPENSAO' => 'Suspensão', 'DESLIGAMENTO' => 'Desligamento', 'OUTRO' => 'Outro']) !!}</div>
            <div class="col-md-4"><button class="btn btn-primary">Filtrar</button> <a class="btn btn-danger" href="{{ route('rh.ocorrencias.index') }}">Limpar</a></div>
        </div>
        {!! Form::close() !!}
        <hr>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead><tr><th>Funcionário</th><th>Tipo</th><th>Título</th><th>Descrição</th><th>Data</th><th>Ações</th></tr></thead>
                <tbody>
                @if($hasTable)
                    @forelse($data as $item)
                    <tr>
                        <td>{{ $item->funcionario_nome }}</td>
                        <td>{{ $item->tipo }}</td>
                        <td>{{ $item->titulo }}</td>
                        <td>{{ $item->descricao }}</td>
                        <td>{{ \Carbon\Carbon::parse($item->data_ocorrencia)->format('d/m/Y') }}</td>
                        <td>
                            <a class="btn btn-warning btn-sm" href="{{ route('rh.ocorrencias.edit', $item->id) }}"><i class="bx bx-edit"></i></a>
                            <a class="btn btn-danger btn-sm" href="{{ route('rh.ocorrencias.destroy', $item->id) }}" onclick="return confirm('Remover ocorrência?')"><i class="bx bx-trash"></i></a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted">Sem ocorrências.</td></tr>
                    @endforelse
                @else
                    <tr><td colspan="6" class="text-center text-muted">Módulo ainda não instalado no banco.</td></tr>
                @endif
                </tbody>
            </table>
        </div>
        @if($hasTable && method_exists($data, 'links'))
        {{ $data->appends(request()->all())->links() }}
        @endif
    </div></div>
</div>
@endsection
