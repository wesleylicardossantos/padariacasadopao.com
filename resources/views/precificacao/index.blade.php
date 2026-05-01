@extends('default.layout',['title' => 'Painel de Precificação'])
@section('content')
<div class="page-content">
    <style>
        .mc-shell{display:flex;flex-direction:column;gap:18px}
        .mc-card{border:1px solid #e7ebf3;border-radius:18px;background:#fff;box-shadow:0 10px 30px rgba(15,23,42,.05)}
        .mc-toolbar{display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap}
        .mc-title{font-size:1.6rem;font-weight:800;color:#122033;margin:0}
        .mc-subtitle{margin:4px 0 0;color:#6b7280}
        .mc-pills,.mc-filters,.mc-actions{display:flex;gap:10px;flex-wrap:wrap}
        .mc-pill{padding:.65rem 1rem;border-radius:14px;border:1px solid #dbe4f0;background:#fff;font-weight:700;color:#24364b}
        .mc-pill.active{background:#f5f8fc;border-color:#b7c8de;color:#0f172a}
        .mc-kpi{padding:16px 18px;border-radius:16px;border:1px solid #edf2f7;background:linear-gradient(180deg,#fff 0%,#fafcff 100%)}
        .mc-kpi-label{font-size:.76rem;text-transform:uppercase;letter-spacing:.05em;color:#7a8699;font-weight:800}
        .mc-kpi-value{font-size:1.8rem;font-weight:800;color:#0f172a;line-height:1.2;margin-top:4px}
        .mc-filter{min-width:190px;padding:.75rem .95rem;border:1px solid #dbe4f0;border-radius:14px;background:#fff;color:#334155}
        .mc-btn{padding:.75rem 1rem;border-radius:14px;font-weight:700;border:1px solid #dbe4f0;background:#fff;color:#1f2937;text-decoration:none;display:inline-flex;align-items:center;gap:8px}
        .mc-btn.primary{background:#0f172a;color:#fff;border-color:#0f172a}
        .mc-section-title{font-size:1rem;font-weight:800;color:#0f172a;margin-bottom:4px}
        .mc-section-subtitle{font-size:.92rem;color:#6b7280}
        .mc-table-wrap{overflow:auto}
        .mc-table{width:100%;min-width:1050px;border-collapse:separate;border-spacing:0}
        .mc-table th{font-size:.78rem;text-transform:uppercase;letter-spacing:.04em;color:#7a8699;background:#f8fafc;padding:14px 12px;border-bottom:1px solid #e7ebf3;position:sticky;top:0;z-index:1}
        .mc-table td{padding:14px 12px;border-bottom:1px solid #eef2f7;color:#0f172a;vertical-align:top}
        .mc-chip{display:inline-flex;align-items:center;padding:.3rem .7rem;border-radius:999px;font-size:.77rem;font-weight:800}
        .mc-ok{background:#e8fff3;color:#0f8f52}.mc-alerta{background:#fff7df;color:#946200}.mc-bloqueado,.mc-erro{background:#ffe9e9;color:#c92a2a}
        .mc-mini{font-size:.82rem;color:#6b7280}
        .mc-list{margin:0;padding-left:18px;color:#6b7280}
    </style>

    <div class="mc-shell">
        <div class="mc-toolbar">
            <div>
                <div class="mc-pills mb-3">
                    <a href="{{ route('produtos.index') }}" class="mc-pill">Mercadoria</a>
                    <a href="{{ route('precificacao.receitas.index') }}" class="mc-pill">Receita</a>
                    <a href="{{ route('precificacao.exemplos.index') }}" class="mc-pill">Exemplos</a>
                    <a href="{{ route('precificacao.index') }}" class="mc-pill active">Itens de cardápio</a>
                </div>
                <h4 class="mc-title">Cardápio / Precificação</h4>
                <p class="mc-subtitle">Recriação funcional inspirada no arquivo enviado do Menu Control, adaptada ao seu módulo Laravel atual.</p>
            </div>
            <div class="mc-actions">
                <form action="{{ route('precificacao.kit-padaria.instalar') }}" method="POST" onsubmit="return confirm('Implantar ou atualizar o kit avançado de padaria neste projeto?')">
                    @csrf
                    <button type="submit" class="mc-btn">Implantar kit avançado padaria</button>
                </form>
                <a href="{{ route('precificacao.sugestoes.index') }}" class="mc-btn primary">Atualizar preços</a>
                <a href="{{ route('precificacao.dashboard-executivo.index') }}" class="mc-btn">Centro de comando</a>
            </div>
        </div>


        @if(session('success'))
            <div class="alert alert-success mc-card mb-0">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger mc-card mb-0">{{ session('error') }}</div>
        @endif

        @php($pendencias = collect($estrutura)->filter(fn($v) => !$v)->keys())
        @if($pendencias->isNotEmpty())
            <div class="alert alert-warning mc-card mb-0">
                <strong>Estrutura parcial detectada:</strong> alguns recursos seguem em modo resiliente. Pendências: {{ $pendencias->implode(', ') }}.
            </div>
        @endif

        <div class="row g-3">
            <div class="col-lg-3 col-md-6"><div class="mc-kpi"><div class="mc-kpi-label">Itens</div><div class="mc-kpi-value">{{ $dashboard['kpis']['produtos_total'] }}</div><div class="mc-mini">Base precificada da empresa</div></div></div>
            <div class="col-lg-3 col-md-6"><div class="mc-kpi"><div class="mc-kpi-label">Preço liberado</div><div class="mc-kpi-value">{{ $dashboard['kpis']['ok'] }}</div><div class="mc-mini">Sem travas de margem</div></div></div>
            <div class="col-lg-3 col-md-6"><div class="mc-kpi"><div class="mc-kpi-label">Em alerta</div><div class="mc-kpi-value">{{ $dashboard['kpis']['alerta'] }}</div><div class="mc-mini">Exigem justificativa</div></div></div>
            <div class="col-lg-3 col-md-6"><div class="mc-kpi"><div class="mc-kpi-label">Bloqueados</div><div class="mc-kpi-value">{{ $dashboard['kpis']['bloqueado'] }}</div><div class="mc-mini">Preço travado por segurança</div></div></div>
        </div>

        <div class="mc-card p-3 p-lg-4">
            <div class="mc-toolbar mb-3">
                <div>
                    <div class="mc-section-title">Itens de cardápio</div>
                    <div class="mc-section-subtitle">Lista que exibe as precificações das suas receitas e mercadorias.</div>
                </div>
                <div class="mc-actions">
                    <span class="mc-btn">Exportar</span>
                </div>
            </div>

            <div class="mc-filters mb-3">
                <select class="mc-filter">
                    <option>Todas as categorias</option>
                    @foreach($categorias as $categoria)
                        <option>{{ $categoria }}</option>
                    @endforeach
                </select>
                <select class="mc-filter">
                    <option>Todos canais de venda</option>
                    @foreach($canais as $canal)
                        <option>{{ $canal }}</option>
                    @endforeach
                </select>
                <span class="mc-btn">Limpar filtros</span>
            </div>

            <div class="mc-table-wrap">
                <table class="mc-table">
                    <thead>
                        <tr>
                            <th>Canal de venda</th>
                            <th>Categoria</th>
                            <th>Nome do item</th>
                            <th>Preço</th>
                            <th>Custo de mercadoria (CMV)</th>
                            <th>Despesas do canal</th>
                            <th>Lucro bruto</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($itensCardapio as $item)
                            @php($statusClass = in_array($item['status'], ['ok','alerta','bloqueado']) ? $item['status'] : 'erro')
                            <tr>
                                <td>{{ $item['canal'] }}</td>
                                <td>{{ $item['categoria'] }}</td>
                                <td>
                                    <strong>{{ $item['produto']->nome ?? '--' }}</strong>
                                    <div class="mc-mini">Preço sugerido: R$ {{ number_format($item['preco_sugerido'] ?? 0, 2, ',', '.') }}</div>
                                </td>
                                <td>R$ {{ number_format($item['preco'] ?? 0, 2, ',', '.') }}</td>
                                <td>{{ number_format($item['cmv'] ?? 0, 2, ',', '.') }}%</td>
                                <td>{{ number_format($item['despesas_percentual'] ?? 0, 2, ',', '.') }}%</td>
                                <td>R$ {{ number_format($item['lucro_bruto'] ?? 0, 2, ',', '.') }}</td>
                                <td><span class="mc-chip mc-{{ $statusClass }}">{{ strtoupper($item['status']) }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted py-4">Você ainda não possui itens de cardápio.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-7">
                <div class="mc-card p-4 h-100">
                    <div class="mc-section-title">Regras automáticas de proteção</div>
                    <div class="mc-section-subtitle mb-3">O módulo agora replica a lógica visual do arquivo enviado, mas com governança real no seu ERP.</div>
                    <ul class="mc-list">
                        <li>Bloqueia publicação abaixo da margem mínima.</li>
                        <li>Bloqueia preço abaixo do preço mínimo operacional.</li>
                        <li>Exige justificativa para itens em alerta.</li>
                        <li>Não publica item sem ficha técnica ou sem vínculo legado.</li>
                        <li>Impede aprovação quando existem insumos sem custo.</li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="mc-card p-4 h-100">
                    <div class="mc-section-title">Resumo rápido</div>
                    <div class="mc-section-subtitle mb-3">Base utilizada para montar a recriação.</div>
                    <div class="mc-mini mb-2">Margem média: <strong>{{ number_format($dashboard['kpis']['margem_media'], 2, ',', '.') }}%</strong></div>
                    <div class="mc-mini mb-2">CMV médio: <strong>{{ number_format($dashboard['kpis']['cmv_medio'], 2, ',', '.') }}%</strong></div>
                    <div class="mc-mini mb-2">Impacto recomendado: <strong>R$ {{ number_format($dashboard['kpis']['impacto_total_recomendado'], 2, ',', '.') }}</strong></div>
                    <div class="mc-mini">Últimos produtos carregados: <strong>{{ $ultimosProdutos->count() }}</strong></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
