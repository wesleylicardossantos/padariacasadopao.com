@extends('default.layout',['title' => 'Holerites da competência'])

@section('content')
<div class="page-content">
    <div class="card">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <div>
                    <h6 class="mb-0 text-uppercase">Holerites da competência</h6>
                    <small class="text-muted">Competência {{ str_pad($mes, 2, '0', STR_PAD_LEFT) }}/{{ $ano }}</small>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('apuracaoMensal.index', ['mes_competencia' => $mes, 'ano_competencia' => $ano]) }}" class="btn btn-light">
                        <i class="bx bx-arrow-back"></i> Voltar
                    </a>
                    <a href="{{ route('apuracaoMensal.holerites_competencia.zip', ['mes_competencia' => $mes, 'ano_competencia' => $ano]) }}" class="btn btn-danger">
                        <i class="bx bx-download"></i> Baixar ZIP
                    </a>
                    <form method="POST" action="{{ route('apuracaoMensal.holerites_competencia.email') }}">
                        @csrf
                        <input type="hidden" name="mes_competencia" value="{{ $mes }}">
                        <input type="hidden" name="ano_competencia" value="{{ $ano }}">
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-envelope"></i> Enviar em fila
                        </button>
                    </form>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card border shadow-none mb-0">
                        <div class="card-body">
                            <div class="text-muted">Total de holerites</div>
                            <h4 class="mb-0">{{ $data->count() }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border shadow-none mb-0">
                        <div class="card-body">
                            <div class="text-muted">Último lote</div>
                            <h4 class="mb-0">{{ optional($lotes->first())->id ? '#' . $lotes->first()->id : '--' }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border shadow-none mb-0">
                        <div class="card-body">
                            <div class="text-muted">Enviados no último lote</div>
                            <h4 class="mb-0 text-success">{{ (int) optional($lotes->first())->enviados }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border shadow-none mb-0">
                        <div class="card-body">
                            <div class="text-muted">Falhas / sem e-mail</div>
                            <h4 class="mb-0 text-danger">{{ (int) optional($lotes->first())->falhas + (int) optional($lotes->first())->sem_email }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            <div id="painel-lotes-wrapper">
                @include('apuracao_mensal.partials.painel_lotes', ['lotes' => $lotes, 'mes' => $mes, 'ano' => $ano])
            </div>

            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Funcionário</th>
                            <th>Mês/Ano</th>
                            <th>Valor final</th>
                            <th>E-mail</th>
                            <th>Portal</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $item)
                            <tr>
                                <td>{{ $item->funcionario->nome ?? 'Funcionário' }}</td>
                                <td>{{ str_pad($mes, 2, '0', STR_PAD_LEFT) }}/{{ $ano }}</td>
                                <td>{{ __moeda($item->valor_final) }}</td>
                                <td>{{ $item->funcionario->email ?: 'Não informado' }}</td>
                                <td>
                                    <div class="d-flex flex-wrap gap-1">
                                        <form method="POST" action="{{ route('rh.portal_externo.enviar_acesso', $item->funcionario_id) }}">
                                            @csrf
                                            <input type="hidden" name="canal" value="whatsapp">
                                            <button type="submit" class="btn btn-sm btn-success">
                                                <i class="bx bxl-whatsapp"></i> WhatsApp
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('rh.portal_externo.enviar_acesso', $item->funcionario_id) }}">
                                            @csrf
                                            <input type="hidden" name="canal" value="email">
                                            <button type="submit" class="btn btn-sm btn-outline-primary">
                                                <i class="bx bx-envelope"></i> E-mail
                                            </button>
                                        </form>
                                    </div>
                                </td>
                                <td>
                                    <a target="_blank" href="{{ route('rh.holerite.show', ['id' => $item->funcionario_id, 'mes' => $mes, 'ano' => $ano]) }}" class="btn btn-sm btn-danger">
                                        <i class="bx bxs-file-pdf"></i> PDF
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">Nenhuma apuração encontrada para essa competência.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
(function () {
    const url = @json(route('apuracaoMensal.holerites_competencia.painel', ['mes_competencia' => $mes, 'ano_competencia' => $ano]));
    const wrapper = document.getElementById('painel-lotes-wrapper');
    if (!wrapper) return;

    let timer = null;
    let running = false;

    async function refreshPanel() {
        if (running) return;
        running = true;
        try {
            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            });
            if (!response.ok) return;
            const data = await response.json();
            if (typeof data.html === 'string') {
                wrapper.innerHTML = data.html;
            }
            if (!data.active && timer) {
                clearInterval(timer);
                timer = null;
            }
        } catch (e) {
            console.error('Falha ao atualizar painel de lotes.', e);
        } finally {
            running = false;
        }
    }

    if (wrapper.textContent.includes('Atualização automática ativa')) {
        timer = setInterval(refreshPanel, 10000);
    }
})();
</script>
@endsection
