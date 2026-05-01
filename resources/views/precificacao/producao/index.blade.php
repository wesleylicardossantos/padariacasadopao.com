@extends('default.layout',['title' => 'Produção'])
@section('content')
<div class="page-content">
    <style>
        .mc-shell{display:flex;flex-direction:column;gap:18px}.mc-card{border:1px solid #e7ebf3;border-radius:18px;background:#fff;box-shadow:0 10px 30px rgba(15,23,42,.05)}
        .mc-title{font-size:1.6rem;font-weight:800;color:#122033;margin:0}.mc-subtitle{margin:4px 0 0;color:#6b7280}.mc-kpi{padding:16px 18px;border-radius:16px;border:1px solid #edf2f7;background:linear-gradient(180deg,#fff 0%,#fafcff 100%)}
        .mc-kpi-label{font-size:.76rem;text-transform:uppercase;letter-spacing:.05em;color:#7a8699;font-weight:800}.mc-kpi-value{font-size:1.7rem;font-weight:800;color:#0f172a;margin-top:4px}.mc-table{width:100%;border-collapse:separate;border-spacing:0}
        .mc-table th{font-size:.78rem;text-transform:uppercase;letter-spacing:.04em;color:#7a8699;background:#f8fafc;padding:14px 12px;border-bottom:1px solid #e7ebf3}.mc-table td{padding:14px 12px;border-bottom:1px solid #eef2f7;color:#0f172a}.mc-chip{display:inline-flex;align-items:center;padding:.3rem .7rem;border-radius:999px;font-size:.77rem;font-weight:800}.mc-ok{background:#e8fff3;color:#0f8f52}.mc-alerta{background:#fff7df;color:#946200}
    </style>
    <div class="mc-shell">
        <div>
            <h4 class="mc-title">Produção</h4>
            <p class="mc-subtitle">Visão operacional inspirada no material de referência, integrada ao custo teórico e real do seu módulo.</p>
        </div>

        @if(!$estrutura['producoes'] || !$estrutura['itens'])
            <div class="alert alert-warning mc-card mb-0"><strong>Estrutura parcial detectada:</strong> algumas tabelas de produção ainda não estão presentes no banco atual.</div>
        @endif

        <div class="row g-3">
            <div class="col-md-3"><div class="mc-kpi"><div class="mc-kpi-label">Ordens</div><div class="mc-kpi-value">{{ $cards['producoes_total'] }}</div></div></div>
            <div class="col-md-3"><div class="mc-kpi"><div class="mc-kpi-label">Concluídas</div><div class="mc-kpi-value">{{ $cards['concluidas'] }}</div></div></div>
            <div class="col-md-3"><div class="mc-kpi"><div class="mc-kpi-label">Custo teórico</div><div class="mc-kpi-value">R$ {{ number_format($cards['custo_teorico'], 2, ',', '.') }}</div></div></div>
            <div class="col-md-3"><div class="mc-kpi"><div class="mc-kpi-label">Custo real</div><div class="mc-kpi-value">R$ {{ number_format($cards['custo_real'], 2, ',', '.') }}</div></div></div>
        </div>

        <div class="row g-3">
            <div class="col-lg-7">
                <div class="mc-card p-3 p-lg-4 h-100">
                    <div class="mb-3"><strong>Ordens de produção</strong><div class="text-muted small">Acompanhamento de custo, status e fechamento da produção.</div></div>
                    <div class="table-responsive">
                        <table class="mc-table">
                            <thead><tr><th>ID</th><th>Produto</th><th>Quantidade</th><th>Custo teórico</th><th>Custo real</th><th>Status</th></tr></thead>
                            <tbody>
                            @forelse($producoes as $producao)
                                @php($status = strtolower((string)($producao->status ?? 'pendente')))
                                <tr>
                                    <td>#{{ $producao->id }}</td>
                                    <td>#{{ $producao->produto_id ?? '--' }}</td>
                                    <td>{{ number_format((float)($producao->quantidade ?? 0), 2, ',', '.') }}</td>
                                    <td>R$ {{ number_format((float)($producao->custo_teorico ?? 0), 2, ',', '.') }}</td>
                                    <td>R$ {{ number_format((float)($producao->custo_real ?? 0), 2, ',', '.') }}</td>
                                    <td><span class="mc-chip {{ $status === 'concluida' ? 'mc-ok' : 'mc-alerta' }}">{{ strtoupper($producao->status ?? 'pendente') }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted py-4">Nenhuma ordem de produção disponível.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="mc-card p-3 p-lg-4 h-100">
                    <div class="mb-3"><strong>Consumo de insumos</strong><div class="text-muted small">Últimos lançamentos de insumos aplicados nas produções.</div></div>
                    <div class="table-responsive">
                        <table class="mc-table">
                            <thead><tr><th>Produção</th><th>Insumo</th><th>Qtd.</th><th>Custo unitário</th></tr></thead>
                            <tbody>
                            @forelse($itens as $item)
                                <tr>
                                    <td>#{{ $item->producao_id ?? '--' }}</td>
                                    <td>#{{ $item->insumo_id ?? '--' }}</td>
                                    <td>{{ number_format((float)($item->quantidade ?? 0), 2, ',', '.') }}</td>
                                    <td>R$ {{ number_format((float)($item->custo_unitario ?? 0), 4, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-4">Nenhum consumo disponível.</td></tr>
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
