@extends('default.layout',['title' => 'Sugestões de Preço'])
@section('content')
<div class="page-content">
    <style>
        .mc-card{border:1px solid #e7ebf3;border-radius:18px;background:#fff;box-shadow:0 10px 30px rgba(15,23,42,.05)}
        .mc-chip{display:inline-flex;align-items:center;padding:.3rem .7rem;border-radius:999px;font-size:.77rem;font-weight:800}
        .mc-ok{background:#e8fff3;color:#0f8f52}.mc-alerta{background:#fff7df;color:#946200}.mc-bloqueado,.mc-erro{background:#ffe9e9;color:#c92a2a}
        .mc-btn{padding:.65rem .95rem;border-radius:14px;font-weight:700;border:1px solid #dbe4f0;background:#fff;color:#1f2937;text-decoration:none;display:inline-flex;align-items:center;gap:8px}
        .mc-btn.primary{background:#0f172a;color:#fff;border-color:#0f172a}
        .mc-kpi{padding:16px 18px;border-radius:16px;border:1px solid #edf2f7;background:linear-gradient(180deg,#fff 0%,#fafcff 100%)}
        .mc-kpi-label{font-size:.76rem;text-transform:uppercase;letter-spacing:.05em;color:#7a8699;font-weight:800}
        .mc-kpi-value{font-size:1.6rem;font-weight:800;color:#0f172a;line-height:1.2;margin-top:4px}
    </style>

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h4 class="mb-1">Sugestões de Preço</h4>
            <p class="text-muted mb-0">Tela recriada com base no arquivo enviado, ligada à validação automática de margem e às travas de segurança.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('precificacao.index') }}" class="mc-btn">Voltar ao painel</a>
            <a href="{{ route('precificacao.dashboard-executivo.index') }}" class="mc-btn primary">Dashboard Executivo</a>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-3"><div class="mc-kpi"><div class="mc-kpi-label">Itens</div><div class="mc-kpi-value">{{ $resumo['total'] }}</div></div></div>
        <div class="col-md-3"><div class="mc-kpi"><div class="mc-kpi-label">OK</div><div class="mc-kpi-value">{{ $resumo['ok'] }}</div></div></div>
        <div class="col-md-3"><div class="mc-kpi"><div class="mc-kpi-label">Alerta</div><div class="mc-kpi-value">{{ $resumo['alerta'] }}</div></div></div>
        <div class="col-md-3"><div class="mc-kpi"><div class="mc-kpi-label">Bloqueados</div><div class="mc-kpi-value">{{ $resumo['bloqueado'] }}</div></div></div>
    </div>

    <div class="mc-card">
        <div class="card-body table-responsive">
            <table class="table table-striped align-middle mb-0">
                <thead>
                    <tr>
                        <th>Nome do item</th>
                        <th>Preço</th>
                        <th>Custo de mercadoria (CMV)</th>
                        <th>Despesas do canal</th>
                        <th>Lucro bruto</th>
                        <th>Status</th>
                        <th>Validação</th>
                        <th class="text-end">Ação</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sugestoes as $item)
                        @php
                            $statusClass = in_array(($item['status'] ?? 'erro'), ['ok','alerta','bloqueado']) ? $item['status'] : 'erro';
                            $lucroBruto = (float)($item['preco_sugerido'] ?? 0) - (float)($item['custo_total'] ?? 0);
                        @endphp
                        <tr>
                            <td>
                                <strong>{{ $item['produto']->nome ?? '--' }}</strong>
                                <div class="small text-muted">Preço mínimo: R$ {{ number_format($item['preco_minimo'] ?? 0, 2, ',', '.') }}</div>
                            </td>
                            <td>
                                <div>Atual: <strong>R$ {{ number_format($item['preco_atual'] ?? 0, 2, ',', '.') }}</strong></div>
                                <div class="small text-primary">Sugerido: R$ {{ number_format($item['preco_sugerido'] ?? 0, 2, ',', '.') }}</div>
                            </td>
                            <td>
                                <div>{{ number_format($item['cmv'] ?? 0, 2, ',', '.') }}%</div>
                                <div class="small text-muted">Custo: R$ {{ number_format($item['custo_total'] ?? 0, 2, ',', '.') }}</div>
                            </td>
                            <td>{{ number_format($item['despesas_percentual'] ?? 0, 2, ',', '.') }}%</td>
                            <td>R$ {{ number_format($lucroBruto, 2, ',', '.') }}</td>
                            <td><span class="mc-chip mc-{{ $statusClass }}">{{ strtoupper($item['status'] ?? 'erro') }}</span></td>
                            <td>
                                @if(!empty($item['bloqueios']))
                                    @foreach($item['bloqueios'] as $msg)
                                        <div class="small text-danger">• {{ $msg }}</div>
                                    @endforeach
                                @endif
                                @if(!empty($item['alertas']))
                                    @foreach($item['alertas'] as $msg)
                                        <div class="small text-warning">• {{ $msg }}</div>
                                    @endforeach
                                @endif
                                @if(empty($item['bloqueios']) && empty($item['alertas']))
                                    <div class="small text-success">• Liberação automática permitida.</div>
                                @endif
                            </td>
                            <td class="text-end" style="min-width:250px;">
                                @if(($item['status'] ?? '') === 'ok')
                                    <form method="POST" action="{{ route('precificacao.sugestoes.aprovar', $item['produto']->id) }}">
                                        @csrf
                                        <button class="btn btn-sm btn-success">Aprovar</button>
                                    </form>
                                @elseif(($item['status'] ?? '') === 'alerta')
                                    <form method="POST" action="{{ route('precificacao.sugestoes.aprovar', $item['produto']->id) }}" class="d-flex flex-column gap-1">
                                        @csrf
                                        <input type="text" name="justificativa" class="form-control form-control-sm" placeholder="Justificativa obrigatória">
                                        <button class="btn btn-sm btn-warning">Aprovar com justificativa</button>
                                    </form>
                                @else
                                    <button class="btn btn-sm btn-outline-danger" disabled>Bloqueado</button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center py-4 text-muted">Nenhuma sugestão disponível.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
