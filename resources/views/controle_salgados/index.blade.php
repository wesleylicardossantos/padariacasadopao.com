@extends('default.layout',['title' => 'Controle de Salgados'])
@section('content')
<div class="page-content">
    <style>
        .salgado-card{border:1px solid #e9edf5;border-radius:18px;box-shadow:0 10px 30px rgba(15,23,42,.05)}
        .salgado-kpi{border:1px solid #eef2f7;border-radius:16px;background:linear-gradient(135deg,#fff 0%,#fbfcff 100%)}
        .salgado-kpi .label{font-size:.78rem;text-transform:uppercase;letter-spacing:.04em;color:#6c757d;font-weight:700}
        .salgado-kpi .value{font-size:1.6rem;font-weight:800;color:#0f172a}
    </style>

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h4 class="mb-1">Controle de Salgados</h4>
            <p class="text-muted mb-0">Listagem dos lançamentos operacionais com saída em PDF, edição e exclusão segura por empresa.</p>
        </div>
        <a href="{{ route('controle.salgados.create') }}" class="btn btn-primary">Novo lançamento</a>
    </div>


    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="card salgado-kpi h-100"><div class="card-body"><div class="label">Lançamentos</div><div class="value">{{ $data->total() }}</div><small class="text-muted">Total filtrado pela consulta atual.</small></div></div>
        </div>
        <div class="col-md-4">
            <div class="card salgado-kpi h-100"><div class="card-body"><div class="label">Página atual</div><div class="value">{{ $data->currentPage() }}</div><small class="text-muted">Paginação pronta para operação em escala.</small></div></div>
        </div>
        <div class="col-md-4">
            <div class="card salgado-kpi h-100"><div class="card-body"><div class="label">Período</div><div class="value">{{ ($filters['data_inicio'] || $filters['data_fim']) ? 'Filtrado' : 'Completo' }}</div><small class="text-muted">Use datas para fechar produção do intervalo.</small></div></div>
        </div>
    </div>

    <div class="card salgado-card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('controle.salgados.index') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Data inicial</label>
                    <input type="date" name="data_inicio" class="form-control" value="{{ $filters['data_inicio'] }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Data final</label>
                    <input type="date" name="data_fim" class="form-control" value="{{ $filters['data_fim'] }}">
                </div>
                <div class="col-md-6 d-flex gap-2">
                    <button class="btn btn-primary">Filtrar</button>
                    <a href="{{ route('controle.salgados.index') }}" class="btn btn-light">Limpar</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card salgado-card">
        <div class="card-body table-responsive">
            <table class="table table-striped align-middle mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Data</th>
                        <th>Dia</th>
                        <th>Itens manhã</th>
                        <th>Itens tarde</th>
                        <th>Cadastrado em</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $row)
                        <tr>
                            <td>{{ $row->id }}</td>
                            <td>{{ optional($row->data)->format('d/m/Y') }}</td>
                            <td>{{ $row->dia ?: '--' }}</td>
                            <td>{{ $row->itens_manha_count }}</td>
                            <td>{{ $row->itens_tarde_count }}</td>
                            <td>{{ optional($row->created_at)->format('d/m/Y H:i') }}</td>
                            <td class="text-end">
                                <div class="btn-group">
                                    <a href="{{ route('controle.salgados.show', $row->id) }}" class="btn btn-sm btn-light">Ver</a>
                                    <a href="{{ route('controle.salgados.edit', $row->id) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                                    <a href="{{ route('controle.salgados.pdf', $row->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary">PDF</a>
                                    <form method="POST" action="{{ route('controle.salgados.destroy', $row->id) }}" onsubmit="return confirm('Deseja remover este lançamento?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Excluir</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">Nenhum lançamento encontrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($data, 'links'))
        <div class="card-footer bg-white">
            {{ $data->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
