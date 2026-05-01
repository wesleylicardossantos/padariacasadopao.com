@extends('default.layout',['title' => 'Canais de Venda'])
@section('content')
<div class="page-content">
    <style>
        .mc-shell{display:flex;flex-direction:column;gap:18px}.mc-card{border:1px solid #e7ebf3;border-radius:18px;background:#fff;box-shadow:0 10px 30px rgba(15,23,42,.05)}
        .mc-toolbar{display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap}.mc-title{font-size:1.6rem;font-weight:800;color:#122033;margin:0}
        .mc-subtitle{margin:4px 0 0;color:#6b7280}.mc-kpi{padding:16px 18px;border-radius:16px;border:1px solid #edf2f7;background:linear-gradient(180deg,#fff 0%,#fafcff 100%)}
        .mc-kpi-label{font-size:.76rem;text-transform:uppercase;letter-spacing:.05em;color:#7a8699;font-weight:800}.mc-kpi-value{font-size:1.7rem;font-weight:800;color:#0f172a;margin-top:4px}
        .mc-table{width:100%;border-collapse:separate;border-spacing:0}.mc-table th{font-size:.78rem;text-transform:uppercase;letter-spacing:.04em;color:#7a8699;background:#f8fafc;padding:14px 12px;border-bottom:1px solid #e7ebf3}
        .mc-table td{padding:14px 12px;border-bottom:1px solid #eef2f7;color:#0f172a}.mc-chip{display:inline-flex;align-items:center;padding:.3rem .7rem;border-radius:999px;font-size:.77rem;font-weight:800}
        .mc-ok{background:#e8fff3;color:#0f8f52}.mc-alerta{background:#fff7df;color:#946200}.mc-btn{padding:.75rem 1rem;border-radius:14px;font-weight:700;border:1px solid #dbe4f0;background:#fff;color:#1f2937;text-decoration:none;display:inline-flex;align-items:center;gap:8px}
        .mc-btn.primary{background:#0f172a;color:#fff;border-color:#0f172a}
    </style>
    <div class="mc-shell">
        <div class="mc-toolbar">
            <div>
                <h4 class="mc-title">Canais de Venda</h4>
                <p class="mc-subtitle">Recriação da linguagem visual do arquivo de referência aplicada aos canais do módulo.</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('precificacao.index') }}" class="mc-btn">Painel</a>
                <a href="{{ route('precificacao.dashboard-executivo.index') }}" class="mc-btn primary">Dashboard</a>
            </div>
        </div>

        @if(!$estruturaOk)
            <div class="alert alert-warning mc-card mb-0"><strong>Estrutura pendente:</strong> a tabela <code>precificacao_canais_venda</code> não está disponível no banco atual.</div>
        @endif

        <div class="row g-3">
            <div class="col-md-3"><div class="mc-kpi"><div class="mc-kpi-label">Canais</div><div class="mc-kpi-value">{{ $cards['total'] }}</div></div></div>
            <div class="col-md-3"><div class="mc-kpi"><div class="mc-kpi-label">Ativos</div><div class="mc-kpi-value">{{ $cards['ativos'] }}</div></div></div>
            <div class="col-md-3"><div class="mc-kpi"><div class="mc-kpi-label">Taxa média</div><div class="mc-kpi-value">{{ number_format($cards['taxa_media'], 2, ',', '.') }}%</div></div></div>
            <div class="col-md-3"><div class="mc-kpi"><div class="mc-kpi-label">Comissão média</div><div class="mc-kpi-value">{{ number_format($cards['comissao_media'], 2, ',', '.') }}%</div></div></div>
        </div>

        <div class="mc-card p-3 p-lg-4">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <div><strong>Configuração operacional por canal</strong><div class="text-muted small">Balcão, delivery, atacado e outras estratégias comerciais.</div></div>
                <span class="mc-btn">Filtro rápido</span>
            </div>
            <div class="table-responsive">
                <table class="mc-table">
                    <thead><tr><th>Canal</th><th>Taxa %</th><th>Taxa fixa</th><th>Comissão %</th><th>Status</th></tr></thead>
                    <tbody>
                    @forelse($canais as $canal)
                        <tr>
                            <td><strong>{{ $canal->nome ?? '--' }}</strong></td>
                            <td>{{ number_format((float)($canal->taxa_percentual ?? 0), 2, ',', '.') }}%</td>
                            <td>R$ {{ number_format((float)($canal->taxa_fixa ?? 0), 2, ',', '.') }}</td>
                            <td>{{ number_format((float)($canal->comissao ?? 0), 2, ',', '.') }}%</td>
                            <td><span class="mc-chip {{ ((int)($canal->ativo ?? 1) === 1) ? 'mc-ok' : 'mc-alerta' }}">{{ ((int)($canal->ativo ?? 1) === 1) ? 'ATIVO' : 'INATIVO' }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">Nenhum canal disponível.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
