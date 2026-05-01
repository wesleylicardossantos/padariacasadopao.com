@extends('default.layout', ['title' => 'Observability Center'])

@section('content')
@php
    $summary = $summary ?? [];
    $labelize = fn($label) => strtoupper(str_replace('_', ' ', (string) $label));
    $formatValue = function ($value) {
        if (is_bool($value)) return $value ? 'SIM' : 'NÃO';
        if (is_numeric($value)) return is_float($value + 0) ? number_format((float) $value, 2, ',', '.') : number_format((int) $value, 0, ',', '.');
        if (is_array($value)) return count($value) . ' registros';
        if (is_object($value)) return 'Objeto';
        return $value ?: '-';
    };
@endphp

<div class="erp-saas-page">
    <div class="erp-saas-header">
        <div>
            <h2 class="erp-saas-title">Observability Center</h2>
            <p class="erp-saas-subtitle">Resumo de observabilidade e saúde operacional.</p>
        </div>
        <div><a href="javascript:history.back()" class="btn btn-sm btn-outline-secondary">Voltar</a></div>
    </div>

    <div class="row g-3">
        @forelse($summary as $label => $value)
            <div class="col-12 col-md-6 col-xl-4">
                <div class="erp-saas-kpi">
                    <div class="card-body">
                        <div class="erp-saas-label">{{ $labelize($label) }}</div>
                        <div class="erp-saas-value">{{ $formatValue($value) }}</div>
                        @if(is_array($value) && !empty($value))
                            <div class="erp-saas-table mt-3">
                                <table class="table table-sm mb-0">
                                    <tbody>
                                        @foreach(array_slice($value, 0, 5, true) as $itemLabel => $itemValue)
                                            <tr>
                                                <td>{{ str_replace('_', ' ', (string) $itemLabel) }}</td>
                                                <td class="text-end fw-bold">{{ is_scalar($itemValue) ? $formatValue($itemValue) : (is_array($itemValue) ? count($itemValue) . ' itens' : '-') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12"><div class="alert alert-info mb-0">Nenhum dado de observabilidade disponível para este tenant.</div></div>
        @endforelse
    </div>
</div>
@endsection
