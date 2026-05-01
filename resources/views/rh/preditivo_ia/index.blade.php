@extends('default.layout',['title' => 'RH - Preditivo IA'])
@section('content')
<style>
.kpi{border:1px solid #e8edf5;border-radius:14px;background:#fff;padding:1rem}
.kpi .l{font-size:.78rem;text-transform:uppercase;color:#64748b;font-weight:700}
.kpi .v{font-size:1.35rem;font-weight:800;color:#0f172a}
.badge-risk{display:inline-block;padding:.25rem .6rem;border-radius:999px;font-weight:700}
.baixo{background:#f0fdf4;color:#166534}
.medio{background:#fff7ed;color:#9a3412}
.alto{background:#fef2f2;color:#991b1b}
</style>

<div class="page-content">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h5 class="mb-0">Preditivo IA</h5>
            <small class="text-muted">Projeção baseada em histórico e aprendizado da IA.</small>
        </div>
        <div class="d-flex gap-2">
            <a href="/rh/maximo" class="btn btn-dark">Nível Máximo</a>
            <a href="/rh/ia-aprendizado" class="btn btn-secondary">IA com Aprendizado</a>
        </div>
    </div>

    <form method="GET" action="/rh/preditivo-ia">
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
        <div class="col-lg-3"><div class="kpi"><div class="l">Receita</div><div class="v">R$ {{ number_format((float)$resumo['receita'],2,',','.') }}</div></div></div>
        <div class="col-lg-3"><div class="kpi"><div class="l">RH</div><div class="v">R$ {{ number_format((float)$resumo['rh'],2,',','.') }}</div></div></div>
        <div class="col-lg-3"><div class="kpi"><div class="l">Resultado</div><div class="v">R$ {{ number_format((float)$resumo['resultado'],2,',','.') }}</div></div></div>
        <div class="col-lg-3"><div class="kpi"><div class="l">Risco</div><div class="v"><span class="badge-risk {{ $dados['risco'] }}">{{ strtoupper($dados['risco']) }}</span></div></div></div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-transparent"><h6 class="mb-0">Aprendizado da IA</h6></div>
                <div class="card-body">
                    <p class="mb-2">Tendência receita: <strong>{{ strtoupper($dados['aprendizado']['tendencia_receita']) }}</strong></p>
                    <p class="mb-2">Tendência RH: <strong>{{ strtoupper($dados['aprendizado']['tendencia_rh']) }}</strong></p>
                    <p class="mb-2">Tendência resultado: <strong>{{ strtoupper($dados['aprendizado']['tendencia_resultado']) }}</strong></p>
                    <p class="mb-0">Parecer: <strong>{{ $dados['parecer'] }}</strong></p>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-transparent"><h6 class="mb-0">Projeção 3 meses</h6></div>
                <div class="card-body p-0">
                    <table class="table table-striped mb-0">
                        <thead><tr><th>M+N</th><th>Receita</th><th>RH</th><th>Resultado</th><th>Margem</th></tr></thead>
                        <tbody>
                            @foreach($dados['projecoes'] as $p)
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
@endsection
