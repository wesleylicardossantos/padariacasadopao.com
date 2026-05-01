<div class="p-2 p-md-3">
    <style>
        .fcx-detalhe .fcx-topo h6{font-size:1rem;letter-spacing:.02em}
        .fcx-detalhe .fcx-topo small{font-size:.82rem}
        .fcx-detalhe .row{--bs-gutter-x: .9rem;--bs-gutter-y: .9rem}
        .fcx-detalhe .fcx-bloco{border:1px solid #e9ecef;border-radius:.55rem;overflow:hidden;background:#fff;height:100%;box-shadow:0 .125rem .35rem rgba(0,0,0,.04)}
        .fcx-detalhe .fcx-bloco-titulo{padding:.55rem .75rem;background:#f8f9fa;border-bottom:1px solid #e9ecef;font-weight:700;color:#495057;display:flex;align-items:center;justify-content:space-between;gap:.5rem}
        .fcx-detalhe .fcx-bloco-corpo{padding:.5rem .75rem}
        .fcx-detalhe .table{margin-bottom:0}
        .fcx-detalhe .table th,
        .fcx-detalhe .table td{padding:.38rem .45rem;vertical-align:top}
        .fcx-detalhe .table thead th{font-size:.78rem;text-transform:uppercase;letter-spacing:.02em;white-space:nowrap;background:#fbfbfc}
        .fcx-detalhe .fcx-id{width:48px;white-space:nowrap;color:#6c757d}
        .fcx-detalhe .fcx-valor{width:120px;white-space:nowrap;text-align:end}
        .fcx-detalhe .fcx-vazio{color:#6c757d;font-size:.9rem;margin:0}
        .fcx-detalhe .fcx-cliente{font-weight:600;line-height:1.2;margin-bottom:.12rem}
        .fcx-detalhe .fcx-desc{font-size:.84rem;color:#6c757d;line-height:1.25;margin-bottom:.22rem}
        .fcx-detalhe .fcx-produtos{list-style:none;padding-left:0;margin:0}
        .fcx-detalhe .fcx-produtos li{font-size:.84rem;line-height:1.25;padding:.08rem 0}
        .fcx-detalhe .fcx-produtos .qtd,
        .fcx-detalhe .fcx-produtos .obs{color:#6c757d}
        .fcx-detalhe .fcx-total-badge{font-size:.75rem;padding:.3rem .45rem;border-radius:999px;font-weight:700}
        .fcx-detalhe .fcx-total-entrada{background:#eaf8ee;color:#198754}
        .fcx-detalhe .fcx-total-saida{background:#fdecec;color:#dc3545}
        .fcx-detalhe .fcx-total-neutro{background:#eef2f7;color:#495057}
        .fcx-detalhe .fcx-total-row td{font-weight:700;background:#fafafa;border-top:2px solid #eceff3}
        .fcx-detalhe .fcx-resumo{display:grid;grid-template-columns:repeat(3, minmax(0, 1fr));gap:.75rem;margin-top:.95rem}
        .fcx-detalhe .fcx-resumo-card{border-radius:.6rem;padding:.75rem .9rem;color:#fff}
        .fcx-detalhe .fcx-resumo-card small{display:block;opacity:.9;letter-spacing:.02em;text-transform:uppercase;font-size:.72rem}
        .fcx-detalhe .fcx-resumo-card strong{display:block;font-size:1.15rem;line-height:1.2;margin-top:.2rem}
        .fcx-detalhe .fcx-resumo-entrada{background:#198754}
        .fcx-detalhe .fcx-resumo-saida{background:#dc3545}
        .fcx-detalhe .fcx-resumo-saldo.pos{background:#0d6efd}
        .fcx-detalhe .fcx-resumo-saldo.neg{background:#6f42c1}
        .fcx-detalhe .fcx-resumo-saldo.zero{background:#6c757d}
        .fcx-detalhe .text-entrada{color:#198754;font-weight:700}
        .fcx-detalhe .text-saida{color:#dc3545;font-weight:700}
        @media (max-width: 768px){
            .fcx-detalhe .fcx-valor{width:95px}
            .fcx-detalhe .table th,
            .fcx-detalhe .table td{padding:.34rem}
            .fcx-detalhe .fcx-resumo{grid-template-columns:1fr}
        }
    </style>

    @php
        $blocos = [
            'Vendas' => $vendas,
            'Frente de caixa' => $vendasCaixa,
            'Contas recebidas' => $contasReceber,
            'Créditos' => $suprimentos,
            'Débitos' => $sangrias,
            'Ordens de serviço' => $ordensServico,
            'Contas pagas' => $contasPagar,
        ];

        $tipos = [
            'Vendas' => 'entrada',
            'Frente de caixa' => 'entrada',
            'Contas recebidas' => 'entrada',
            'Créditos' => 'entrada',
            'Débitos' => 'saida',
            'Ordens de serviço' => 'entrada',
            'Contas pagas' => 'saida',
        ];

        $somarColecao = function($colecao) {
            return $colecao->sum(function($registro) {
                return (float) ($registro->valor_total ?? $registro->valor_recebido ?? $registro->valor_pago ?? $registro->valor ?? 0);
            });
        };

        $totais = [];
        $totalEntradas = 0.0;
        $totalSaidas = 0.0;

        foreach ($blocos as $titulo => $colecao) {
            $totais[$titulo] = $somarColecao($colecao);
            if (($tipos[$titulo] ?? 'entrada') === 'saida') {
                $totalSaidas += $totais[$titulo];
            } else {
                $totalEntradas += $totais[$titulo];
            }
        }

        $saldoDia = $totalEntradas - $totalSaidas;
    @endphp

    <div class="fcx-detalhe">
        <div class="fcx-topo mb-2">
            <h6 class="mb-0 text-uppercase">Detalhamento da movimentação do dia {{ $dataView }}</h6>
            <small class="text-muted">Confira as entradas e saídas consideradas no resultado diário.</small>
        </div>

        <div class="row">
            @foreach($blocos as $titulo => $colecao)
                @php
                    $tipo = $tipos[$titulo] ?? 'entrada';
                    $totalBloco = $totais[$titulo] ?? 0;
                @endphp
                <div class="col-md-6">
                    <div class="fcx-bloco">
                        <div class="fcx-bloco-titulo">
                            <span>{{ $titulo }}</span>
                            <span class="fcx-total-badge {{ $tipo === 'saida' ? 'fcx-total-saida' : 'fcx-total-entrada' }}">
                                {{ $tipo === 'saida' ? '-' : '+' }} {{ __moeda($totalBloco) }}
                            </span>
                        </div>
                        <div class="fcx-bloco-corpo">
                            @if($colecao->count())
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover align-middle">
                                        <thead>
                                            <tr>
                                                <th class="fcx-id">ID</th>
                                                <th>Descrição</th>
                                                <th class="fcx-valor">Valor</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($colecao as $registro)
                                                @php
                                                    $valor = (float) ($registro->valor_total ?? $registro->valor_recebido ?? $registro->valor_pago ?? $registro->valor ?? 0);
                                                @endphp
                                                <tr>
                                                    <td class="fcx-id">{{ $registro->id }}</td>
                                                    <td>
                                                        @if($titulo === 'Frente de caixa')
                                                            @php
                                                                $clienteNome = trim((string) (optional($registro->cliente)->razao_social ?? ''));
                                                                $observacao = trim((string) ($registro->observacao ?? ''));
                                                                $descricaoOutros = trim((string) ($registro->descricao_pag_outros ?? ''));
                                                                $nomeRegistro = trim((string) ($registro->nome ?? ''));
                                                                $produtos = collect($registro->itens ?? [])->map(function($item) {
                                                                    $nomeProduto = trim((string) ($item->produto->nome ?? ''));
                                                                    $obsItem = trim((string) ($item->observacao ?? ''));
                                                                    if ($nomeProduto === '') {
                                                                        return null;
                                                                    }
                                                                    return [
                                                                        'texto' => $nomeProduto,
                                                                        'obs' => $obsItem,
                                                                        'quantidade' => $item->quantidade ?? null,
                                                                    ];
                                                                })->filter()->values();

                                                                $descricaoLivre = $observacao !== ''
                                                                    ? $observacao
                                                                    : ($descricaoOutros !== ''
                                                                        ? $descricaoOutros
                                                                        : ($nomeRegistro !== '' ? $nomeRegistro : ''));
                                                            @endphp

                                                            @if($clienteNome !== '')
                                                                <div class="fcx-cliente">{{ $clienteNome }}</div>
                                                            @endif

                                                            @if($descricaoLivre !== '')
                                                                <div class="fcx-desc">{{ $descricaoLivre }}</div>
                                                            @endif

                                                            @if($produtos->count())
                                                                <ul class="fcx-produtos">
                                                                    @foreach($produtos as $produto)
                                                                        <li>
                                                                            {{ $produto['texto'] }}
                                                                            @if(!empty($produto['quantidade']))
                                                                                <span class="qtd">x{{ rtrim(rtrim(number_format((float)$produto['quantidade'], 3, ',', '.'), '0'), ',') }}</span>
                                                                            @endif
                                                                            @if($produto['obs'] !== '')
                                                                                <span class="obs">— {{ $produto['obs'] }}</span>
                                                                            @endif
                                                                        </li>
                                                                    @endforeach
                                                                </ul>
                                                            @elseif($clienteNome === '' && $descricaoLivre === '')
                                                                <span class="text-muted">Sem descrição</span>
                                                            @endif
                                                        @else
                                                            @php
                                                                $descricao = trim((string) (
                                                                    $registro->observacao
                                                                    ?? $registro->observacoes
                                                                    ?? $registro->descricao
                                                                    ?? $registro->referencia
                                                                    ?? $registro->razao_social
                                                                    ?? $registro->nome
                                                                    ?? optional($registro->cliente)->razao_social
                                                                ));
                                                            @endphp
                                                            <span class="small">{{ $descricao !== '' ? $descricao : 'Sem descrição' }}</span>
                                                        @endif
                                                    </td>
                                                    <td class="fcx-valor {{ $tipo === 'saida' ? 'text-saida' : 'text-entrada' }}">{{ __moeda($valor) }}</td>
                                                </tr>
                                            @endforeach
                                            <tr class="fcx-total-row">
                                                <td colspan="2">Total {{ $titulo }}</td>
                                                <td class="fcx-valor {{ $tipo === 'saida' ? 'text-saida' : 'text-entrada' }}">{{ __moeda($totalBloco) }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="fcx-vazio">Nenhum registro encontrado.</p>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="fcx-resumo">
            <div class="fcx-resumo-card fcx-resumo-entrada">
                <small>Total de entradas</small>
                <strong>{{ __moeda($totalEntradas) }}</strong>
            </div>
            <div class="fcx-resumo-card fcx-resumo-saida">
                <small>Total de saídas</small>
                <strong>{{ __moeda($totalSaidas) }}</strong>
            </div>
            <div class="fcx-resumo-card fcx-resumo-saldo {{ $saldoDia > 0 ? 'pos' : ($saldoDia < 0 ? 'neg' : 'zero') }}">
                <small>Saldo do dia</small>
                <strong>{{ __moeda($saldoDia) }}</strong>
            </div>
        </div>
    </div>
</div>
