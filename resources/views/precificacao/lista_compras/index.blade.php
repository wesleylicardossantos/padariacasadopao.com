@extends('default.layout',['title' => 'Lista de Compras'])
@section('content')
<div class="page-content">
    <style>
        .mc-shell{display:flex;flex-direction:column;gap:18px}.mc-card{border:1px solid #e7ebf3;border-radius:18px;background:#fff;box-shadow:0 10px 30px rgba(15,23,42,.05)}
        .mc-title{font-size:1.6rem;font-weight:800;color:#122033;margin:0}.mc-subtitle{margin:4px 0 0;color:#6b7280}
        .mc-kpi{padding:16px 18px;border-radius:16px;border:1px solid #edf2f7;background:linear-gradient(180deg,#fff 0%,#fafcff 100%)}.mc-kpi-label{font-size:.76rem;text-transform:uppercase;letter-spacing:.05em;color:#7a8699;font-weight:800}.mc-kpi-value{font-size:1.7rem;font-weight:800;color:#0f172a;margin-top:4px}
        .mc-table{width:100%;border-collapse:separate;border-spacing:0}.mc-table th{font-size:.78rem;text-transform:uppercase;letter-spacing:.04em;color:#7a8699;background:#f8fafc;padding:14px 12px;border-bottom:1px solid #e7ebf3}.mc-table td{padding:14px 12px;border-bottom:1px solid #eef2f7;color:#0f172a}
    </style>
    <div class="mc-shell">
        <div>
            <h4 class="mc-title">Lista de Compras</h4>
            <p class="mc-subtitle">Visual alinhado à referência, com foco na leitura rápida de demanda, custo estimado e pendências de compra.</p>
        </div>

        @if(!$estrutura['listas'] || !$estrutura['itens'])
            <div class="alert alert-warning mc-card mb-0"><strong>Estrutura parcial detectada:</strong> as tabelas de lista de compras ainda não estão completas no banco atual.</div>
        @endif

        <div class="row g-3">
            <div class="col-md-3"><div class="mc-kpi"><div class="mc-kpi-label">Listas</div><div class="mc-kpi-value">{{ $cards['listas_total'] }}</div></div></div>
            <div class="col-md-3"><div class="mc-kpi"><div class="mc-kpi-label">Itens</div><div class="mc-kpi-value">{{ $cards['itens_total'] }}</div></div></div>
            <div class="col-md-3"><div class="mc-kpi"><div class="mc-kpi-label">Quantidade total</div><div class="mc-kpi-value">{{ number_format($cards['quantidade_total'], 2, ',', '.') }}</div></div></div>
            <div class="col-md-3"><div class="mc-kpi"><div class="mc-kpi-label">Custo estimado</div><div class="mc-kpi-value">R$ {{ number_format($cards['custo_estimado'], 2, ',', '.') }}</div></div></div>
        </div>

        <div class="row g-3">
            <div class="col-lg-5">
                <div class="mc-card p-3 p-lg-4 h-100">
                    <div class="mb-3"><strong>Últimas listas</strong><div class="text-muted small">Controle por status e data de criação.</div></div>
                    <div class="table-responsive">
                        <table class="mc-table">
                            <thead><tr><th>ID</th><th>Status</th><th>Criada em</th></tr></thead>
                            <tbody>
                            @forelse($listas as $lista)
                                <tr>
                                    <td>#{{ $lista->id }}</td>
                                    <td>{{ strtoupper($lista->status ?? 'aberta') }}</td>
                                    <td>{{ optional($lista->created_at)->format('d/m/Y H:i') ?? '--' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted py-4">Nenhuma lista encontrada.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="mc-card p-3 p-lg-4 h-100">
                    <div class="mb-3"><strong>Itens para compra</strong><div class="text-muted small">Consolidação de itens planejados a partir das receitas e da produção.</div></div>
                    <div class="table-responsive">
                        <table class="mc-table">
                            <thead><tr><th>Lista</th><th>Insumo</th><th>Quantidade</th><th>Custo estimado</th></tr></thead>
                            <tbody>
                            @forelse($itens as $item)
                                <tr>
                                    <td>#{{ $item->lista_id ?? '--' }}</td>
                                    <td>#{{ $item->insumo_id ?? '--' }}</td>
                                    <td>{{ number_format((float)($item->quantidade ?? 0), 2, ',', '.') }}</td>
                                    <td>R$ {{ number_format((float)($item->custo_estimado ?? 0), 2, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-4">Nenhum item de compra disponível.</td></tr>
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
