@extends('default.layout', ['title' => 'Excluir Movimentações'])

@section('content')
<div class="page-content">
    <div class="card">
        <div class="card-body p-4">
            <h6 class="mb-3 text-uppercase">Excluir movimentações do dia: <strong>{{ $dataView }}</strong></h6>

            <div class="alert alert-warning">
                Selecione quais itens deseja excluir para esta data. Esta ação remove os registros selecionados.
            </div>

            <form method="POST" action="{{ route('fluxoCaixa.excluirSubmit', $date) }}">
                @csrf
                @method('DELETE')

                <div class="row">
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-header">Vendas</div>
                            <div class="card-body">
                                @forelse($vendas as $v)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="vendas[]" value="{{ $v->id }}" id="venda_{{ $v->id }}">
                                        <label class="form-check-label" for="venda_{{ $v->id }}">
                                            #{{ $v->id }} - {{ __moeda($v->valor_total) }} - {{ __data_pt($v->data_registro, 1) }}
                                        </label>
                                    </div>
                                @empty
                                    <span class="text-muted">Nenhuma venda.</span>
                                @endforelse
                            </div>
                        </div>

                        <div class="card mb-3">
                            <div class="card-header">Frente de Caixa</div>
                            <div class="card-body">
                                @forelse($vendasCaixa as $v)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="vendas_caixa[]" value="{{ $v->id }}" id="venda_caixa_{{ $v->id }}">
                                        <label class="form-check-label" for="venda_caixa_{{ $v->id }}">
                                            #{{ $v->id }} - {{ __moeda($v->valor_total) }} - {{ __data_pt($v->data_registro, 1) }}
                                        </label>
                                    </div>
                                @empty
                                    <span class="text-muted">Nenhuma venda de frente de caixa.</span>
                                @endforelse
                            </div>
                        </div>

                        <div class="card mb-3">
                            <div class="card-header">Ordens de Serviço</div>
                            <div class="card-body">
                                @forelse($ordensServico as $o)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="ordens_servico[]" value="{{ $o->id }}" id="os_{{ $o->id }}">
                                        <label class="form-check-label" for="os_{{ $o->id }}">
                                            #{{ $o->id }} - {{ __moeda($o->valor) }} - {{ __data_pt($o->updated_at, 1) }}
                                        </label>
                                    </div>
                                @empty
                                    <span class="text-muted">Nenhuma OS.</span>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-header">Contas Recebidas</div>
                            <div class="card-body">
                                @forelse($contasReceber as $c)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="contas_receber[]" value="{{ $c->id }}" id="cr_{{ $c->id }}">
                                        <label class="form-check-label" for="cr_{{ $c->id }}">
                                            #{{ $c->id }} - {{ __moeda($c->valor_recebido) }} - {{ __data_pt($c->data_recebimento, 1) }}
                                        </label>
                                    </div>
                                @empty
                                    <span class="text-muted">Nenhuma conta recebida.</span>
                                @endforelse
                            </div>
                        </div>

                        <div class="card mb-3">
                            <div class="card-header">Contas Pagas</div>
                            <div class="card-body">
                                @forelse($contasPagar as $c)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="contas_pagar[]" value="{{ $c->id }}" id="cp_{{ $c->id }}">
                                        <label class="form-check-label" for="cp_{{ $c->id }}">
                                            #{{ $c->id }} - {{ __moeda($c->valor_pago) }} - {{ __data_pt($c->data_pagamento, 1) }}
                                        </label>
                                    </div>
                                @empty
                                    <span class="text-muted">Nenhuma conta paga.</span>
                                @endforelse
                            </div>
                        </div>

                        <div class="card mb-3">
                            <div class="card-header">Créditos (Suprimento de Caixa)</div>
                            <div class="card-body">
                                @forelse($suprimentos as $s)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="suprimentos[]" value="{{ $s->id }}" id="suprimento_{{ $s->id }}">
                                        <label class="form-check-label" for="suprimento_{{ $s->id }}">
                                            #{{ $s->id }} - {{ __moeda($s->valor) }} - {{ __data_pt($s->created_at, 1) }}
                                            @if(!empty($s->observacao))
                                                - {{ $s->observacao }}
                                            @endif
                                        </label>
                                    </div>
                                @empty
                                    <span class="text-muted">Nenhum crédito de caixa.</span>
                                @endforelse
                            </div>
                        </div>

                        <div class="card mb-3">
                            <div class="card-header">Débitos (Sangria de Caixa)</div>
                            <div class="card-body">
                                @forelse($sangrias as $s)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="sangrias[]" value="{{ $s->id }}" id="sangria_{{ $s->id }}">
                                        <label class="form-check-label" for="sangria_{{ $s->id }}">
                                            #{{ $s->id }} - {{ __moeda($s->valor) }} - {{ __data_pt($s->created_at, 1) }}
                                            @if(!empty($s->observacao))
                                                - {{ $s->observacao }}
                                            @endif
                                        </label>
                                    </div>
                                @empty
                                    <span class="text-muted">Nenhum débito de caixa.</span>
                                @endforelse
                            </div>
                        </div>

                        <div class="card mb-3">
                            <div class="card-header">Créditos de Venda</div>
                            <div class="card-body">
                                @forelse($creditos as $c)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="creditos[]" value="{{ $c->id }}" id="cv_{{ $c->id }}">
                                        <label class="form-check-label" for="cv_{{ $c->id }}">
                                            #{{ $c->id }} - {{ __moeda($c->valor) }} - {{ __data_pt($c->updated_at, 1) }}
                                        </label>
                                    </div>
                                @empty
                                    <span class="text-muted">Nenhum crédito de venda.</span>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-danger">
                        <i class="bx bx-trash"></i> Excluir selecionados
                    </button>
                    <a href="{{ route('fluxoCaixa.index') }}" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
