@extends('default.layout',['title' => 'RH - Dashboard Executivo de Rescisões'])
@section('content')
<div class="page-content">
    <div class="card"><div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
            <div>
                <h6 class="mb-0 text-uppercase">Dashboard executivo de rescisões</h6>
                <small class="text-muted">Fase 4 integrada com desligamentos, portal e exportação operacional.</small>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('rh.desligamentos.exportar_fgts') }}" class="btn btn-outline-success">Exportar FGTS/SEFIP</a>
                <a href="{{ route('rh.desligamentos.index') }}" class="btn btn-secondary">Voltar</a>
            </div>
        </div>
        <div class="row g-3 mb-4">
            <div class="col-md-3"><div class="border rounded p-3"><div class="text-muted">Total de rescisões</div><h3 class="mb-0">{{ $total }}</h3></div></div>
            <div class="col-md-3"><div class="border rounded p-3"><div class="text-muted">Rescisões no mês</div><h3 class="mb-0">{{ $mes }}</h3></div></div>
            <div class="col-md-3"><div class="border rounded p-3"><div class="text-muted">Líquido no mês</div><h3 class="mb-0 text-success">{{ __moeda($liquido_mes) }}</h3></div></div>
            <div class="col-md-3"><div class="border rounded p-3"><div class="text-muted">FGTS/multa mês</div><h3 class="mb-0">{{ __moeda($fgts_mes) }}</h3></div></div>
        </div>
        <div class="table-responsive"><table class="table table-striped">
            <thead><tr><th>Funcionário</th><th>Data</th><th>Motivo</th><th class="text-end">Total líquido</th><th></th></tr></thead>
            <tbody>
                @forelse($desligamentos_recentes as $item)
                    <tr>
                        <td>{{ optional($item->funcionario)->nome }}</td>
                        <td>{{ optional($item->data_rescisao)->format('d/m/Y') }}</td>
                        <td>{{ $item->motivo }}</td>
                        <td class="text-end">{{ __moeda($item->total_liquido) }}</td>
                        <td class="text-end"><a href="{{ route('rh.desligamentos.show', $item->id) }}" class="btn btn-sm btn-outline-primary">Abrir</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center">Nenhuma rescisão processada.</td></tr>
                @endforelse
            </tbody>
        </table></div>
    </div></div>
</div>
@endsection
