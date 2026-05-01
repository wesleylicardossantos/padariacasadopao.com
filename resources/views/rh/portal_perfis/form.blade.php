@extends('default.layout',['title' => isset($item->id) ? 'Editar perfil do portal' : 'Novo perfil do portal'])
@section('content')
<div class="page-content">
    <div class="card">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="mb-0">{{ isset($item->id) ? 'Editar perfil do portal' : 'Novo perfil do portal' }}</h5>
                <a href="{{ route('rh.portal_perfis.index') }}" class="btn btn-secondary"><i class="bx bx-arrow-back"></i> Voltar</a>
            </div>

            <form method="POST" action="{{ $action }}">
                @csrf
                @if($method !== 'POST')
                    @method($method)
                @endif

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nome do perfil</label>
                        <input type="text" name="nome" class="form-control" value="{{ old('nome', $item->nome) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" id="ativo" name="ativo" value="1" {{ old('ativo', $item->ativo) ? 'checked' : '' }}>
                            <label class="form-check-label" for="ativo">Perfil ativo</label>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Descrição</label>
                        <textarea name="descricao" class="form-control" rows="3">{{ old('descricao', $item->descricao) }}</textarea>
                    </div>
                </div>

                <hr class="my-4">
                <h6 class="mb-3">Permissões do perfil</h6>
                <div class="row g-3">
                    @foreach($permissoesDisponiveis as $chave => $rotulo)
                        <div class="col-md-4">
                            <div class="form-check border rounded p-3 h-100">
                                <input class="form-check-input" type="checkbox" id="perm_{{ md5($chave) }}" name="permissoes[]" value="{{ $chave }}" {{ in_array($chave, old('permissoes', $item->permissoes ?? [])) ? 'checked' : '' }}>
                                <label class="form-check-label" for="perm_{{ md5($chave) }}">
                                    <strong>{{ $rotulo }}</strong><br>
                                    <small class="text-muted">{{ $chave }}</small>
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bx bx-save"></i> Salvar perfil</button>
                    <a href="{{ route('rh.portal_perfis.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
