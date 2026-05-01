@extends('default.layout',['title' => 'RH Premium'])
@section('content')
@php
    $mes = (int) ($mes ?? date('m'));
    $ano = (int) ($ano ?? date('Y'));
    $totalFuncionarios = (int) ($totalFuncionarios ?? 0);
    $ativos = (int) ($ativos ?? 0);
    $inativos = (int) ($inativos ?? 0);
    $folhaBase = (float) ($folhaBase ?? 0);
    $turnover = (float) ($turnover ?? 0);
    $admissoesMes = (int) ($admissoesMes ?? 0);
    $desligamentosMes = (int) ($desligamentosMes ?? 0);
    $faltasMes = (int) ($faltasMes ?? 0);
    $atestadosMes = (int) ($atestadosMes ?? 0);
    $atrasosMes = (int) ($atrasosMes ?? 0);
    $feriasProximas = $feriasProximas ?? collect();
@endphp
<style>
.kpi{border:1px solid #e8edf5;border-radius:14px;background:#fff;padding:1rem}
.kpi .l{font-size:.78rem;text-transform:uppercase;color:#64748b;font-weight:700}
.kpi .v{font-size:1.35rem;font-weight:800;color:#0f172a}
.box{border-left:4px solid #94a3b8;border-radius:10px;padding:.9rem 1rem;background:#fff}
</style>
<div class="page-content">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h5 class="mb-0">Dashboard Executivo Premium</h5>
            <small class="text-muted">Visão premium com score, alertas, previsão e custos por setor.</small>
        </div>
        <div class="d-flex gap-2">
            <a href="/rh/maximo" class="btn btn-dark">Nível Máximo</a>
            <a href="/rh/ia-externa" class="btn btn-secondary">IA Externa</a>
        </div>
    </div>

    <form method="GET" action="/rh/dashboard-premium">
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
                <div class="card-header bg-transparent"><h6 class="mb-0">Alertas</h6></div>
                <div class="card-body">
                    @foreach($alertas as $a)
                        <div class="box mb-2">{{ $a['texto'] }}</div>
                    @endforeach
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