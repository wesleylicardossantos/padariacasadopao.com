@extends('default.layout',['title' => 'Portal do funcionário'])
@section('content')
<div class="page-content">
    <div class="card">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                <div>
                    <h4 class="mb-1">Portal do funcionário</h4>
                    <div class="text-muted">Histórico completo de holerites de {{ $funcionario->nome }}</div>
                    
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    @if($resumo['ultimo_holerite'])
                        <a target="_blank" href="{{ route('rh.portal_funcionario.pdf', $resumo['ultimo_holerite']->id) }}" class="btn btn-danger">
                            <i class="bx bxs-file-pdf"></i> Último holerite
                        </a>
                    @endif
                    <a href="{{ url('/graficos') }}" class="btn btn-light">
                        <i class="bx bx-arrow-back"></i> Voltar
                    </a>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card border shadow-none mb-0 h-100">
                        <div class="card-body">
                            <div class="text-muted">Total de holerites</div>
                            <h3 class="mb-0">{{ $resumo['total_holerites'] }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border shadow-none mb-0 h-100">
                        <div class="card-body">
                            <div class="text-muted">Total líquido no histórico</div>
                            <h3 class="mb-0 text-success">{{ __moeda($resumo['total_recebido']) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border shadow-none mb-0 h-100">
                        <div class="card-body">
                            <div class="text-muted">Última competência</div>
                            <h3 class="mb-0">
                                @if($resumo['ultimo_holerite'])
                                    {{ \App\Support\RHCompetenciaHelper::formatar($resumo['ultimo_holerite']->mes, $resumo['ultimo_holerite']->ano) }}
                                @else
                                    --
                                @endif
                            </h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">Mês</label>
                            <select name="mes" class="form-select">
                                <option value="">Todos</option>
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" @selected((int) request('mes') === $m)>{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Ano</label>
                            <select name="ano" class="form-select">
                                <option value="">Todos</option>
                                @foreach($competencias->pluck('ano')->unique() as $ano)
                                    <option value="{{ $ano }}" @selected((string) request('ano') === (string) $ano)>{{ $ano }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 d-flex gap-2">
                            <button type="submit" class="btn btn-primary"><i class="bx bx-filter-alt"></i> Filtrar</button>
                            <a href="{{ route('rh.portal_funcionario.index') }}" class="btn btn-outline-secondary">Limpar</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card border mb-4">
                <div class="card-header bg-light">
                    <strong>Histórico de holerites</strong>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Competência</th>
                                    <th>Valor líquido</th>
                                    <th>Forma de pagamento</th>
                                    <th>Observação</th>
                                    <th class="text-end">Ação</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($apuracoes as $apuracao)
                                    <tr>
                                        <td>{{ \App\Support\RHCompetenciaHelper::formatar($apuracao->mes, $apuracao->ano) }}</td>
                                        <td>{{ __moeda($apuracao->valor_final) }}</td>
                                        <td>{{ $apuracao->forma_pagamento ?: '--' }}</td>
                                        <td>{{ $apuracao->observacao ?: '--' }}</td>
                                        <td class="text-end">
                                            <a target="_blank" href="{{ route('rh.portal_funcionario.pdf', $apuracao->id) }}" class="btn btn-sm btn-danger">
                                                <i class="bx bxs-file-pdf"></i> Baixar PDF
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4">Nenhum holerite encontrado para os filtros informados.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if(method_exists($apuracoes, 'links'))
                    <div class="card-footer">
                        {{ $apuracoes->links() }}
                    </div>
                @endif
            </div>

            <div class="card border">
                <div class="card-header bg-light">
                    <strong>Histórico de envios por e-mail</strong>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Competência</th>
                                    <th>Status</th>
                                    <th>E-mail</th>
                                    <th>Tentativas</th>
                                    <th>Ocorrência</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($historicoEnvios as $envio)
                                    @php
                                        $envioBadge = [
                                            'enviado' => 'success',
                                            'falha' => 'danger',
                                            'sem_email' => 'dark',
                                            'processando' => 'warning text-dark',
                                            'cancelado' => 'dark',
                                            'fila' => 'secondary',
                                        ][$envio->status] ?? 'secondary';
                                    @endphp
                                    <tr>
                                        <td>{{ str_pad($envio->mes, 2, '0', STR_PAD_LEFT) }}/{{ $envio->ano }}</td>
                                        <td><span class="badge bg-{{ $envioBadge }}">{{ ucfirst(str_replace('_', ' ', $envio->status)) }}</span></td>
                                        <td>{{ $envio->email ?: 'Não informado' }}</td>
                                        <td>{{ $envio->tentativas }}</td>
                                        <td>
                                            @if($envio->ultima_falha)
                                                <span class="text-danger">{{ $envio->ultima_falha }}</span>
                                            @elseif($envio->enviado_em)
                                                Enviado em {{ \Carbon\Carbon::parse($envio->enviado_em)->format('d/m/Y H:i') }}
                                            @elseif($envio->ultima_tentativa_em)
                                                Tentativa em {{ \Carbon\Carbon::parse($envio->ultima_tentativa_em)->format('d/m/Y H:i') }}
                                            @else
                                                --
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4">Ainda não há histórico de envio para este funcionário.</td>
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