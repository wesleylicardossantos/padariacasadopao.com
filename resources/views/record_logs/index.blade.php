@extends('default.layout',['title' => 'Auditoria do Sistema'])
@section('content')
<div class="page-content">
    <div class="card">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h4 class="mb-0">Auditoria do Sistema</h4>
                    <small class="text-muted">Histórico de ações registradas em record_logs.</small>
                </div>
                <a href="{{ route('admin.panel') }}" class="btn btn-secondary">Voltar</a>
            </div>

            <form method="GET" action="{{ route('admin.audit') }}" class="row g-2 mb-3">
                <div class="col-md-3">
                    <label class="form-label">Tipo</label>
                    <select name="tipo" class="form-select">
                        @foreach($tipos as $key => $label)
                        <option value="{{ $key }}" {{ request('tipo') == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tabela</label>
                    <input type="text" name="tabela" class="form-control" value="{{ request('tabela') }}" placeholder="Ex.: venda_caixas">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Data inicial</label>
                    <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Data final</label>
                    <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                </div>
                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button class="btn btn-primary w-100" type="submit">Pesquisar</button>
                    <a href="{{ route('admin.audit') }}" class="btn btn-danger">Limpar</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-striped table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Data</th>
                            <th>Usuário</th>
                            <th>Tipo</th>
                            <th>Tabela</th>
                            <th>Registro</th>
                            <th>Resumo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $item)
                        <tr>
                            <td>{{ $item->id }}</td>
                            <td>{{ __data_pt($item->created_at) }}</td>
                            <td>{{ optional($item->usuario)->nome ?? '---' }}</td>
                            <td>{{ ucfirst($item->tipo) }}</td>
                            <td>{{ $item->tabela }}</td>
                            <td>{{ $item->registro_id }}</td>
                            <td>{{ method_exists($item, 'registro') ? $item->registro() : $item->registro_id }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center">Nenhum registro encontrado</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $data->appends(request()->all())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
