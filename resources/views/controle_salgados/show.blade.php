@extends('default.layout',['title' => 'Controle de Salgados'])
@section('content')
<div class="page-content">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h4 class="mb-1">Detalhes do controle de salgados</h4>
            <p class="text-muted mb-0">Visualização completa do lançamento com os dois turnos operacionais.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('controle.salgados.pdf', $item->id) }}" target="_blank" class="btn btn-outline-secondary">PDF</a>
            <a href="{{ route('controle.salgados.edit', $item->id) }}" class="btn btn-primary">Editar</a>
            <a href="{{ route('controle.salgados.index') }}" class="btn btn-light">Voltar</a>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3"><strong>Data:</strong><br>{{ optional($item->data)->format('d/m/Y') }}</div>
                <div class="col-md-3"><strong>Dia:</strong><br>{{ $item->dia ?: '--' }}</div>
                <div class="col-md-6"><strong>Observações:</strong><br>{{ $item->observacoes ?: '--' }}</div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        @foreach(['manha' => 'MANHÃ', 'tarde' => 'TARDE'] as $periodo => $label)
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header bg-light"><strong>{{ $label }}</strong></div>
                    <div class="card-body p-0 table-responsive">
                        <table class="table table-sm table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>QTD</th>
                                    <th>Descrição</th>
                                    <th>Término</th>
                                    <th>Saldo</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($item->itens->where('periodo', $periodo)->sortBy('ordem') as $row)
                                    <tr>
                                        <td>{{ $row->qtd }}</td>
                                        <td>{{ $row->descricao }}</td>
                                        <td>{{ $row->termino }}</td>
                                        <td>{{ $row->saldo }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted py-3">Sem itens cadastrados.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
