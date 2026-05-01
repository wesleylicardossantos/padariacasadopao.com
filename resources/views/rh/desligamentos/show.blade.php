@extends('default.layout',['title' => 'RH - Rescisão'])
@section('content')
<div class="page-content">
    <div class="row g-3">
        <div class="col-12">
            <div class="card"><div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <div>
                        <h6 class="mb-0 text-uppercase">Rescisão de {{ optional($rescisao->funcionario)->nome }}</h6>
                        <small class="text-muted">Desligamento em {{ optional($rescisao->data_rescisao)->format('d/m/Y') }}</small>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <a target="_blank" href="{{ route('rh.documentos.trct.pdf', $rescisao->id) }}" class="btn btn-outline-danger">TRCT</a>
                        <a target="_blank" href="{{ route('rh.documentos.tqrct.pdf', $rescisao->id) }}" class="btn btn-outline-secondary">TQRCT</a>
                        <a target="_blank" href="{{ route('rh.documentos.homologacao.pdf', $rescisao->id) }}" class="btn btn-outline-primary">Homologação</a>
                        <form method="POST" action="{{ route('rh.desligamentos.reativar', $rescisao->id) }}">
                            @csrf
                            <button class="btn btn-success">Reativar funcionário</button>
                        </form>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-md-3"><div class="border rounded p-3"><div class="text-muted">Total bruto</div><h5 class="mb-0">{{ __moeda($rescisao->total_bruto) }}</h5></div></div>
                    <div class="col-md-3"><div class="border rounded p-3"><div class="text-muted">Descontos</div><h5 class="mb-0">{{ __moeda($rescisao->total_descontos) }}</h5></div></div>
                    <div class="col-md-3"><div class="border rounded p-3"><div class="text-muted">Total líquido</div><h5 class="mb-0 text-success">{{ __moeda($rescisao->total_liquido) }}</h5></div></div>
                    <div class="col-md-3"><div class="border rounded p-3"><div class="text-muted">Multa FGTS</div><h5 class="mb-0">{{ __moeda($rescisao->fgts_multa) }}</h5></div></div>
                </div>
            </div></div>
        </div>

        <div class="col-lg-7">
            <div class="card"><div class="card-header bg-light"><strong>Itens da rescisão</strong></div><div class="card-body p-0">
                <div class="table-responsive"><table class="table table-sm table-striped mb-0">
                    <thead><tr><th>Código</th><th>Descrição</th><th>Tipo</th><th class="text-end">Valor</th></tr></thead>
                    <tbody>
                        @forelse($rescisao->itens as $item)
                        <tr>
                            <td>{{ $item->codigo }}</td>
                            <td>{{ $item->descricao }}</td>
                            <td>{{ ucfirst($item->tipo) }}</td>
                            <td class="text-end">{{ __moeda($item->valor) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center">Sem itens gerados.</td></tr>
                        @endforelse
                    </tbody>
                </table></div>
            </div></div>
        </div>
        <div class="col-lg-5">
            <div class="card"><div class="card-header bg-light"><strong>Resumo operacional</strong></div><div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-5">Motivo</dt><dd class="col-sm-7">{{ $rescisao->motivo }}</dd>
                    <dt class="col-sm-5">Tipo aviso</dt><dd class="col-sm-7">{{ ucfirst($rescisao->tipo_aviso ?: '--') }}</dd>
                    <dt class="col-sm-5">Dependentes</dt><dd class="col-sm-7">{{ $rescisao->dependentes_irrf }}</dd>
                    <dt class="col-sm-5">INSS</dt><dd class="col-sm-7">{{ __moeda($rescisao->inss) }}</dd>
                    <dt class="col-sm-5">IRRF</dt><dd class="col-sm-7">{{ __moeda($rescisao->irrf) }}</dd>
                    <dt class="col-sm-5">Base FGTS</dt><dd class="col-sm-7">{{ __moeda($rescisao->fgts_base) }}</dd>
                    <dt class="col-sm-5">Depósito FGTS</dt><dd class="col-sm-7">{{ __moeda($rescisao->fgts_deposito) }}</dd>
                    <dt class="col-sm-5">Observações</dt><dd class="col-sm-7">{{ $rescisao->observacoes ?: '--' }}</dd>
                </dl>
            </div></div>
        </div>
    </div>
</div>
@endsection
