<div class="row g-3">
    <div class="col-md-6">{!! Form::select('funcionario_id', 'Funcionário', ['' => 'Selecione'] + $funcionarios->pluck('nome','id')->all())->attrs(['class' => 'select2'])->required() !!}</div>
    <div class="col-md-3">{!! Form::select('tipo', 'Tipo', ['ADVERTENCIA' => 'Advertência', 'ELOGIO' => 'Elogio', 'SUSPENSAO' => 'Suspensão', 'DESLIGAMENTO' => 'Desligamento', 'OUTRO' => 'Outro'])->required() !!}</div>
    <div class="col-md-3">{!! Form::date('data_ocorrencia', 'Data')->required() !!}</div>
    <div class="col-md-12">{!! Form::text('titulo', 'Título')->required() !!}</div>
    <div class="col-md-12">{!! Form::textarea('descricao', 'Descrição')->attrs(['rows' => 3]) !!}</div>
</div>
<div class="mt-3"><button class="btn btn-primary">Salvar</button></div>
