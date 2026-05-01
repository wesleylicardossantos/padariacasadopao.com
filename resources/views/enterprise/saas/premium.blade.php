@extends('default.layout', ['title' => 'SaaS Premium Center'])

@section('content')
@php
    $money = fn($v) => 'R$ ' . number_format((float) ($v ?? 0), 2, ',', '.');
    $percent = fn($v) => number_format((float) ($v ?? 0), 2, ',', '.') . '%';
@endphp
<div class="erp-saas-page">
    <div class="erp-saas-header">
        <div>
            <h2 class="erp-saas-title">SaaS Premium Center</h2>
            <p class="erp-saas-subtitle">Empresa {{ $empresaId ?? request('empresa_id') }} · Atualizado em {{ $updatedAt ?? now()->format('d/m/Y H:i:s') }}</p>
        </div>
        <div><a href="javascript:history.back()" class="btn btn-sm btn-outline-secondary">Voltar</a></div>
    </div>

    <form class="row g-2 mb-3">
        <div class="col-md-2"><input type="number" class="form-control" name="mes" value="{{ $mes }}" min="1" max="12"></div>
        <div class="col-md-2"><input type="number" class="form-control" name="ano" value="{{ $ano }}"></div>
        <div class="col-md-2"><button class="btn btn-primary w-100">Atualizar</button></div>
    </form>

    <div class="row g-3 mb-4">
        <div class="col-12 col-md-6 col-xl-2"><div class="erp-saas-kpi"><div class="card-body"><div class="erp-saas-label">Receita Premium</div><div class="erp-saas-value">{{ $money($premiumSummary['mr_receita'] ?? 0) }}</div></div></div></div>
        <div class="col-12 col-md-6 col-xl-2"><div class="erp-saas-kpi"><div class="card-body"><div class="erp-saas-label">Lucro Premium</div><div class="erp-saas-value">{{ $money($premiumSummary['mr_lucro'] ?? 0) }}</div></div></div></div>
        <div class="col-12 col-md-6 col-xl-2"><div class="erp-saas-kpi"><div class="card-body"><div class="erp-saas-label">Margem</div><div class="erp-saas-value">{{ $percent($premiumSummary['mr_margem'] ?? 0) }}</div></div></div></div>
        <div class="col-12 col-md-6 col-xl-2"><div class="erp-saas-kpi"><div class="card-body"><div class="erp-saas-label">Alertas</div><div class="erp-saas-value">{{ $premiumSummary['alerts_open'] ?? 0 }}</div></div></div></div>
        <div class="col-12 col-md-6 col-xl-2"><div class="erp-saas-kpi"><div class="card-body"><div class="erp-saas-label">Notificações</div><div class="erp-saas-value">{{ $premiumSummary['notifications'] ?? 0 }}</div></div></div></div>
        <div class="col-12 col-md-6 col-xl-2"><div class="erp-saas-kpi"><div class="card-body"><div class="erp-saas-label">Automação</div><div class="erp-saas-value">{{ ($automation['usage_snapshot_created'] ?? false) ? 'OK' : 'PENDENTE' }}</div></div></div></div>
    </div>

    <div class="row g-3">
        <div class="col-12 col-xl-7"><div class="erp-saas-card"><div class="card-body"><h5 class="erp-saas-section-title">Analytics Premium</h5><div class="erp-saas-table table-responsive"><table class="table table-sm mb-0"><thead><tr><th>Período</th><th>Receita</th><th>Lucro</th></tr></thead><tbody>
            @foreach($trend as $point)
                <tr><td>{{ $point['period'] }}</td><td>{{ $money($point['receita'] ?? 0) }}</td><td>{{ $money($point['lucro'] ?? 0) }}</td></tr>
            @endforeach
        </tbody></table></div></div></div></div>
        <div class="col-12 col-xl-5"><div class="erp-saas-card"><div class="card-body"><h5 class="erp-saas-section-title">Central de notificações</h5>
            @forelse($notifications as $item)
                <div class="border rounded p-2 mb-2"><div class="d-flex justify-content-between"><strong>{{ $item['title'] }}</strong><small class="text-muted">{{ $item['channel'] }}</small></div><div class="small text-muted mb-1">{{ $item['created_at'] }}</div><div>{{ $item['message'] }}</div></div>
            @empty
                <div class="alert alert-success mb-0">Nenhuma notificação premium aberta.</div>
            @endforelse
        </div></div></div>
    </div>
</div>
@endsection
