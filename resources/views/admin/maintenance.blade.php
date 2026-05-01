@extends('default.layout',['title' => 'Painel Admin Pro'])
@section('content')
<div class="page-content">
    <div class="card">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                <div>
                    <h4 class="mb-0">Painel Admin Pro</h4>
                    <small class="text-muted">Central de manutenção, auditoria e atalhos do ERP.</small>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.clearAll') }}" class="btn btn-warning"><i class="bx bx-refresh"></i> Limpar cache</a>
                    <a href="{{ route('admin.backup') }}" class="btn btn-dark"><i class="bx bx-download"></i> Backup banco</a>
                    <a href="{{ route('admin.audit') }}" class="btn btn-primary"><i class="bx bx-list-ul"></i> Auditoria</a>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card radius-10 bg-light">
                        <div class="card-body">
                            <small class="text-muted">Vendas hoje</small>
                            <h4 class="mb-0">R$ {{ __moeda($vendasHoje ?? 0) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card radius-10 bg-light">
                        <div class="card-body">
                            <small class="text-muted">Contas pendentes</small>
                            <h4 class="mb-0">{{ $contasPendentes ?? 0 }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card radius-10 bg-light">
                        <div class="card-body">
                            <small class="text-muted">Caixas abertos</small>
                            <h4 class="mb-0">{{ $caixasAbertos ?? 0 }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card radius-10 bg-light">
                        <div class="card-body">
                            <small class="text-muted">Usuários ativos</small>
                            <h4 class="mb-0">{{ $usuariosAtivos ?? 0 }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-header bg-transparent"><strong>Logs recentes</strong></div>
                        <div class="card-body table-responsive">
                            <table class="table table-sm table-striped">
                                <thead>
                                    <tr>
                                        <th>Data</th>
                                        <th>Usuário</th>
                                        <th>Ação</th>
                                        <th>Tabela</th>
                                        <th>Registro</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($logsRecentes as $item)
                                    <tr>
                                        <td>{{ __data_pt($item->created_at) }}</td>
                                        <td>{{ optional($item->usuario)->nome ?? '---' }}</td>
                                        <td>{{ ucfirst($item->tipo) }}</td>
                                        <td>{{ $item->tabela }}</td>
                                        <td>{{ $item->registro_id }}</td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="5" class="text-center">Sem logs recentes</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                            <strong>Erros recentes</strong>
                            <a href="{{ route('errosLog.index') }}" class="btn btn-sm btn-outline-danger">Ver todos</a>
                        </div>
                        <div class="card-body table-responsive">
                            <table class="table table-sm table-striped">
                                <thead>
                                    <tr>
                                        <th>Data</th>
                                        <th>Linha</th>
                                        <th>Arquivo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($errosRecentes as $item)
                                    <tr>
                                        <td>{{ __data_pt($item->created_at) }}</td>
                                        <td>{{ $item->linha }}</td>
                                        <td>{{ \Illuminate\Support\Str::limit($item->arquivo, 60) }}</td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="3" class="text-center">Sem erros recentes</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <hr class="my-4">

            <div class="d-flex flex-wrap gap-2">
                <a href="/funcionarios" class="btn btn-outline-primary">Funcionários</a>
                <a href="/frontBox" class="btn btn-outline-primary">Frente de caixa</a>
                <a href="/fluxo_caixa" class="btn btn-outline-primary">Fluxo de caixa</a>
                <a href="/conta_pagar" class="btn btn-outline-primary">Contas a pagar</a>
            </div>
        </div>
    </div>
</div>
@endsection
