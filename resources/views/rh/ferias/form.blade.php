<div class="row g-3">
    <div class="col-md-6">{!! Form::select('funcionario_id', 'Funcionário', ['' => 'Selecione'] + $funcionarios->pluck('nome','id')->all())->attrs(['class' => 'select2'])->required() !!}</div>
    <div class="col-md-3">{!! Form::date('data_inicio', 'Data início')->required() !!}</div>
    <div class="col-md-3">{!! Form::date('data_fim', 'Data fim')->required() !!}</div>
    <div class="col-md-4">{!! Form::select('status', 'Status', ['PROGRAMADA' => 'Programada', 'EM_ANDAMENTO' => 'Em andamento', 'CONCLUIDA' => 'Concluída'])->required() !!}</div>
    <div class="col-md-8">{!! Form::text('observacao', 'Observação') !!}</div>
</div>
<div class="mt-3"><button class="btn btn-primary">Salvar</button></div>
