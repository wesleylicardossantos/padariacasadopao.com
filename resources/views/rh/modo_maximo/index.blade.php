@extends('default.layout',['title' => 'RH - Nível Máximo'])
@section('content')
<style>
.rh-card{border:1px solid #e8edf5;border-radius:16px;box-shadow:0 8px 24px rgba(15,23,42,.04)}
.rh-kpi{border:1px solid #e8edf5;border-radius:14px;background:#fff}
.rh-kpi .card-body{padding:1rem}
.rh-kpi .label{font-size:.78rem;text-transform:uppercase;font-weight:700;color:#64748b}
.rh-kpi .value{font-size:1.35rem;font-weight:800;color:#0f172a}
.badgebox{padding:.4rem .7rem;border-radius:999px;font-weight:700;font-size:.8rem}
.ok{background:#f0fdf4;color:#166534}
.warn{background:#fff7ed;color:#9a3412}
.danger{background:#fef2f2;color:#991b1b}
.box{border-left:4px solid #94a3b8;border-radius:10px;padding:.9rem 1rem;background:#fff}
.box.positivo{border-left-color:#16a34a;background:#f0fdf4}
.box.alerta{border-left-color:#f59e0b;background:#fff7ed}
.box.critico{border-left-color:#dc2626;background:#fef2f2}
.score{font-size:2.6rem;font-weight:900}
</style>

<div class="page-content">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h5 class="mb-0">RH - Nível Máximo</h5>
            <small class="text-muted">Painel único com IA, histórico, previsão, alertas, modo dono e ação executiva.</small>
        </div>
        <div class="d-flex gap-2">
            <a href="/rh/enterprise-total" class="btn btn-dark">Enterprise Total</a>
            <a href="/rh/ia-autonoma" class="btn btn-secondary">IA Autônoma</a>
        </div>
    </div>

    <form method="GET" action="/rh/maximo">
        <div class="card rh-card mb-3">
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
        <div class="col-lg-3"><div class="card rh-kpi"><div class="card-body"><div class="label">Score</div><div class="score">{{ $score }}/100</div></div></div></div>
        <div class="col-lg-3"><div class="card rh-kpi"><div class="card-body"><div class="label">Receita</div><div class="value">R$ {{ number_format((float)$resumo['receita'],2,',','.') }}</div></div></div></div>
        <div class="col-lg-3"><div class="card rh-kpi"><div class="card-body"><div class="label">RH</div><div class="value">R$ {{ number_format((float)$resumo['rh'],2,',','.') }}</div></div></div></div>
        <div class="col-lg-3"><div class="card rh-kpi"><div class="card-body"><div class="label">Resultado</div><div class="value">R$ {{ number_format((float)$resumo['resultado'],2,',','.') }}</div></div></div></div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-lg-3"><div class="card rh-kpi"><div class="card-body"><div class="label">% Folha/Faturamento</div><div class="value">{{ number_format((float)$resumo['peso_folha'],2,',','.') }}%</div></div></div></div>
        <div class="col-lg-3"><div class="card rh-kpi"><div class="card-body"><div class="label">Margem</div><div class="value">{{ number_format((float)$resumo['margem'],2,',','.') }}%</div></div></div></div>
        <div class="col-lg-3"><div class="card rh-kpi"><div class="card-body"><div class="label">Receita por colaborador</div><div class="value">R$ {{ number_format((float)$resumo['receita_por_funcionario'],2,',','.') }}</div></div></div></div>
        <div class="col-lg-3"><div class="card rh-kpi"><div class="card-body"><div class="label">Custo por colaborador</div><div class="value">R$ {{ number_format((float)$resumo['custo_por_funcionario'],2,',','.') }}</div></div></div></div>
    </div>

    <div class="card rh-card mb-3">
        <div class="card-header bg-transparent"><h6 class="mb-0">Modo Dono</h6></div>
        <div class="card-body">
            @php
                $cls = $modoDono['status'] === 'excelente' ? 'ok' : ($modoDono['status'] === 'atencao' ? 'warn' : 'danger');
            @endphp
            <span class="badgebox {{ $cls }}">{{ strtoupper($modoDono['status']) }}</span>
            <div class="mt-3"><strong>Decisão sugerida:</strong> {{ $modoDono['decisao'] }}</div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-lg-6">
            <div class="card rh-card">
                <div class="card-header bg-transparent"><h6 class="mb-0">Alertas automáticos</h6></div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @foreach($alertas as $a)
                            <div class="box {{ $a['nivel'] }}">{{ $a['texto'] }}</div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card rh-card">
                <div class="card-header bg-transparent"><h6 class="mb-0">Ações da IA</h6></div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @foreach($acoes as $a)
                            <div class="box {{ $a['nivel'] }}">
                                <strong>{{ $a['titulo'] }}</strong><br>
                                <span><strong>Ação:</strong> {{ $a['acao'] }}</span><br>
                                <span><strong>Motivo:</strong> {{ $a['motivo'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-lg-6">
            <div class="card rh-card">
                <div class="card-header bg-transparent"><h6 class="mb-0">Tendência histórica (6 meses)</h6></div>
                <div class="card-body">
                    <p class="mb-2">Receita: <strong>{{ strtoupper($tendencia['receita']) }}</strong></p>
                    <p class="mb-2">RH: <strong>{{ strtoupper($tendencia['rh']) }}</strong></p>
                    <p class="mb-0">Resultado: <strong>{{ strtoupper($tendencia['resultado']) }}</strong></p>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card rh-card">
                <div class="card-header bg-transparent"><h6 class="mb-0">Previsão dos próximos 3 meses</h6></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr><th>M+N</th><th>Receita</th><th>RH</th><th>Resultado</th><th>Margem</th></tr>
                            </thead>
                            <tbody>
                                @foreach($previsoes as $p)
                                <tr>
                                    <td>M+{{ $p['passo'] }}</td>
                                    <td>R$ {{ number_format((float)$p['receita'],2,',','.') }}</td>
                                    <td>R$ {{ number_format((float)$p['rh'],2,',','.') }}</td>
                                    <td>R$ {{ number_format((float)$p['resultado'],2,',','.') }}</td>
                                    <td>{{ number_format((float)$p['margem'],2,',','.') }}%</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card rh-card">
        <div class="card-header bg-transparent"><h6 class="mb-0">Top setores/funções por custo</h6></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr><th>Setor/Função</th><th class="text-end">Custo</th></tr>
                    </thead>
                    <tbody>
                        @forelse($setores as $s)
                        <tr>
                            <td>{{ $s['setor'] }}</td>
                            <td class="text-end">R$ {{ number_format((float)$s['custo'],2,',','.') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="2" class="text-center py-4">Sem dados</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
