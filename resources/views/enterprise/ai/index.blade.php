@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h1 class="mb-3">IA de Negócio</h1>
    <p class="text-muted">Previsões, recomendações operacionais e detecção de anomalias do PDV.</p>

    <div class="row g-3">
        <div class="col-md-4">
            <div class="card"><div class="card-body">
                <h5>Previsão 30 dias</h5>
                <strong>R$ {{ number_format($forecast['projected_next_30_days'] ?? 0, 2, ',', '.') }}</strong>
                <div class="small text-muted">Confiança: {{ $forecast['confidence'] ?? 'baixa' }}</div>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card"><div class="card-body">
                <h5>Risco de caixa</h5>
                <strong>{{ strtoupper($cash_risk['risk'] ?? 'baixo') }}</strong>
                <div class="small text-muted">Cobertura: {{ $cash_risk['coverage_ratio'] ?? '-' }}</div>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card"><div class="card-body">
                <h5>Anomalias PDV</h5>
                <strong>{{ count($pdv_anomalies ?? []) }}</strong>
                <div class="small text-muted">Última geração: {{ $generated_at ?? '-' }}</div>
            </div></div>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-md-6">
            <div class="card"><div class="card-body">
                <h5>Recomendações</h5>
                <ul class="mb-0">
                    @forelse(($recommendations ?? []) as $item)
                        <li><strong>{{ $item['title'] ?? 'Recomendação' }}:</strong> {{ $item['message'] ?? '' }}</li>
                    @empty
                        <li>Nenhuma recomendação disponível.</li>
                    @endforelse
                </ul>
            </div></div>
        </div>
        <div class="col-md-6">
            <div class="card"><div class="card-body">
                <h5>Anomalias operacionais</h5>
                <ul class="mb-0">
                    @forelse(($pdv_anomalies ?? []) as $item)
                        <li><strong>{{ strtoupper($item['severity'] ?? 'info') }}:</strong> {{ $item['message'] ?? '' }}</li>
                    @empty
                        <li>Sem anomalias detectadas.</li>
                    @endforelse
                </ul>
            </div></div>
        </div>
    </div>
</div>
@endsection
