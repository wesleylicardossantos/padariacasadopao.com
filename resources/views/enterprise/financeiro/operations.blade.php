@extends('default.layout')
@section('content')
<div class="container py-4">
    <h2>Financeiro Enterprise - Operações</h2>
    <p>Empresa {{ $empresa_id }} | Filial {{ $filial_id }}</p>

    <div class="row">
        <div class="col-md-3"><div class="card mb-3"><div class="card-body"><strong>Saldo previsto</strong><br>R$ {{ number_format($snapshot['saldo_previsto'] ?? 0, 2, ',', '.') }}</div></div></div>
        <div class="col-md-3"><div class="card mb-3"><div class="card-body"><strong>Risco de caixa</strong><br>{{ strtoupper($snapshot['risco_caixa'] ?? 'n/a') }}</div></div></div>
        <div class="col-md-3"><div class="card mb-3"><div class="card-body"><strong>Receber vencido</strong><br>R$ {{ number_format($aging['receber_vencido'] ?? 0, 2, ',', '.') }}</div></div></div>
        <div class="col-md-3"><div class="card mb-3"><div class="card-body"><strong>Pagar vencido</strong><br>R$ {{ number_format($aging['pagar_vencido'] ?? 0, 2, ',', '.') }}</div></div></div>
    </div>

    <h4>Fechamento mensal</h4>
    <pre>{{ json_encode($closure, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre>

    <h4>Fluxo de caixa projetado</h4>
    <pre>{{ json_encode($cashFlow, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre>
</div>
@endsection
