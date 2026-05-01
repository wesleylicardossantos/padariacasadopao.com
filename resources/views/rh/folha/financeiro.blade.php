@extends('default.layout',['title' => 'RH - Impacto Financeiro'])
@section('content')
@php
    $folha = (float) ($folha ?? 0);
    $totalReceber = (float) ($totalReceber ?? 0);
    $totalPagar = (float) ($totalPagar ?? 0);
    $pesoFolha = (float) ($pesoFolha ?? 0);
    $ultimoFechamento = $ultimoFechamento ?? null;
@endphp
<style>
.rh-card{border:1px solid #e8edf5;border-radius:16px;box-shadow:0 8px 24px rgba(15,23,42,.04)}
</style>
<div class="page-content">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h5 class="mb-0">RH V6 - Impacto Financeiro</h5>
            <small class="text-muted">Relação entre folha, contas a pagar e contas a receber.</small>
        </div>
        <a href="/rh/folha" class="btn btn-secondary">Voltar para Folha</a>
    </div>

    <div class="row g-3">
        <div class="col-lg-4"><div class="card rh-card"><div class="card-body"><div class="text-muted text-uppercase small">Folha Base</div><div class="fs-3 fw-bold">R$ {{ number_format((float)$folha,2,',','.') }}</div></div></div></div>
        <div class="col-lg-4"><div class="card rh-card"><div class="card-body"><div class="text-muted text-uppercase small">Contas a Receber</div><div class="fs-3 fw-bold">R$ {{ number_format((float)$totalReceber,2,',','.') }}</div></div></div></div>
        <div class="col-lg-4"><div class="card rh-card"><div class="card-body"><div class="text-muted text-uppercase small">Contas a Pagar</div><div class="fs-3 fw-bold">R$ {{ number_format((float)$totalPagar,2,',','.') }}</div></div></div></div>
    </div>

    <div class="card rh-card mt-3">
        <div class="card-body">
            <h6 class="mb-3">Indicadores</h6>
            <p class="mb-1">Peso da folha sobre contas a receber abertas:</p>
            <h3>{{ number_format((float)$pesoFolha,2,',','.') }}%</h3>
            @if($ultimoFechamento)
                <hr>
                <p class="mb-1">Último fechamento registrado:</p>
                <strong>{{ str_pad($ultimoFechamento->mes,2,'0',STR_PAD_LEFT) }}/{{ $ultimoFechamento->ano }}</strong>
                — Líquido: <strong>R$ {{ number_format((float)$ultimoFechamento->liquido_total,2,',','.') }}</strong>
                — Conta a pagar #{{ $ultimoFechamento->conta_pagar_id ?? '-' }}
            @endif
        </div>
    </div>
</div>
@endsection