@extends('default.layout',['title' => 'RH - Cálculo de Férias'])
@section('content')
<div class="page-content">
    <div class="card">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <div>
                    <h6 class="mb-0 text-uppercase">RH - Cálculo automático de férias</h6>
                    <small class="text-muted">Estimativa de avos e período aquisitivo por funcionário.</small>
                </div>
                <a href="/rh/dashboard-v4" class="btn btn-secondary">Voltar</a>
            </div>

            <div class="table-responsive">
                <table class="table table-striped">
                    <thead><tr><th>Funcionário</th><th>Data admissão</th><th>Período aquisitivo</th><th>Avos</th><th>Última férias</th></tr></thead>
                    <tbody>
                        @forelse($data as $item)
                        <tr>
                            <td>{{ $item['funcionario']->nome }}</td>
                            <td>{{ $item['data_admissao'] ? $item['data_admissao']->format('d/m/Y') : '-' }}</td>
                            <td>{{ $item['periodo_inicio'] ? $item['periodo_inicio']->format('d/m/Y') : '-' }} até {{ $item['periodo_fim'] ? $item['periodo_fim']->format('d/m/Y') : '-' }}</td>
                            <td>{{ $item['avos'] }}/12</td>
                            <td>
                                @if($item['ultima_ferias'])
                                    {{ \Carbon\Carbon::parse($item['ultima_ferias']->data_inicio)->format('d/m/Y') }} até {{ \Carbon\Carbon::parse($item['ultima_ferias']->data_fim)->format('d/m/Y') }}
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center">Nenhum funcionário encontrado.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
