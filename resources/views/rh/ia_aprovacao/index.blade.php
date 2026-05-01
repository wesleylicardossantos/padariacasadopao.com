@extends('default.layout',['title' => 'RH - IA com Aprovação'])
@section('content')
<style>
.kpi{border:1px solid #e8edf5;border-radius:14px;background:#fff;padding:1rem}
.kpi .l{font-size:.78rem;text-transform:uppercase;color:#64748b;font-weight:700}
.kpi .v{font-size:1.35rem;font-weight:800;color:#0f172a}
.box{border-left:4px solid #94a3b8;border-radius:10px;padding:.9rem 1rem;background:#fff}
</style>

<div class="page-content">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h5 class="mb-0">IA com Aprovação Manual</h5>
            <small class="text-muted">A IA sugere ações e você aprova antes da execução.</small>
        </div>
        <div class="d-flex gap-2">
            <a href="/rh/maximo" class="btn btn-dark">Nível Máximo</a>
            <a href="/rh/absurdo" class="btn btn-secondary">Nível Absurdo</a>
        </div>
    </div>

    <form method="GET" action="/rh/ia-aprovacao">
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
        <div class="col-lg-4"><div class="kpi"><div class="l">Receita</div><div class="v">R$ {{ number_format((float)$resumo['receita'],2,',','.') }}</div></div></div>
        <div class="col-lg-4"><div class="kpi"><div class="l">RH</div><div class="v">R$ {{ number_format((float)$resumo['rh'],2,',','.') }}</div></div></div>
        <div class="col-lg-4"><div class="kpi"><div class="l">Resultado</div><div class="v">R$ {{ number_format((float)$resumo['resultado'],2,',','.') }}</div></div></div>
    </div>

    <div class="card">
        <div class="card-header bg-transparent"><h6 class="mb-0">Sugestões da IA</h6></div>
        <div class="card-body">
            <div class="d-grid gap-3">
                @foreach($sugestoes as $s)
                    <div class="box">
                        <strong>{{ $s['titulo'] }}</strong><br>
                        <span>{{ $s['descricao'] }}</span><br>
                        <small>Risco: {{ strtoupper($s['risco']) }}</small>

                        <form method="POST" action="/rh/ia-aprovacao/aprovar" class="mt-3">
                            @csrf
                            <input type="hidden" name="mes" value="{{ $mes }}">
                            <input type="hidden" name="ano" value="{{ $ano }}">
                            <input type="hidden" name="acao" value="{{ $s['acao'] }}">
                            <button class="btn btn-success" onclick="return confirm('Confirma a execução desta ação sugerida pela IA?')">Aprovar e executar</button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
