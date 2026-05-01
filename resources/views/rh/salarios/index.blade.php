@extends('default.layout',['title' => 'RH - Salários'])
@section('content')
<style>.rh-card{border:1px solid #e9edf5;border-radius:14px;box-shadow:0 8px 24px rgba(15,23,42,.04)}</style>
<div class="page-content">
    <div class="card rh-card">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <div>
                    <h6 class="mb-0 text-uppercase">RH - Salários</h6>
                    <small class="text-muted">Gestão salarial com reajuste registrado em histórico.</small>
                </div>
                <a href="{{ route('rh.salarios.create') }}" class="btn btn-success"><i class="bx bx-plus"></i> Novo reajuste</a>
            </div>

            <form method="GET" action="{{ route('rh.salarios.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Pesquisar por nome</label>
                        <input type="text" class="form-control" name="nome" value="{{ $nome ?? '' }}">
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary w-100">Filtrar</button>
                    </div>
                </div>
            </form>

            <hr>

            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Funcionário</th>
                            <th>Função</th>
                            <th>Salário atual</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $item)
                        <tr>
                            <td>{{ $item->nome }}</td>
                            <td>{{ $item->funcao ?? '-' }}</td>
                            <td>R$ {{ number_format((float)$item->salario, 2, ',', '.') }}</td>
                            <td>{!! (!isset($item->ativo) || $item->ativo) ? '<span class="badge bg-success">Ativo</span>' : '<span class="badge bg-danger">Inativo</span>' !!}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center">Nenhum funcionário encontrado.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {!! $data->appends(request()->all())->links() !!}
        </div>
    </div>
</div>
@endsection
