@extends('default.layout',['title' => 'Apuração mensal'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto d-flex flex-wrap gap-2 align-items-end">
                    <form method="POST" action="{{ route('apuracaoMensal.gerar_automatica') }}" class="row g-2 align-items-end">
                        @csrf
                        <div class="col-auto">
                            <label class="form-label mb-1">Mês</label>
                            <input type="number" min="1" max="12" name="mes_competencia" class="form-control" value="{{ now()->month }}" required>
                        </div>
                        <div class="col-auto">
                            <label class="form-label mb-1">Ano</label>
                            <input type="number" min="2000" max="2100" name="ano_competencia" class="form-control" value="{{ now()->year }}" required>
                        </div>
                        <div class="col-auto">
                            <label class="form-label mb-1">Vencimento folha</label>
                            <input type="date" name="vencimento_folha" class="form-control" value="{{ now()->endOfMonth()->format('Y-m-d') }}">
                        </div>
                        <div class="col-auto">
                            <label class="form-label mb-1 d-block">&nbsp;</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" name="sobrescrever" id="sobrescrever_apuracao">
                                <label class="form-check-label" for="sobrescrever_apuracao">Sobrescrever</label>
                            </div>
                        </div>
                        <div class="col-auto">
                            <label class="form-label mb-1 d-block">&nbsp;</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" name="integrar_financeiro" id="integrar_financeiro_apuracao" checked>
                                <label class="form-check-label" for="integrar_financeiro_apuracao">Gerar contas a pagar</label>
                            </div>
                        </div>
                        <div class="col-auto">
                            <label class="form-label mb-1">Após gerar</label>
                            <select name="acao_pos_geracao" class="form-select">
                                <option value="nenhuma">Somente gerar</option>
                                <option value="listar_holerites">Abrir holerites</option>
                                <option value="baixar_zip">Baixar ZIP dos holerites</option>
                            </select>
                        </div>
                        <div class="col-auto">
                            <label class="form-label mb-1 d-block">&nbsp;</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" name="enviar_holerites_email" id="enviar_holerites_email">
                                <label class="form-check-label" for="enviar_holerites_email">Enviar holerites por e-mail</label>
                            </div>
                        </div>
                        <div class="col-auto">
                            <label class="form-label mb-1 d-block">&nbsp;</label>
                            <button type="submit" class="btn btn-dark">
                                <i class="bx bx-refresh"></i> Gerar automática
                            </button>
                        </div>
                    </form>
                    <form method="POST" action="{{ route('apuracaoMensal.integrar_financeiro') }}" class="row g-2 align-items-end">
                        @csrf
                        <input type="hidden" name="mes_competencia" value="{{ request('mes_competencia', now()->month) }}">
                        <input type="hidden" name="ano_competencia" value="{{ request('ano_competencia', now()->year) }}">
                        <input type="hidden" name="vencimento_folha" value="{{ request('vencimento_folha', now()->endOfMonth()->format('Y-m-d')) }}">
                        <div class="col-auto">
                            <label class="form-label mb-1 d-block">&nbsp;</label>
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-wallet"></i> Integrar folha com financeiro
                            </button>
                        </div>
                    </form>
                    <form method="GET" action="{{ route('apuracaoMensal.holerites_competencia') }}" class="row g-2 align-items-end">
                        <input type="hidden" name="mes_competencia" value="{{ request('mes_competencia', now()->month) }}">
                        <input type="hidden" name="ano_competencia" value="{{ request('ano_competencia', now()->year) }}">
                        <div class="col-auto">
                            <label class="form-label mb-1 d-block">&nbsp;</label>
                            <button type="submit" class="btn btn-outline-danger">
                                <i class="bx bxs-file-pdf"></i> Holerites da competência
                            </button>
                        </div>
                    </form>
                    <a href="{{ route('rh.folha.processamento.index') }}" type="button" class="btn btn-outline-dark">
                        <i class="bx bx-cog"></i> Processamento da folha
                    </a>
                    <a href="{{ route('apuracaoMensal.create')}}" type="button" class="btn btn-success">
                        <i class="bx bx-plus"></i> Nova apuração
                    </a>
                </div>
            </div>
            <div class="col">
                <h6 class="mb-0 text-uppercase">Apuração mensal</h6>
                {!! Form::open()->fill(request()->all())->get() !!}
                <div class="row mt-2">
                    <div class="col-md-5">
                        {!! Form::text('nome', 'Nome') !!}
                    </div>
                    <div class="col-md-2">
                        {!! Form::date('start_date', 'Data inicial') !!}
                    </div>
                    <div class="col-md-2">
                        {!! Form::date('end_date', 'Data final') !!}
                    </div>
                    <div class="col-md-3 text-left">
                        <br>
                        <button class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
                        <a id="clear-filter" class="btn btn-danger" href="{{ route('apuracaoMensal.index') }}"><i class="bx bx-eraser"></i> Limpar</a>
                    </div>
                </div>
                {!! Form::close() !!}


                @if(isset($lotesRecentes) && $lotesRecentes->isNotEmpty())
                <div class="card mt-3 border">
                    <div class="card-header bg-light">
                        <strong>Últimos lotes de envio de holerite</strong>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th>Lote</th>
                                        <th>Competência</th>
                                        <th>Status</th>
                                        <th>Enviados</th>
                                        <th>Falhas</th>
                                        <th>Sem e-mail</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($lotesRecentes as $lote)
                                    <tr>
                                        <td>#{{ $lote->id }}</td>
                                        <td>{{ str_pad($lote->mes, 2, '0', STR_PAD_LEFT) }}/{{ $lote->ano }}</td>
                                        <td>{{ ucfirst(str_replace('_', ' ', $lote->status)) }}</td>
                                        <td>{{ $lote->enviados }}</td>
                                        <td>{{ $lote->falhas }}</td>
                                        <td>{{ $lote->sem_email }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endif
                <div class="card mt-3">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table mb-0 table-striped">
                                <thead class="">
                                    <tr>
                                        <th>Funcionário</th>
                                        <th>Data de registro</th>
                                        <th>Valor final</th>
                                        <th>Mês/Ano</th>
                                        <th>Adicionado em contas a pagar</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($data as $item)
                                    <tr>
                                        <td>{{ $item->funcionario->nome }}</td>
                                        <td>{{ $item->created_at }}</td>
                                        <td>{{ __moeda($item->valor_final) }}</td>
                                        <td> {{strtoupper($item->mes)}}/{{$item->ano}} </td>
                                        <td>
                                            <span class="codigo" style="width: 150px;" id="id">
                                                @if($item->conta_pagar_id == 0)
                                                <span class="btn btn-danger btn-sm">Não</span>
                                                @else
                                                <span class="btn btn-success btn-sm">Sim</span>
                                                <a target="_blank" href="/contasPagar/edit/{{$item->conta_pagar_id}}">#{{$item->conta_pagar_id}}</a>
                                                @endif
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                <a target="_blank" href="{{ route('rh.holerite.show', ['id' => $item->funcionario_id, 'mes' => $item->mes, 'ano' => $item->ano]) }}" class="btn btn-sm btn-dark" title="Gerar PDF do holerite">
                                                    <i class="bx bxs-file-pdf"></i>
                                                </a>
                                                <form action="{{ route('apuracaoMensal.destroy', $item->id) }}" method="post" id="form-{{$item->id}}">
                                                    @method('delete')
                                                    @csrf
                                                    <button type="button" class="btn btn-delete btn-sm btn-danger">
                                                        <i class="bx bx-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center">Nenhuma apuração cadastrada para esta empresa. Use <strong>Gerar automática</strong> para criar a competência e, se quiser, já integrar com o financeiro.</td>
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
