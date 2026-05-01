@extends('default.layout',['title' => 'RH - Nível Absurdo Máximo'])
@section('content')
<style>
.kpi{border:1px solid #e8edf5;border-radius:14px;background:#fff;padding:1rem}
.kpi .l{font-size:.78rem;text-transform:uppercase;color:#64748b;font-weight:700}
.kpi .v{font-size:1.35rem;font-weight:800;color:#0f172a}
.box{border-left:4px solid #94a3b8;border-radius:10px;padding:.9rem 1rem;background:#fff}
.box.positivo{border-left-color:#16a34a;background:#f0fdf4}
.box.alerta{border-left-color:#f59e0b;background:#fff7ed}
.box.critico{border-left-color:#dc2626;background:#fef2f2}
</style>
<div class="page-content">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h5 class="mb-0">Nível Absurdo Máximo</h5>
            <small class="text-muted">IA com aprendizado histórico, motor de decisão, WhatsApp inteligente e visão CEO.</small>
        </div>
        <div class="d-flex gap-2">
            <a href="/rh/dashboard-premium" class="btn btn-dark">Dashboard Premium</a>
            <a href="/rh/whatsapp-inteligente" class="btn btn-secondary">WhatsApp Inteligente</a>
        </div>
    </div>

    <form method="GET" action="/rh/absurdo">
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
        <div class="col-lg-3"><div class="kpi"><div class="l">Score</div><div class="v">{{ $score }}/100</div></div></div>
        <div class="col-lg-3"><div class="kpi"><div class="l">Receita</div><div class="v">R$ {{ number_format((float)$resumo['receita'],2,',','.') }}</div></div></div>
        <div class="col-lg-3"><div class="kpi"><div class="l">RH</div><div class="v">R$ {{ number_format((float)$resumo['rh'],2,',','.') }}</div></div></div>
        <div class="col-lg-3"><div class="kpi"><div class="l">Resultado</div><div class="v">R$ {{ number_format((float)$resumo['resultado'],2,',','.') }}</div></div></div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-transparent"><h6 class="mb-0">Aprendizado da IA</h6></div>
                <div class="card-body">
                    <p class="mb-2">Tendência receita: <strong>{{ strtoupper($aprendizado['tendencia_receita']) }}</strong></p>
                    <p class="mb-2">Tendência RH: <strong>{{ strtoupper($aprendizado['tendencia_rh']) }}</strong></p>
                    <p class="mb-2">Tendência resultado: <strong>{{ strtoupper($aprendizado['tendencia_resultado']) }}</strong></p>
                    <p class="mb-0">Volatilidade do resultado: <strong>{{ number_format((float)$aprendizado['volatilidade_resultado'],2,',','.') }}</strong></p>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-transparent"><h6 class="mb-0">Previsão 3 meses</h6></div>
                <div class="card-body p-0">
                    <table class="table table-striped mb-0">
                        <thead><tr><th>M+N</th><th>Resultado</th><th>Margem</th></tr></thead>
                        <tbody>
                            @foreach($previsoes as $p)
                            <tr>
                                <td>M+{{ $p['passo'] }}</td>
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

    <div class="row g-3 mb-3">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-transparent"><h6 class="mb-0">Alertas</h6></div>
                <div class="card-body">
                    @foreach($alertas as $a)
                        <div class="box {{ $a['nivel'] }} mb-2">{{ $a['texto'] }}</div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-transparent"><h6 class="mb-0">Motor de decisão</h6></div>
                <div class="card-body">
                    @foreach($acoes as $a)
                        <div class="box {{ $a['nivel'] }} mb-2">
                            <strong>{{ $a['titulo'] }}</strong><br>
                            <span><strong>Ação:</strong> {{ $a['acao'] }}</span><br>
                            <span><strong>Impacto:</strong> {{ $a['impacto'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-transparent"><h6 class="mb-0">Top setores/funções por custo</h6></div>
        <div class="card-body p-0">
            <table class="table table-striped mb-0">
                <thead><tr><th>Setor/Função</th><th class="text-end">Custo</th></tr></thead>
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
@endsection
