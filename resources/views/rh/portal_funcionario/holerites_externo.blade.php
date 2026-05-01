@extends('rh.portal_funcionario.layout_externo',['title' => 'Meus holerites'])
@section('content')
<div class="page-content">
    <div class="card">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                <div>
                    <h4 class="mb-1">Meus holerites</h4>
                    <div class="text-muted">Consulte, filtre e baixe seus holerites em PDF.</div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('rh.portal_externo.dashboard') }}" class="btn btn-light">
                        <i class="bx bx-arrow-back"></i> Voltar ao portal
                    </a>
                    <a href="{{ route('rh.portal_externo.logout') }}" class="btn btn-outline-secondary">
                        <i class="bx bx-log-out"></i> Sair
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
                            <div class="text-muted">Total líquido acumulado</div>
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
                                @foreach($competencias->pluck('ano')->unique() as $anoItem)
                                    <option value="{{ $anoItem }}" @selected((string) request('ano') === (string) $anoItem)>{{ $anoItem }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 d-flex gap-2">
                            <button type="submit" class="btn btn-primary"><i class="bx bx-filter-alt"></i> Filtrar</button>
                            <a href="{{ route('rh.portal_externo.holerites') }}" class="btn btn-outline-secondary">Limpar</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card border">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <strong>Lista de holerites</strong>
                    <span class="badge bg-light text-dark border">PDF profissional</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Competência</th>
                                    <th>Proventos</th>
                                    <th>Descontos</th>
                                    <th>Líquido</th>
                                    <th>Competência detalhada</th>
                                    <th>Forma de pagamento</th>
                                    <th class="text-end">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($apuracoes as $apuracao)
                                    <tr>
                                        <td>{{ \App\Support\RHCompetenciaHelper::formatar($apuracao->mes, $apuracao->ano) }}</td>
                                        <td>{{ __moeda($apuracao->total_proventos ?? $apuracao->valor_final) }}</td>
                                        <td>{{ __moeda($apuracao->total_descontos ?? 0) }}</td>
                                        <td><strong>{{ __moeda($apuracao->liquido ?? $apuracao->valor_final) }}</strong></td>
                                        <td><div class="small text-muted">Base INSS: {{ __moeda($apuracao->base_inss ?? 0) }}</div><div class="small text-muted">Base IRRF: {{ __moeda($apuracao->base_irrf ?? 0) }}</div></td>
                                        <td>{{ $apuracao->forma_pagamento ?: '--' }}</td>
                                        <td class="text-end">
                                            <div class="btn-group">
                                                <a target="_blank" href="{{ route('rh.portal_externo.pdf', $apuracao->id) }}" class="btn btn-sm btn-outline-dark">
                                                    <i class="bx bx-show"></i> Visualizar
                                                </a>
                                                <a target="_blank" href="{{ route('rh.portal_externo.pdf', $apuracao->id) }}?download=1" class="btn btn-sm btn-danger">
                                                    <i class="bx bxs-file-pdf"></i> PDF
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4">Nenhum holerite encontrado para os filtros informados.</td>
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
        </div>
    </div>
</div>
@endsection
