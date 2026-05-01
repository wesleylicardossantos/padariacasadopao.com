@extends('default.layout',['title' => 'RH - IA com Aprendizado'])
@section('content')
<style>
.kpi{border:1px solid #e8edf5;border-radius:14px;background:#fff;padding:1rem}
.kpi .l{font-size:.78rem;text-transform:uppercase;color:#64748b;font-weight:700}
.kpi .v{font-size:1.35rem;font-weight:800;color:#0f172a}
.box{border-left:4px solid #94a3b8;border-radius:10px;padding:.9rem 1rem;background:#fff}
.badge-score{display:inline-block;padding:.2rem .55rem;border-radius:999px;background:#eef2ff;font-size:.78rem;font-weight:700}
</style>

<div class="page-content">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h5 class="mb-0">IA com Aprendizado</h5>
            <small class="text-muted">A IA aprende com aprovações e rejeições para priorizar melhores sugestões.</small>
        </div>
        <div class="d-flex gap-2">
            <a href="/rh/ia-aprovacao" class="btn btn-dark">IA com Aprovação</a>
            <a href="/rh/maximo" class="btn btn-secondary">Nível Máximo</a>
        </div>
    </div>

    <form method="GET" action="/rh/ia-aprendizado">
        <div class="card mb-3">
            <div class="card-body p-4">
                <div class="row g-3 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label">Mês</label>
                        <input type="number" class="form-control" name="mes" min="1" max="12" value="{{ $mes }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Ano</label>
                        <input type="number" class="form-control" name="ano" value="{{ $ano }}">
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary w-100">Atualizar</button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <div class="row g-3 mb-3">
        <div class="col-lg-3"><div class="kpi"><div class="l">Decisões totais</div><div class="v">{{ $memoria['total'] }}</div></div></div>
        <div class="col-lg-3"><div class="kpi"><div class="l">Aprovadas</div><div class="v">{{ $memoria['aprovados'] }}</div></div></div>
        <div class="col-lg-3"><div class="kpi"><div class="l">Rejeitadas</div><div class="v">{{ $memoria['rejeitados'] }}</div></div></div>
        <div class="col-lg-3"><div class="kpi"><div class="l">Taxa de aprovação</div><div class="v">{{ number_format((float)$memoria['taxa_aprovacao'],2,',','.') }}%</div></div></div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header bg-transparent"><h6 class="mb-0">Sugestões priorizadas pela IA</h6></div>
                <div class="card-body">
                    <div class="d-grid gap-3">
                        @foreach($sugestoes as $s)
                            <div class="box">
                                <div class="d-flex justify-content-between align-items-start gap-2">
                                    <div>
                                        <strong>{{ $s['titulo'] }}</strong><br>
                                        <span>{{ $s['descricao'] }}</span><br>
                                        <small>Risco: {{ strtoupper($s['risco']) }}</small>
                                    </div>
                                    <span class="badge-score">Aprendizado: {{ number_format((float)($s['score_aprendizado'] ?? 0),2,',','.') }}</span>
                                </div>

                                <div class="mt-3 d-flex gap-2 flex-wrap">
                                    <form method="POST" action="/rh/ia-aprendizado/decidir">
                                        @csrf
                                        <input type="hidden" name="mes" value="{{ $mes }}">
                                        <input type="hidden" name="ano" value="{{ $ano }}">
                                        <input type="hidden" name="acao" value="{{ $s['acao'] }}">
                                        <input type="hidden" name="titulo" value="{{ $s['titulo'] }}">
                                        <input type="hidden" name="decisao" value="aprovado">
                                        <button class="btn btn-success" onclick="return confirm('Confirma aprovar e executar esta ação?')">Aprovar</button>
                                    </form>

                                    <form method="POST" action="/rh/ia-aprendizado/decidir">
                                        @csrf
                                        <input type="hidden" name="mes" value="{{ $mes }}">
                                        <input type="hidden" name="ano" value="{{ $ano }}">
                                        <input type="hidden" name="acao" value="{{ $s['acao'] }}">
                                        <input type="hidden" name="titulo" value="{{ $s['titulo'] }}">
                                        <input type="hidden" name="decisao" value="rejeitado">
                                        <button class="btn btn-outline-danger" onclick="return confirm('Confirma rejeitar esta ação?')">Rejeitar</button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card">
                <div class="card-header bg-transparent"><h6 class="mb-0">Ações mais aprendidas</h6></div>
                <div class="card-body p-0">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Ação</th>
                                <th>Aprovações</th>
                                <th>Rejeições</th>
                                <th>Score</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topAcoes as $a)
                            <tr>
                                <td>{{ $a->titulo ?: $a->acao }}</td>
                                <td>{{ $a->aprovados }}</td>
                                <td>{{ $a->rejeitados }}</td>
                                <td>{{ number_format((float)$a->score,2,',','.') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center py-4">Sem histórico ainda</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
