@extends('default.layout',['title' => 'Dashboard Executivo de Precificação'])
@section('content')
<div class="page-content">
    <style>
        .mc-card{border:1px solid #e7ebf3;border-radius:18px;background:#fff;box-shadow:0 10px 30px rgba(15,23,42,.05)}
        .mc-kpi{padding:16px 18px;border-radius:16px;border:1px solid #edf2f7;background:linear-gradient(180deg,#fff 0%,#fafcff 100%)}
        .mc-kpi-label{font-size:.76rem;text-transform:uppercase;letter-spacing:.05em;color:#7a8699;font-weight:800}
        .mc-kpi-value{font-size:1.6rem;font-weight:800;color:#0f172a;line-height:1.2;margin-top:4px}
        .mc-title{font-size:1.6rem;font-weight:800;color:#122033;margin:0}
    </style>

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h4 class="mc-title">Dashboard Executivo / Centro de comando</h4>
            <p class="text-muted mb-0">Recriado a partir do layout de referência, adaptado aos indicadores reais do seu módulo.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('precificacao.index') }}" class="btn btn-light">Painel</a>
            <a href="{{ route('precificacao.sugestoes.index') }}" class="btn btn-primary">Sugestões</a>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-2"><div class="mc-kpi"><div class="mc-kpi-label">Produtos</div><div class="mc-kpi-value">{{ $dashboard['kpis']['produtos_total'] }}</div></div></div>
        <div class="col-md-2"><div class="mc-kpi"><div class="mc-kpi-label">OK</div><div class="mc-kpi-value">{{ $dashboard['kpis']['ok'] }}</div></div></div>
        <div class="col-md-2"><div class="mc-kpi"><div class="mc-kpi-label">Alerta</div><div class="mc-kpi-value">{{ $dashboard['kpis']['alerta'] }}</div></div></div>
        <div class="col-md-2"><div class="mc-kpi"><div class="mc-kpi-label">Bloqueado</div><div class="mc-kpi-value">{{ $dashboard['kpis']['bloqueado'] }}</div></div></div>
        <div class="col-md-2"><div class="mc-kpi"><div class="mc-kpi-label">Margem média</div><div class="mc-kpi-value">{{ number_format($dashboard['kpis']['margem_media'], 1, ',', '.') }}%</div></div></div>
        <div class="col-md-2"><div class="mc-kpi"><div class="mc-kpi-label">CMV médio</div><div class="mc-kpi-value">{{ number_format($dashboard['kpis']['cmv_medio'], 1, ',', '.') }}%</div></div></div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-lg-6">
            <div class="mc-card h-100">
                <div class="card-header bg-white"><strong>Top lucrativos</strong></div>
                <div class="card-body table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead><tr><th>Item</th><th>Margem</th><th>Preço sugerido</th></tr></thead>
                        <tbody>
                        @forelse($dashboard['top_lucrativos'] as $item)
                            <tr>
                                <td>{{ $item['produto']->nome ?? '--' }}</td>
                                <td>{{ number_format($item['margem'] ?? 0, 2, ',', '.') }}%</td>
                                <td>R$ {{ number_format($item['preco_sugerido'] ?? 0, 2, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted py-4">Sem dados.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="mc-card h-100">
                <div class="card-header bg-white"><strong>Top críticos</strong></div>
                <div class="card-body table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead><tr><th>Item</th><th>Margem</th><th>Motivos</th></tr></thead>
                        <tbody>
                        @forelse($dashboard['top_criticos'] as $item)
                            <tr>
                                <td>{{ $item['produto']->nome ?? '--' }}</td>
                                <td>{{ number_format($item['margem'] ?? 0, 2, ',', '.') }}%</td>
                                <td class="small text-danger">{{ collect($item['bloqueios'] ?? [])->implode(' | ') ?: collect($item['alertas'] ?? [])->implode(' | ') ?: '--' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted py-4">Sem dados.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="mc-card">
        <div class="card-header bg-white"><strong>Sugestões pendentes</strong></div>
        <div class="card-body table-responsive">
            <table class="table table-striped align-middle mb-0">
                <thead><tr><th>Produto</th><th>Status</th><th>Margem</th><th>CMV</th><th>Preço sugerido</th></tr></thead>
                <tbody>
                @forelse($dashboard['sugestoes_pendentes'] as $item)
                    <tr>
                        <td>{{ $item['produto']->nome ?? '--' }}</td>
                        <td>{{ strtoupper($item['status'] ?? 'erro') }}</td>
                        <td>{{ number_format($item['margem'] ?? 0, 2, ',', '.') }}%</td>
                        <td>{{ number_format($item['cmv'] ?? 0, 2, ',', '.') }}%</td>
                        <td>R$ {{ number_format($item['preco_sugerido'] ?? 0, 2, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">Nenhuma pendência crítica.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
