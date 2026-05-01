@extends('default.layout',['title' => $title])
@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <h4 class="mb-1">{{ $isEdit ? 'Editar Ficha Técnica' : 'Nova Ficha Técnica' }}</h4>
                <div class="text-muted">Cadastre os dados principais da ficha técnica. Os itens podem ser ajustados depois no fluxo do módulo.</div>
            </div>
            <a href="{{ route('precificacao.receitas.index') }}" class="btn btn-light">Voltar</a>
        </div>

        @if(!$estruturaOk)
            <div class="alert alert-warning">A tabela <strong>precificacao_receitas</strong> não foi encontrada no banco.</div>
        @else
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form action="{{ $formAction }}" method="POST">
                        @csrf
                        @if($method !== 'POST')
                            @method($method)
                        @endif
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nome da ficha técnica</label>
                                <input type="text" name="nome" class="form-control @error('nome') is-invalid @enderror" value="{{ old('nome', $receita->nome) }}" required>
                                @error('nome')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Rendimento</label>
                                <input type="number" step="0.01" name="rendimento" class="form-control @error('rendimento') is-invalid @enderror" value="{{ old('rendimento', $receita->rendimento) }}">
                                @error('rendimento')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Unidade</label>
                                <input type="text" name="unidade_rendimento" class="form-control @error('unidade_rendimento') is-invalid @enderror" value="{{ old('unidade_rendimento', $receita->unidade_rendimento) }}">
                                @error('unidade_rendimento')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select @error('status') is-invalid @enderror">
                                    <option value="ativo" @selected(old('status', $receita->status) === 'ativo')>Ativo</option>
                                    <option value="inativo" @selected(old('status', $receita->status) === 'inativo')>Inativo</option>
                                </select>
                                @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="mt-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Salvar alterações' : 'Criar ficha técnica' }}</button>
                            <a href="{{ route('precificacao.receitas.index') }}" class="btn btn-light">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
