@extends('default.layout',['title' => 'Detalhe do Funcionário'])
@section('content')
<div class="page-content">
    <div class="card">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0 text-uppercase">Ficha do Funcionário</h6>
                <div>
                    <a href="{{ route('funcionarios.toggleStatus', $item->id) }}" class="btn {{ !isset($item->ativo) || $item->ativo ? 'btn-danger' : 'btn-success' }}" onclick="return confirm('Deseja realmente {{ !isset($item->ativo) || $item->ativo ? 'inativar' : 'reativar' }} este funcionário?')">
                        <i class="bx {{ !isset($item->ativo) || $item->ativo ? 'bx-user-x' : 'bx-user-check' }}"></i>
                        {{ !isset($item->ativo) || $item->ativo ? 'Inativar' : 'Reativar' }}
                    </a>
                    <a href="{{ route('funcionarios.index') }}" class="btn btn-secondary"><i class="bx bx-arrow-back"></i> Voltar</a>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="card border">
                        <div class="card-body">
                            <h6 class="mb-3">Dados pessoais</h6>
                            <p class="mb-1"><strong>Nome:</strong> {{ $item->nome }}</p>
                            <p class="mb-1"><strong>CPF:</strong> {{ $item->cpf }}</p>
                            <p class="mb-1"><strong>RG:</strong> {{ $item->rg }}</p>
                            <p class="mb-1"><strong>Telefone:</strong> {{ $item->telefone }}</p>
                            <p class="mb-1"><strong>Celular:</strong> {{ $item->celular }}</p>
                            <p class="mb-1"><strong>Email:</strong> {{ $item->email ?: '-' }}</p>
                            <p class="mb-1"><strong>Endereço:</strong> {{ $item->rua }}, {{ $item->numero }} - {{ $item->bairro }}</p>
                            <p class="mb-1"><strong>Cidade:</strong> {{ optional($item->cidade)->nome ?? '-' }}</p>
                            <p class="mb-0"><strong>Status:</strong> <span class="badge {{ !isset($item->ativo) || $item->ativo ? 'bg-success' : 'bg-danger' }}">{{ !isset($item->ativo) || $item->ativo ? 'Ativo' : 'Inativo' }}</span></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border">
                        <div class="card-body">
                            <h6 class="mb-3">Dados profissionais</h6>
                            <p class="mb-1"><strong>Função:</strong> {{ $item->funcao ?: '-' }}</p>
                            <p class="mb-1"><strong>Salário:</strong> {{ __moeda($item->salario) }}</p>
                            <p class="mb-1"><strong>Comissão:</strong> {{ __moeda($item->percentual_comissao ?? 0) }}</p>
                            <p class="mb-1"><strong>Data de admissão:</strong> {{ $item->fichaAdmissao && $item->fichaAdmissao->data_admissao ? __data_pt($item->fichaAdmissao->data_admissao, false) : __data_pt($item->data_registro, false) }}</p>
                            <p class="mb-1"><strong>Data de cadastro:</strong> {{ __data_pt($item->created_at) }}</p>
                            <p class="mb-0"><strong>Usuário vinculado:</strong> {{ optional($item->usuario)->nome ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            @if($item->fichaAdmissao)
            <div class="card border mt-3">
                <div class="card-body">
                    <h6 class="mb-3">Ficha completa</h6>
                    <div class="row">
                        <div class="col-md-4"><strong>Nome do pai:</strong> {{ $item->fichaAdmissao->nome_pai ?: '-' }}</div>
                        <div class="col-md-4"><strong>Nome da mãe:</strong> {{ $item->fichaAdmissao->nome_mae ?: '-' }}</div>
                        <div class="col-md-4"><strong>Naturalidade:</strong> {{ $item->fichaAdmissao->naturalidade ?: '-' }}</div>
                        <div class="col-md-3 mt-2"><strong>Data de nascimento:</strong> {{ $item->fichaAdmissao->data_nascimento ? __data_pt($item->fichaAdmissao->data_nascimento, false) : '-' }}</div>
                        <div class="col-md-3 mt-2"><strong>Estado civil:</strong> {{ $item->fichaAdmissao->estado_civil ?: '-' }}</div>
                        <div class="col-md-3 mt-2"><strong>Escolaridade:</strong> {{ $item->fichaAdmissao->grau_instrucao ?: '-' }}</div>
                        <div class="col-md-3 mt-2"><strong>Banco:</strong> {{ $item->fichaAdmissao->banco ?: '-' }}</div>
                        <div class="col-md-12 mt-2"><strong>Observações:</strong> {{ $item->fichaAdmissao->observacoes ?: '-' }}</div>
                    </div>
                </div>
            </div>
            @endif

            <div class="card border mt-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                        <h6 class="mb-0">Portal do funcionário</h6>
                        <span class="badge {{ ($acessoPortal && $acessoPortal->ativo) ? 'bg-success' : 'bg-secondary' }}">{{ ($acessoPortal && $acessoPortal->ativo) ? 'Portal ativo' : 'Portal desativado' }}</span>
                    </div>

                    <form method="POST" action="{{ route('rh.portal_externo.configurar', $item->id) }}" class="row g-3 align-items-end">
                        @csrf
                        <div class="col-md-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="portal-ativo" name="ativo" value="1" {{ ($acessoPortal->ativo ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label" for="portal-ativo">Portal ativo</label>
                            </div>
                            <small class="text-muted">Permite login do funcionário no portal externo.</small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Perfil RBAC do portal</label>
                            <select name="perfil_id" class="form-select">
                                <option value="">Sem perfil definido</option>
                                @foreach(($perfisPortal ?? collect()) as $perfilPortal)
                                    <option value="{{ $perfilPortal->id }}" @selected((int) old('perfil_id', $acessoPortal->perfil_id ?? 0) === (int) $perfilPortal->id)>
                                        {{ $perfilPortal->nome }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Selecione um perfil pronto ou mantenha sem perfil para usar apenas permissões extras.</small>
                        </div>
                        <div class="col-md-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="portal-produtos" name="pode_ver_relatorio_produtos_extra" value="1" {{ in_array('produtos.visualizar', $acessoPortal?->permissoes_extras ?? []) || ($acessoPortal->pode_ver_relatorio_produtos ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label" for="portal-produtos">Extra: consulta de produtos</label>
                            </div>
                            <small class="text-muted">Acrescenta a permissão de produtos acima do perfil selecionado.</small>
                            <input type="hidden" name="pode_ver_relatorio_produtos" value="{{ ($acessoPortal->pode_ver_relatorio_produtos ?? false) ? 1 : 0 }}">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100"><i class="bx bx-save"></i> Salvar portal</button>
                        </div>
                        <div class="col-12">
                            <a href="{{ route('rh.portal_perfis.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bx bx-shield-quarter"></i> Gerenciar perfis RBAC</a>
                        </div>
                    </form>

                    <div class="row g-3 mt-1">
                        <div class="col-md-3"><strong>Último login:</strong> {{ !empty($acessoPortal?->ultimo_login_em) ? \Carbon\Carbon::parse($acessoPortal->ultimo_login_em)->format('d/m/Y H:i') : '-' }}</div>
                        <div class="col-md-3"><strong>IP do último login:</strong> {{ $acessoPortal->ultimo_login_ip ?? '-' }}</div>
                        <div class="col-md-3"><strong>Perfil atual:</strong> {{ $acessoPortal?->perfil?->nome ?? 'Sem perfil' }}</div>
                        <div class="col-md-3"><strong>Acesso de produtos:</strong> {{ $acessoPortal && $acessoPortal->hasPermission('produtos.visualizar') ? 'Liberado' : 'Bloqueado' }}</div>
                        <div class="col-12">
                            <strong>Permissões efetivas:</strong>
                            <div class="d-flex flex-wrap gap-1 mt-2">
                                @forelse(($acessoPortal?->permissoesEfetivas() ?? ['dashboard.visualizar','holerites.visualizar']) as $permissaoEfetiva)
                                    <span class="badge bg-info text-dark">{{ $permissaoEfetiva }}</span>
                                @empty
                                    <span class="text-muted">Nenhuma permissão definida.</span>
                                @endforelse
                            </div>
                        </div>
                    </div></div>
                </div>
            </div>

            <div class="card border mt-3">
                <div class="card-body">
                    <h6 class="mb-3">Histórico de alterações</h6>
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Tipo</th>
                                    <th>Descrição</th>
                                    <th>Anterior</th>
                                    <th>Novo</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($item->historicos as $historico)
                                <tr>
                                    <td>{{ __data_pt($historico->created_at) }}</td>
                                    <td>{{ ucfirst($historico->tipo) }}</td>
                                    <td>{{ $historico->descricao }}</td>
                                    <td>{{ $historico->valor_anterior !== null ? __moeda($historico->valor_anterior) : '-' }}</td>
                                    <td>{{ $historico->valor_novo !== null ? __moeda($historico->valor_novo) : '-' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center">Nenhum histórico encontrado</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
