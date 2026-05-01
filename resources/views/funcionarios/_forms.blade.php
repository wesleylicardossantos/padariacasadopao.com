<div class="row g-3">

    <div class="col-md-4">
        {!! Form::text('nome', 'Nome')->required() !!}
    </div>

    <div class="col-md-3">
        {!! Form::tel('cpf', 'CPF')->attrs(['class' => 'cpf'])->required()  !!}
    </div>

    <div class="col-md-2">
        {!! Form::tel('rg', 'RG')->required()->attrs(['class' => 'ie_rg']) !!}
    </div>

    <div class="col-md-3">
        {!! Form::text('email', 'Email')->type('email') !!}
    </div>

    <div class="col-md-2">
        {!! Form::tel('celular', 'Celular')->required() ->attrs(['class' => 'fone']) !!}
    </div>

    <div class="col-md-2">
        {!! Form::tel('telefone', 'Telefone')->required() ->attrs(['class' => 'fone']) !!}
    </div>

    <div class="col-md-2">
        {!! Form::date('data_registro', 'Data do registro')->required()  !!}
    </div>

    <div class="col-md-2">
        {!! Form::tel('percentual_comissao', '% Comissão')->attrs(['class' => 'perc']) !!}
    </div>

    <div class="col-md-2">
        {!! Form::tel('salario', 'Salário')->required() ->attrs(['class' => 'moeda']) !!}
    </div>

    <div class="col-md-3">
        <label class="form-label">Função</label>
        <input type="text" name="funcao" class="form-control" required value="{{ old('funcao', $item->funcao ?? '') }}" data-funcao-search="1" list="lista_funcao_oficial" placeholder="Digite a função oficial conforme CBO/eSocial">
        <datalist id="lista_funcao_oficial"></datalist>
        <small class="text-muted">Função padronizada a partir da CBO, conforme descrição usada no eSocial.</small>
    </div>

    <div class="col-md-3">
        {!! Form::select('usuario_id', 'Usuário (opcional)', [null => 'Selecione' ] + $usuarios->pluck('nome', 'id')->all())->attrs([
            'class' => 'select2',
        ]) !!}
    </div>

    <hr class="mt-4">

    <h5>Endereço</h5>

    <div class="col-md-4">
        {!! Form::text('rua', 'Rua')->required()  !!}
    </div>
    <div class="col-md-2">
        {!! Form::tel('numero', 'Número')->required()->attrs(['data-mask' => '00000000']) !!}
    </div>

    <div class="col-md-2">
        {!! Form::text('bairro', 'Bairro')->required()  !!}
    </div>

    <div class="col-md-3">
        {!! Form::select('cidade_id', 'Cidade')->required()->attrs(['class' => 'select2'])->options(isset($item) ? [$item->cidade_id => $item->cidade->info] : []) !!}
    </div>


<hr class="mt-4">

@php
    $ficha = isset($item) ? $item->fichaAdmissao : null;
@endphp

<hr class="mt-4">
<h6>Identificação complementar</h6>
<div class="row g-3">
    <div class="col-md-3">
        <label class="form-label">Matrícula</label>
        <input type="text" name="matricula" class="form-control" value="{{ old('matricula', $ficha->matricula ?? '') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">Matrícula social</label>
        <input type="text" name="matricula_social" class="form-control" value="{{ old('matricula_social', $ficha->matricula_social ?? '') }}">
    </div>
</div>

<hr class="mt-4">
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Nome do Pai</label>
        <input type="text" name="nome_pai" class="form-control" value="{{ old('nome_pai', $ficha->nome_pai ?? '') }}">
    </div>
    <div class="col-md-6">
        <label class="form-label">Nome da Mãe</label>
        <input type="text" name="nome_mae" class="form-control" value="{{ old('nome_mae', $ficha->nome_mae ?? '') }}">
    </div>

    <div class="col-md-4">
        <label class="form-label">Naturalidade</label>
        <input type="text" name="naturalidade" class="form-control" value="{{ old('naturalidade', $ficha->naturalidade ?? '') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">Nacionalidade</label>
        <input type="text" name="nacionalidade" class="form-control" value="{{ old('nacionalidade', $ficha->nacionalidade ?? '') }}">
    </div>
    <div class="col-md-2">
        <label class="form-label">U.F.</label>
        <input type="text" name="uf_naturalidade" class="form-control" value="{{ old('uf_naturalidade', $ficha->uf_naturalidade ?? '') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">Data de Nascimento</label>
        <input type="date" name="data_nascimento" class="form-control" value="{{ old('data_nascimento', $ficha->data_nascimento ?? '') }}">
    </div>
    <div class="col-md-3 d-flex align-items-end">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="deficiencia_fisica" value="1" {{ old('deficiencia_fisica', ($ficha->deficiencia_fisica ?? 0)) ? 'checked' : '' }}>
            <label class="form-check-label">Defic. físico: SIM</label>
        </div>
    </div>

    <div class="col-md-4">
        <label class="form-label">Raça/Cor</label>
        <input type="text" name="raca_cor" class="form-control" value="{{ old('raca_cor', $ficha->raca_cor ?? '') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Sexo</label>
        <select name="sexo" class="form-control">
            @php $sexoVal = old('sexo', $ficha->sexo ?? ''); @endphp
            <option value="">Selecione</option>
            <option value="MASCULINO" {{ $sexoVal == 'MASCULINO' ? 'selected' : '' }}>Masculino</option>
            <option value="FEMININO" {{ $sexoVal == 'FEMININO' ? 'selected' : '' }}>Feminino</option>
            <option value="OUTRO" {{ $sexoVal == 'OUTRO' ? 'selected' : '' }}>Outro</option>
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Estado Civil</label>
        <input type="text" name="estado_civil" class="form-control" value="{{ old('estado_civil', $ficha->estado_civil ?? '') }}">
    </div>

    <div class="col-md-6">
        <label class="form-label">Grau de Instrução</label>
        <input type="text" name="grau_instrucao" class="form-control" value="{{ old('grau_instrucao', $ficha->grau_instrucao ?? '') }}">
    </div>
</div>

<hr class="mt-4">
<h6>CTPS / PIS / RG / CNH / Título</h6>

<div class="row g-3">
    <div class="col-md-3">
        <label class="form-label">Número CTPS</label>
        <input type="text" name="ctps_numero" class="form-control" value="{{ old('ctps_numero', $ficha->ctps_numero ?? '') }}">
    </div>
    <div class="col-md-2">
        <label class="form-label">Série</label>
        <input type="text" name="ctps_serie" class="form-control" value="{{ old('ctps_serie', $ficha->ctps_serie ?? '') }}">
    </div>
    <div class="col-md-1">
        <label class="form-label">UF</label>
        <input type="text" name="ctps_uf" class="form-control" value="{{ old('ctps_uf', $ficha->ctps_uf ?? '') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">Data Expedição CTPS</label>
        <input type="date" name="ctps_data_expedicao" class="form-control" value="{{ old('ctps_data_expedicao', $ficha->ctps_data_expedicao ?? '') }}">
    </div>

    <div class="col-md-3">
        <label class="form-label">Número PIS</label>
        <input type="text" name="pis_numero" class="form-control" value="{{ old('pis_numero', $ficha->pis_numero ?? '') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">Data Cad. PIS</label>
        <input type="date" name="pis_data_cadastro" class="form-control" value="{{ old('pis_data_cadastro', $ficha->pis_data_cadastro ?? '') }}">
    </div>

    <div class="col-md-3">
        <label class="form-label">Órgão Emissor (RG)</label>
        <input type="text" name="rg_orgao_emissor" class="form-control" value="{{ old('rg_orgao_emissor', $ficha->rg_orgao_emissor ?? '') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">Data Emissão (RG)</label>
        <input type="date" name="rg_data_emissao" class="form-control" value="{{ old('rg_data_emissao', $ficha->rg_data_emissao ?? '') }}">
    </div>

    <div class="col-md-4">
        <label class="form-label">Título Eleitor</label>
        <input type="text" name="titulo_eleitor" class="form-control" value="{{ old('titulo_eleitor', $ficha->titulo_eleitor ?? '') }}">
    </div>
    <div class="col-md-2">
        <label class="form-label">Zona</label>
        <input type="text" name="titulo_zona" class="form-control" value="{{ old('titulo_zona', $ficha->titulo_zona ?? '') }}">
    </div>
    <div class="col-md-2">
        <label class="form-label">Seção</label>
        <input type="text" name="titulo_secao" class="form-control" value="{{ old('titulo_secao', $ficha->titulo_secao ?? '') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Certificado de reservista</label>
        <input type="text" name="certificado_reservista" class="form-control" value="{{ old('certificado_reservista', $ficha->certificado_reservista ?? '') }}">
    </div>

    <div class="col-md-3">
        <label class="form-label">CNH</label>
        <input type="text" name="cnh_numero" class="form-control" value="{{ old('cnh_numero', $ficha->cnh_numero ?? '') }}">
    </div>
    <div class="col-md-2">
        <label class="form-label">Categoria</label>
        <input type="text" name="cnh_categoria" class="form-control" value="{{ old('cnh_categoria', $ficha->cnh_categoria ?? '') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">Validade</label>
        <input type="date" name="cnh_validade" class="form-control" value="{{ old('cnh_validade', $ficha->cnh_validade ?? '') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">1ª Habilitação</label>
        <input type="date" name="cnh_primeira_habilitacao" class="form-control" value="{{ old('cnh_primeira_habilitacao', $ficha->cnh_primeira_habilitacao ?? '') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">Tipo habilitação</label>
        <input type="text" name="tipo_habilitacao" class="form-control" value="{{ old('tipo_habilitacao', $ficha->tipo_habilitacao ?? '') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">Registro profissional</label>
        <input type="text" name="registro_profissional" class="form-control" value="{{ old('registro_profissional', $ficha->registro_profissional ?? '') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">Órgão do registro</label>
        <input type="text" name="orgao_registro_profissional" class="form-control" value="{{ old('orgao_registro_profissional', $ficha->orgao_registro_profissional ?? '') }}">
    </div>
</div>

<hr class="mt-4">
<h6>Dependentes</h6>
<div class="row g-3">
    <div class="col-md-3 d-flex align-items-end">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="possui_dependentes" value="1" {{ old('possui_dependentes', ($ficha->possui_dependentes ?? 0)) ? 'checked' : '' }}>
            <label class="form-check-label">Possui dependentes</label>
        </div>
    </div>
    <div class="col-md-9">
        <label class="form-label">Observação/Detalhes (nome, data nasc., local nasc., parentesco)</label>
        <input type="text" name="dependentes_texto" class="form-control" value="{{ old('dependentes_texto', $ficha->dependentes_texto ?? '') }}">
    </div>
</div>

<hr class="mt-4">
<h6>Vale Transporte</h6>
<div class="row g-3">
    <div class="col-md-2 d-flex align-items-end">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="vale_transporte" value="1" {{ old('vale_transporte', ($ficha->vale_transporte ?? 0)) ? 'checked' : '' }}>
            <label class="form-check-label">SIM</label>
        </div>
    </div>
    <div class="col-md-5">
        <label class="form-label">Linhas</label>
        <input type="text" name="vt_linhas" class="form-control" value="{{ old('vt_linhas', $ficha->vt_linhas ?? '') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">Preço da passagem</label>
        <input type="number" step="0.01" name="vt_preco_passagem" class="form-control" value="{{ old('vt_preco_passagem', $ficha->vt_preco_passagem ?? '') }}">
    </div>
    <div class="col-md-2">
        <label class="form-label">Passagens/dia</label>
        <input type="number" name="vt_quantidade_dia" class="form-control" value="{{ old('vt_quantidade_dia', $ficha->vt_quantidade_dia ?? '') }}">
    </div>
</div>

<hr class="mt-4">
<h6>Horário de Trabalho</h6>
<div class="row g-3">
    <div class="col-md-3">
        <label class="form-label">Seg a Sex Entrada</label>
        <input type="time" name="horario_seg_sex_entrada" class="form-control" value="{{ old('horario_seg_sex_entrada', $ficha->horario_seg_sex_entrada ?? '') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">Seg a Sex Saída</label>
        <input type="time" name="horario_seg_sex_saida" class="form-control" value="{{ old('horario_seg_sex_saida', $ficha->horario_seg_sex_saida ?? '') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">Intervalo Início</label>
        <input type="time" name="horario_seg_sex_intervalo_inicio" class="form-control" value="{{ old('horario_seg_sex_intervalo_inicio', $ficha->horario_seg_sex_intervalo_inicio ?? '') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">Intervalo Fim</label>
        <input type="time" name="horario_seg_sex_intervalo_fim" class="form-control" value="{{ old('horario_seg_sex_intervalo_fim', $ficha->horario_seg_sex_intervalo_fim ?? '') }}">
    </div>

    <div class="col-md-3">
        <label class="form-label">Sábado Entrada</label>
        <input type="time" name="horario_sabado_entrada" class="form-control" value="{{ old('horario_sabado_entrada', $ficha->horario_sabado_entrada ?? '') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">Sábado Saída</label>
        <input type="time" name="horario_sabado_saida" class="form-control" value="{{ old('horario_sabado_saida', $ficha->horario_sabado_saida ?? '') }}">
    </div>
    <div class="col-md-6 d-flex align-items-end">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="nao_trabalha_sabado" value="1" {{ old('nao_trabalha_sabado', ($ficha->nao_trabalha_sabado ?? 0)) ? 'checked' : '' }}>
            <label class="form-check-label">Não trabalha aos sábados</label>
        </div>
    </div>
</div>

<hr class="mt-4">
<h6>Admissão / Experiência / Banco</h6>
<div class="row g-3">
    <div class="col-md-3">
        <label class="form-label">Data Admissão</label>
        <input type="date" name="data_admissao" class="form-control" value="{{ old('data_admissao', $ficha->data_admissao ?? '') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">Exame Médico Admissional</label>
        <input type="date" name="data_exame_admissional" class="form-control" value="{{ old('data_exame_admissional', $ficha->data_exame_admissional ?? '') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">Data de opção FGTS</label>
        <input type="date" name="data_opcao_fgts" class="form-control" value="{{ old('data_opcao_fgts', $ficha->data_opcao_fgts ?? '') }}">
    </div>
    <div class="col-md-3 d-flex align-items-end">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="contrato_experiencia" value="1" {{ old('contrato_experiencia', ($ficha->contrato_experiencia ?? 0)) ? 'checked' : '' }}>
            <label class="form-check-label">Contrato de experiência</label>
        </div>
        <div class="ms-3" style="width: 100%;">
            <label class="form-label mb-0">Tipo (30+30 / 45+45 / 60 dias)</label>
            <input type="text" name="experiencia_tipo" class="form-control" value="{{ old('experiencia_tipo', $ficha->experiencia_tipo ?? '') }}">
        </div>
    </div>

    <div class="col-md-4">
        <label class="form-label">Forma de pagamento</label>
        <input type="text" name="forma_pagamento" class="form-control" value="{{ old('forma_pagamento', $ficha->forma_pagamento ?? 'MENSAL') }}" placeholder="Ex.: MENSAL">
    </div>
    <div class="col-md-4">
        <label class="form-label">Indicativo de admissão</label>
        <input type="text" name="indicativo_admissao" list="lista_indicativo_admissao" class="form-control" value="{{ old('indicativo_admissao', $ficha->indicativo_admissao ?? '') }}" placeholder="Selecione pela tabela oficial do eSocial">
        <datalist id="lista_indicativo_admissao">
            @foreach(($officialReferences['indicativosAdmissao'] ?? []) as $indicativo)
                <option value="{{ $indicativo->codigo }} - {{ $indicativo->descricao }}"></option>
            @endforeach
        </datalist>
        <small class="text-muted">Valores oficiais do eSocial.</small>
    </div>
    <div class="col-md-4">
        <label class="form-label">Nº processo trabalhista</label>
        <input type="text" name="numero_processo_trabalhista" class="form-control" value="{{ old('numero_processo_trabalhista', $ficha->numero_processo_trabalhista ?? '') }}">
    </div>

    <div class="col-md-6">
        <label class="form-label">Categoria trabalhador</label>
        <input type="text" name="categoria_trabalhador" list="lista_categoria_trabalhador" class="form-control" value="{{ old('categoria_trabalhador', $ficha->categoria_trabalhador ?? '') }}" placeholder="Selecione pela tabela oficial do eSocial">
        <datalist id="lista_categoria_trabalhador">
            @foreach(($officialReferences['categorias'] ?? []) as $categoria)
                <option value="{{ $categoria->codigo }} - {{ $categoria->descricao }}"></option>
            @endforeach
        </datalist>
        <small class="text-muted">Base oficial do eSocial.</small>
    </div>
    <div class="col-md-6">
        <label class="form-label">Tipo contrato de trabalho</label>
        <input type="text" name="tipo_contrato_trabalho" list="lista_tipo_contrato_trabalho" class="form-control" value="{{ old('tipo_contrato_trabalho', $ficha->tipo_contrato_trabalho ?? '') }}" placeholder="Selecione pela tabela oficial do eSocial">
        <datalist id="lista_tipo_contrato_trabalho">
            @foreach(($officialReferences['tiposContrato'] ?? []) as $tipoContrato)
                <option value="{{ $tipoContrato->codigo }} - {{ $tipoContrato->descricao }}"></option>
            @endforeach
        </datalist>
        <small class="text-muted">Valores oficiais do eSocial.</small>
    </div>

    <div class="col-md-4">
        <label class="form-label">Natureza da atividade</label>
        <input type="text" name="natureza_atividade" list="lista_natureza_atividade" class="form-control" value="{{ old('natureza_atividade', $ficha->natureza_atividade ?? '') }}" placeholder="Selecione pela tabela oficial do eSocial">
        <datalist id="lista_natureza_atividade">
            @foreach(($officialReferences['naturezasAtividade'] ?? []) as $natureza)
                <option value="{{ $natureza->codigo }} - {{ $natureza->descricao }}"></option>
            @endforeach
        </datalist>
        <small class="text-muted">Valores oficiais do eSocial.</small>
    </div>
    <div class="col-md-4">
        <label class="form-label">Departamento</label>
        <input type="text" name="departamento" list="lista_departamento" class="form-control" value="{{ old('departamento', $ficha->departamento ?? '') }}" data-departamento-input="1" placeholder="Departamento interno sugerido automaticamente">
        <datalist id="lista_departamento">
            @foreach(($officialReferences['departamentos'] ?? []) as $departamento)
                <option value="{{ $departamento->descricao }}"></option>
            @endforeach
        </datalist>
        <small class="text-muted">Departamento é interno do ERP; o sistema sugere com base na função/CBO.</small>
    </div>
    <div class="col-md-4">
        <label class="form-label">CBO</label>
        <input type="text" name="cbo" class="form-control" value="{{ old('cbo', $ficha->cbo ?? '') }}" data-cbo-search="1" list="lista_cbo_oficial" placeholder="Digite código ou ocupação oficial">
        <datalist id="lista_cbo_oficial"></datalist>
        <small class="text-muted">Busca automática na base oficial da CBO.</small>
    </div>

    <div class="col-md-3">
        <label class="form-label">Descanso semanal</label>
        <input type="text" name="descanso_semanal" class="form-control" value="{{ old('descanso_semanal', $ficha->descanso_semanal ?? '') }}" placeholder="Ex.: Domingo">
    </div>
    <div class="col-md-3">
        <label class="form-label">Horas/mês</label>
        <input type="number" step="0.01" name="horas_mes" class="form-control" value="{{ old('horas_mes', $ficha->horas_mes ?? '') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">Horas/semana</label>
        <input type="number" step="0.01" name="horas_semana" class="form-control" value="{{ old('horas_semana', $ficha->horas_semana ?? '') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">Conta Salário</label>
        <input type="text" name="conta_salario" class="form-control" value="{{ old('conta_salario', $ficha->conta_salario ?? '') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">Agência</label>
        <input type="text" name="agencia" class="form-control" value="{{ old('agencia', $ficha->agencia ?? '') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">Banco</label>
        <input type="text" name="banco" class="form-control" value="{{ old('banco', $ficha->banco ?? '') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">Ficha preenchida por</label>
        <input type="text" name="ficha_preenchida_por" class="form-control" value="{{ old('ficha_preenchida_por', $ficha->ficha_preenchida_por ?? '') }}">
    </div>

    <div class="col-md-3">
        <label class="form-label">Dependentes salário família</label>
        <input type="number" min="0" name="dependentes_salario_familia" class="form-control" value="{{ old('dependentes_salario_familia', $ficha->dependentes_salario_familia ?? '') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">Dependentes IRRF</label>
        <input type="number" min="0" name="dependentes_irrf" class="form-control" value="{{ old('dependentes_irrf', $ficha->dependentes_irrf ?? '') }}">
    </div>
    <div class="col-md-6">
        <label class="form-label">Descrição salário variável</label>
        <input type="text" name="salario_variavel_descricao" class="form-control" value="{{ old('salario_variavel_descricao', $ficha->salario_variavel_descricao ?? '') }}">
    </div>

    <div class="col-md-12">
        <label class="form-label">Observações</label>
        <textarea name="observacoes" class="form-control" rows="3">{{ old('observacoes', $ficha->observacoes ?? '') }}</textarea>
    </div>
</div>


    <hr>

    <div class="col-12">
        <button type="submit" class="btn btn-primary px-5">Salvar</button>
    </div>
</div>

@section('js')
<script>
(function(){
    const cboInput = document.querySelector('[data-cbo-search="1"]');
    const cboList = document.getElementById('lista_cbo_oficial');
    const functionInput = document.querySelector('[data-funcao-search="1"]');
    const functionList = document.getElementById('lista_funcao_oficial');
    const departmentInput = document.querySelector('[data-departamento-input="1"]');
    let functionLookup = {};


    const guessDepartment = () => {
        if (!departmentInput || departmentInput.value.trim() !== '') return;

        const text = `${functionInput ? functionInput.value : ''} ${cboInput ? cboInput.value : ''}`.toLowerCase();
        const rules = [
            ['financeiro', 'FINANCEIRO'],
            ['contador', 'FINANCEIRO'],
            ['fiscal', 'FINANCEIRO'],
            ['recursos humanos', 'RH'],
            [' rh ', 'RH'],
            ['administrativo', 'ADMINISTRATIVO'],
            ['gerente', 'ADMINISTRATIVO'],
            ['vendas', 'COMERCIAL'],
            ['vendedor', 'COMERCIAL'],
            ['comercial', 'COMERCIAL'],
            ['marketing', 'COMERCIAL'],
            ['estoque', 'LOGISTICA'],
            ['logistica', 'LOGISTICA'],
            ['almoxarifado', 'LOGISTICA'],
            ['entrega', 'LOGISTICA'],
            ['motorista', 'LOGISTICA'],
            ['forneiro', 'OPERACIONAL'],
            ['cozinha', 'OPERACIONAL'],
            ['atendente', 'OPERACIONAL'],
            ['caixa', 'OPERACIONAL'],
            ['operador', 'OPERACIONAL'],
            ['operacional', 'OPERACIONAL'],
            ['producao', 'PRODUCAO'],
            ['suporte', 'SUPORTE'],
            ['tecnologia', 'TECNOLOGIA'],
            ['desenvolvedor', 'TECNOLOGIA'],
            ['programador', 'TECNOLOGIA'],
        ];

        const normalized = ` ${text.normalize('NFD').replace(/[\u0300-\u036f]/g, '')} `;
        for (const [needle, department] of rules) {
            if (normalized.includes(` ${needle} `) || normalized.includes(needle)) {
                departmentInput.value = department;
                break;
            }
        }
    };

    let functionTimer = null;
    const updateFunctionOptions = async () => {
        if (!functionInput || !functionList) return;
        const query = functionInput.value.trim();
        if (query.length < 2) return;

        try {
            const response = await fetch(`{{ url('/rh/oficial/funcoes') }}?q=${encodeURIComponent(query)}&limit=20`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const payload = await response.json();
            const items = Array.isArray(payload.data) ? payload.data : [];
            functionLookup = {};
            functionList.innerHTML = items.map(item => {
                functionLookup[item.descricao] = item.cbo_codigo || '';
                return `<option value="${item.descricao.replace(/"/g, '&quot;')}">${item.label}</option>`;
            }).join('');
        } catch (e) {
            console.warn('Falha ao carregar funções oficiais', e);
        }
    };

    let cboTimer = null;
    const updateCboOptions = async () => {
        if (!cboInput || !cboList) return;
        const query = cboInput.value.trim();
        if (query.length < 2) return;

        try {
            const response = await fetch(`{{ url('/rh/oficial/cbo') }}?q=${encodeURIComponent(query)}&limit=20`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const payload = await response.json();
            const items = Array.isArray(payload.data) ? payload.data : [];
            cboList.innerHTML = items.map(item => `<option value="${item.codigo}">${item.label}</option>`).join('');
        } catch (e) {
            console.warn('Falha ao carregar CBO oficial', e);
        }
    };

    if (cboInput) {
        cboInput.addEventListener('input', () => {
            clearTimeout(cboTimer);
            cboTimer = setTimeout(updateCboOptions, 250);
            guessDepartment();
        });
        cboInput.addEventListener('change', guessDepartment);
        if (cboInput.value.trim() !== '') {
            updateCboOptions();
        }
    }

    if (functionInput) {
        functionInput.addEventListener('input', () => {
            clearTimeout(functionTimer);
            functionTimer = setTimeout(updateFunctionOptions, 250);
            guessDepartment();
        });
        functionInput.addEventListener('change', () => {
            const cboFromFunction = functionLookup[functionInput.value.trim()] || '';
            if (cboInput && cboInput.value.trim() === '' && cboFromFunction !== '') {
                cboInput.value = cboFromFunction;
                updateCboOptions();
            }
            guessDepartment();
        });
        if (functionInput.value.trim() !== '') {
            updateFunctionOptions();
        }
    }

    guessDepartment();
})();
</script>
@endsection
