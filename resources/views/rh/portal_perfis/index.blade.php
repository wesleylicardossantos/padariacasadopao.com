@extends('default.layout',['title' => 'Perfis do portal'])
@section('content')
<div class="page-content">
    <div class="card">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                <div>
                    <h5 class="mb-1">Perfis do portal do funcionário</h5>
                    <div class="text-muted">Controle RBAC por perfil para o portal externo.</div>
                </div>
                <a href="{{ route('rh.portal_perfis.create') }}" class="btn btn-primary {{ !empty($semTabela) ? "disabled" : "" }}"><i class="bx bx-plus"></i> Novo perfil</a>
            </div>

            @if(!empty($semTabela))
                <div class="alert alert-warning">Tabela <strong>rh_portal_perfis</strong> não encontrada no banco. Execute a migration/SQL do módulo RH antes de usar o RBAC do portal.</div>
            @endif

            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>Perfil</th>
                            <th>Descrição</th>
                            <th>Permissões</th>
                            <th>Status</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($perfis as $perfil)
                            <tr>
                                <td>
                                    <strong>{{ $perfil->nome }}</strong>
                                    @if(is_null($perfil->empresa_id))
                                        <div><span class="badge bg-light text-dark border">Global</span></div>
                                    @endif
                                </td>
                                <td>{{ $perfil->descricao ?: '--' }}</td>
                                <td>
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach(($perfil->permissoes ?? []) as $permissao)
                                            <span class="badge bg-info text-dark">{{ $permissoesDisponiveis[$permissao] ?? $permissao }}</span>
                                        @endforeach
                                    </div>
                                </td>
                                <td>
                                    <span class="badge {{ $perfil->ativo ? 'bg-success' : 'bg-danger' }}">{{ $perfil->ativo ? 'Ativo' : 'Inativo' }}</span>
                                </td>
                                <td class="text-end text-nowrap">
                                    <a href="{{ route('rh.portal_perfis.edit', $perfil->id) }}" class="btn btn-sm btn-outline-primary"><i class="bx bx-edit"></i></a>
                                    @if(!is_null($perfil->empresa_id))
                                    <form method="POST" action="{{ route('rh.portal_perfis.destroy', $perfil->id) }}" class="d-inline" onsubmit="return confirm('Deseja remover este perfil do portal?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bx bx-trash"></i></button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center">Nenhum perfil cadastrado.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $perfis->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
