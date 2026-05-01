@extends('default.layout', ['title' => 'Fluxo de Caixa'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
            </div>
            <div class="col">
                <h6 class="mb-0 text-uppercase">MOVIMENTAÇÃO DE CAIXA</h6>
                {!! Form::open()->fill(request()->all())->get() !!}
                <div class="row">
                    <div class="col-md-3">
                        {!! Form::date('start_date', 'Data inicial') !!}
                    </div>
                    <div class="col-md-3">
                        {!! Form::date('end_date', 'Data final') !!}
                    </div>
                    <div class="col-md-6 text-left ">
                        <br>
                        <button class="btn btn-primary" type="submit"><i class="bx bx-search"></i> Pesquisar</button>
                        <a id="clear-filter" class="btn btn-danger" href="{{ route('fluxoCaixa.index') }}"><i class="bx bx-eraser"></i> Limpar</a>
                        <div class="btn-group">
                            <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bx bx-plus"></i> Adicionar
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#modal-suprimento_caixa"><i class="bx bx-plus-circle text-success"></i> Crédito</a></li>
                                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#modal-sangria_caixa"><i class="bx bx-minus-circle text-danger"></i> Débito</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                {!! Form::close() !!}
                <hr />
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table mb-0 table-striped">
                                <thead class="">
                                    <tr>
                                        <th>Data</th>
                                        <th>Vendas</th>
                                        <th>Frente de caixa</th>
                                        <th>Soma de vendas</th>
                                        <th>Contas recebidas</th>
                                        <th>Créditos</th>
                                        <th>Débitos</th>
                                        <th>Ordem de serviço</th>
                                        <th>Contas pagas</th>
                                        <th>Resultado</th>
                                        <th class="text-center">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($fluxo as $item)
                                    <tr>
                                        <td>{{ $item['data'] }}</td>
                                        <td>{{ __moeda($item['venda']) }}</td>
                                        <td>{{ __moeda($item['venda_caixa']) }}</td>
                                        <td>{{ __moeda($item['venda'] + $item['venda_caixa']) }}</td>
                                        <td>{{ __moeda($item['conta_receber']) }}</td>
                                        <td>{{ __moeda($item['suprimento'] ?? 0) }}</td>
                                        <td>{{ __moeda($item['sangria'] ?? 0) }}</td>
                                        <td>{{ __moeda($item['os']) }}</td>
                                        <td>{{ __moeda($item['conta_pagar']) }}</td>
                                        <td>{{ __moeda(($item['venda'] + $item['venda_caixa'] + $item['conta_receber'] + ($item['suprimento'] ?? 0) + $item['os']) - (($item['sangria'] ?? 0) + $item['conta_pagar'])) }}</td>
                                        <td class="text-center" style="min-width: 130px;">
                                            <div class="dropdown">
                                                <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    Ações
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <button type="button" class="dropdown-item btn-detalhar-dia" title="Visualizar" data-url="{{ route('fluxoCaixa.detalharDia', $item['data_raw'] ?? '') }}" data-bs-toggle="modal" data-bs-target="#modalDetalharDia">
                                                            <i class="bx bx-show"></i> Visualizar
                                                        </button>
                                                    </li>
                                                    @if(session('user_logged.super') ?? false)
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <a class="dropdown-item text-danger" title="Excluir itens do dia" href="{{ route('fluxoCaixa.excluirForm', $item['data_raw'] ?? '') }}">
                                                            <i class="bx bx-trash"></i> Excluir
                                                        </a>
                                                    </li>
                                                    @endif
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="11" class="text-center">Nada encontrado</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@include('modals.frontBox._suprimento_caixa')
@include('modals.frontBox._sangria_caixa')

<div class="modal fade" id="modalDetalharDia" tabindex="-1" aria-labelledby="modalDetalharDiaLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDetalharDiaLabel">Detalhamento da movimentação</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modalDetalharDiaBody">
                <div class="text-center p-4 text-muted">Selecione um dia para visualizar os detalhes.</div>
            </div>
        </div>
    </div>
</div>

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalEl = document.getElementById('modalDetalharDia');
    const modalBody = document.getElementById('modalDetalharDiaBody');
    const loadingHtml = '<div class=\"text-center p-5 text-muted\"><div class=\"spinner-border text-primary mb-3\" role=\"status\"></div><div>Carregando detalhamento...</div></div>';
    const emptyHtml = '<div class=\"alert alert-danger m-3 mb-0\">Não foi possível carregar o detalhamento deste dia.</div>';

    document.querySelectorAll('.btn-detalhar-dia').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const url = this.getAttribute('data-url');
            if (!modalBody || !url) return;
            modalBody.innerHTML = loadingHtml;

            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html'
                }
            })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Erro ao carregar detalhamento');
                }
                return response.text();
            })
            .then(function (html) {
                modalBody.innerHTML = html;
            })
            .catch(function () {
                modalBody.innerHTML = emptyHtml;
            });
        });
    });

    if (modalEl) {
        modalEl.addEventListener('hidden.bs.modal', function () {
            if (modalBody) {
                modalBody.innerHTML = '<div class=\"text-center p-4 text-muted\">Selecione um dia para visualizar os detalhes.</div>';
            }
        });
    }
});
</script>
@endsection
