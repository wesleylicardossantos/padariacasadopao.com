@extends('default.layout',['title' => 'Painel de Manutenção'])
@section('content')
<div class="page-content">
    <div class="row">
        <div class="col-12">
            <div class="alert alert-warning border-0 bg-warning alert-dismissible fade show py-2">
                <div class="d-flex align-items-center">
                    <div class="font-35 text-dark"><i class="bx bx-shield-quarter"></i></div>
                    <div class="ms-3">
                        <h6 class="mb-0 text-dark">Painel administrativo oculto</h6>
                        <div class="text-dark">
                            Acesso restrito para administrador. Caminho oculto atual: <strong>{{ $maintenancePath }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card radius-10 border-start border-0 border-4 border-primary">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div>
                            <p class="mb-0 text-secondary">Funcionários</p>
                            <h4 class="my-1 text-primary">{{ $stats['funcionarios_total'] }}</h4>
                            <p class="mb-0 font-13">Ativos: {{ $stats['funcionarios_ativos'] ?? 0 }} | Inativos: {{ $stats['funcionarios_inativos'] ?? 0 }}</p>
                        </div>
                        <div class="widgets-icons-2 rounded-circle bg-gradient-ohhappiness text-white ms-auto"><i class='bx bx-user'></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card radius-10 border-start border-0 border-4 border-success">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div>
                            <p class="mb-0 text-secondary">Histórico de funcionários</p>
                            <h4 class="my-1 text-success">{{ $stats['historico_funcionarios'] }}</h4>
                            <p class="mb-0 font-13">Registros de alterações do módulo</p>
                        </div>
                        <div class="widgets-icons-2 rounded-circle bg-gradient-bloody text-white ms-auto"><i class='bx bx-history'></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card radius-10 border-start border-0 border-4 border-danger">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div>
                            <p class="mb-0 text-secondary">Auditoria / Logs</p>
                            <h4 class="my-1 text-danger">{{ $stats['log_usuarios'] + $stats['auditoria_exclusoes'] }}</h4>
                            <p class="mb-0 font-13">Logs: {{ $stats['log_usuarios'] }} | Auditoria: {{ $stats['auditoria_exclusoes'] }}</p>
                        </div>
                        <div class="widgets-icons-2 rounded-circle bg-gradient-burning text-white ms-auto"><i class='bx bx-file'></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card">
                <div class="card-body">
                    <h5 class="mb-3">Ferramentas de manutenção</h5>

                    @if(session('flash_sucesso'))
                    <div class="alert alert-success">{{ session('flash_sucesso') }}</div>
                    @endif

                    @if(session('flash_erro'))
                    <div class="alert alert-danger">{{ session('flash_erro') }}</div>
                    @endif

                    @if(session('maintenance_output'))
                    <div class="alert alert-light border">
                        <strong>Saída do comando:</strong>
                        <pre class="mb-0 mt-2" style="white-space: pre-wrap;">{{ session('maintenance_output') }}</pre>
                    </div>
                    @endif

                    <div class="row g-2">
                        <div class="col-md-6">
                            <form method="POST" action="{{ route('adminMaintenance.run') }}">
                                @csrf
                                <input type="hidden" name="action" value="optimize_clear">
                                <button class="btn btn-dark w-100" type="submit" onclick="return confirm('Executar limpeza geral do sistema?')">
                                    <i class="bx bx-refresh"></i> Limpar tudo
                                </button>
                            </form>
                        </div>
                        <div class="col-md-6">
                            <form method="POST" action="{{ route('adminMaintenance.run') }}">
                                @csrf
                                <input type="hidden" name="action" value="cache_clear">
                                <button class="btn btn-primary w-100" type="submit">
                                    <i class="bx bx-rocket"></i> Limpar cache
                                </button>
                            </form>
                        </div>
                        <div class="col-md-6">
                            <form method="POST" action="{{ route('adminMaintenance.run') }}">
                                @csrf
                                <input type="hidden" name="action" value="config_clear">
                                <button class="btn btn-warning w-100 text-dark" type="submit">
                                    <i class="bx bx-cog"></i> Limpar config
                                </button>
                            </form>
                        </div>
                        <div class="col-md-6">
                            <form method="POST" action="{{ route('adminMaintenance.run') }}">
                                @csrf
                                <input type="hidden" name="action" value="view_clear">
                                <button class="btn btn-info w-100 text-dark" type="submit">
                                    <i class="bx bx-layer"></i> Limpar views
                                </button>
                            </form>
                        </div>
                        <div class="col-md-6">
                            <form method="POST" action="{{ route('adminMaintenance.run') }}">
                                @csrf
                                <input type="hidden" name="action" value="route_clear">
                                <button class="btn btn-danger w-100" type="submit">
                                    <i class="bx bx-map"></i> Limpar rotas
                                </button>
                            </form>
                        </div>
                        <div class="col-md-6">
                            <a href="{{ route('adminMaintenance.index') }}" class="btn btn-secondary w-100">
                                <i class="bx bx-reset"></i> Atualizar painel
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card">
                <div class="card-body">
                    <h5 class="mb-3">Acesso rápido aos módulos</h5>
                    <div class="list-group">
                        <a href="/funcionarios" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            Funcionários
                            <span class="badge bg-primary rounded-pill">Abrir</span>
                        </a>
                        <a href="/frenteCaixa/list" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            Vendas Frente de Caixa
                            <span class="badge bg-primary rounded-pill">Abrir</span>
                        </a>
                        <a href="/fluxoCaixa" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            Fluxo de Caixa
                            <span class="badge bg-primary rounded-pill">Abrir</span>
                        </a>
                        <a href="/conta-pagar" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            Contas a Pagar
                            <span class="badge bg-primary rounded-pill">Abrir</span>
                        </a>
                    </div>

                    <hr>

                    <h6 class="mb-2">Próximas melhorias sugeridas</h6>
                    <ul class="mb-0">
                        <li>auditoria nas exclusões do PDV</li>
                        <li>relatório consolidado do fluxo de caixa</li>
                        <li>comissões e histórico salarial em funcionários</li>
                        <li>backup administrativo com download controlado</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
