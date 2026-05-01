@extends('default.layout',['title' => 'RH - Template Jurídico'])
@section('content')
<div class="page-content">
    <div class="card"><div class="card-body p-4">
        <div class="d-flex justify-content-between mb-3 flex-wrap gap-2">
            <div>
                <h5 class="mb-0">{{ $template->exists ? 'Editar template jurídico' : 'Novo template jurídico' }}</h5>
                <small class="text-muted">Variáveis disponíveis: @{{empresa_nome}}, @{{funcionario_nome}}, @{{funcionario_cpf}}, @{{funcionario_cargo}}, @{{funcionario_salario}}, @{{funcionario_data_admissao}}, @{{motivo_documento}}, @{{observacoes_adicionais}}</small>
            </div>
            <a class="btn btn-secondary" href="{{ route('rh.documentos.templates.index') }}">Voltar</a>
        </div>

        <form method="POST" action="{{ $action }}">
            @csrf
            @if($method !== 'POST') @method($method) @endif
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Nome</label><input type="text" name="nome" class="form-control" value="{{ old('nome', $template->nome) }}" required></div>
                <div class="col-md-3"><label class="form-label">Categoria</label><input type="text" name="categoria" class="form-control" value="{{ old('categoria', $template->categoria) }}" required></div>
                <div class="col-md-3"><label class="form-label">Tipo</label><input type="text" name="tipo_documento" class="form-control" value="{{ old('tipo_documento', $template->tipo_documento) }}" required></div>
                <div class="col-md-8"><label class="form-label">Descrição</label><input type="text" name="descricao" class="form-control" value="{{ old('descricao', $template->descricao) }}"></div>
                <div class="col-md-2"><label class="form-label">Versão</label><input type="text" name="versao" class="form-control" value="{{ old('versao', $template->versao ?: '1.0') }}"></div>
                <div class="col-md-1 d-flex align-items-end"><div class="form-check"><input class="form-check-input" type="checkbox" name="usa_ia" value="1" {{ old('usa_ia', $template->usa_ia) ? 'checked' : '' }}><label class="form-check-label">IA</label></div></div>
                <div class="col-md-1 d-flex align-items-end"><div class="form-check"><input class="form-check-input" type="checkbox" name="ativo" value="1" {{ old('ativo', $template->ativo ?? true) ? 'checked' : '' }}><label class="form-check-label">Ativo</label></div></div>
                <div class="col-md-12"><label class="form-label">Conteúdo HTML</label><textarea name="conteudo_html" rows="18" class="form-control" required>{{ old('conteudo_html', $template->conteudo_html) }}</textarea></div>
            </div>
            <div class="mt-3"><button class="btn btn-primary">Salvar template</button></div>
        </form>
    </div></div>
</div>
@endsection
