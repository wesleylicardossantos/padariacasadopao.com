@extends('default.layout', ['title' => 'SaaS Enterprise'])

@section('content')
@php
    $money = fn($v) => 'R$ ' . number_format((float) ($v ?? 0), 2, ',', '.');
@endphp
<div class="erp-saas-page">
    <div class="erp-saas-header">
        <div>
            <h2 class="erp-saas-title">SaaS Enterprise</h2>
            <p class="erp-saas-subtitle">Empresa {{ $empresa_id ?? request('empresa_id') }} · Atualizado em {{ $updated_at ?? now()->format('d/m/Y H:i:s') }}</p>
        </div>
        <div><a href="javascript:history.back()" class="btn btn-sm btn-outline-secondary">Voltar</a></div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-md-6 col-xl-3"><div class="erp-saas-kpi"><div class="card-body"><div class="erp-saas-label">Status assinatura</div><div class="erp-saas-value">{{ strtoupper($lifecycle['status'] ?? 'inactive') }}</div></div></div></div>
        <div class="col-12 col-md-6 col-xl-3"><div class="erp-saas-kpi"><div class="card-body"><div class="erp-saas-label">Expiração</div><div class="erp-saas-value">{{ $lifecycle['expiracao'] ?? '--' }}</div></div></div></div>
        <div class="col-12 col-md-6 col-xl-3"><div class="erp-saas-kpi"><div class="card-body"><div class="erp-saas-label">Onboarding</div><div class="erp-saas-value">{{ number_format($onboarding['progress_percent'] ?? 0, 2, ',', '.') }}%</div></div></div></div>
        <div class="col-12 col-md-6 col-xl-3"><div class="erp-saas-kpi"><div class="card-body"><div class="erp-saas-label">Cobranças</div><div class="erp-saas-value">{{ $money($billing['valor_total'] ?? 0) }}</div></div></div></div>
    </div>

    <div class="row g-3">
        <div class="col-12 col-xl-6"><div class="erp-saas-card"><div class="card-body"><h5 class="erp-saas-section-title">Planos visíveis</h5><div class="erp-saas-table table-responsive"><table class="table table-sm mb-0"><thead><tr><th>Plano</th><th>Valor</th><th>Intervalo</th></tr></thead><tbody>
            @forelse($visible_plans as $plan)
                <tr><td>{{ $plan->nome }}</td><td>{{ $money($plan->valor ?? 0) }}</td><td>{{ $plan->intervalo_dias ?? 30 }} dias</td></tr>
            @empty
                <tr><td colspan="3">Nenhum plano visível.</td></tr>
            @endforelse
        </tbody></table></div></div></div></div>
        <div class="col-12 col-xl-6"><div class="erp-saas-card"><div class="card-body"><h5 class="erp-saas-section-title">Uso por recurso</h5><div class="erp-saas-table table-responsive"><table class="table table-sm mb-0"><thead><tr><th>Recurso</th><th>Usado</th><th>Limite</th><th>Disponível</th><th>Status</th></tr></thead><tbody>
            @forelse($usage as $feature => $info)
                <tr><td>{{ ucfirst($feature) }}</td><td>{{ $info['used'] ?? 0 }}</td><td>{{ $info['limit'] ?? 'ilimitado' }}</td><td>{{ $info['available'] ?? '∞' }}</td><td>{{ ($info['allowed'] ?? true) ? 'OK' : 'LIMITE' }}</td></tr>
            @empty
                <tr><td colspan="5">Sem dados.</td></tr>
            @endforelse
        </tbody></table></div></div></div></div>
    </div>
</div>
@endsection
