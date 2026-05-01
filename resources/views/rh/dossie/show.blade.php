@extends('default.layout',['title' => 'RH - Dossiê do Funcionário'])
@section('content')
@php
    $fmtDate = function ($value) {
        if (empty($value)) return '-';
        try { return \Carbon\Carbon::parse($value)->format('d/m/Y'); } catch (\Throwable $e) { return (string) $value; }
    };
    $fmtMoney = fn ($value) => 'R$ ' . number_format((float) $value, 2, ',', '.');
@endphp
<div class="page-content">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h4 class="mb-0">Dossiê do Funcionário</h4>
            <small class="text-muted">Visão consolidada de ficha, dependentes, documentos, ocorrências, férias, folha e timeline do colaborador.</small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('funcionarios.index') }}" class="btn btn-light border">Funcionários</a>
            <a href="{{ route('rh.dashboard') }}" class="btn btn-secondary">Voltar RH</a>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <div class="row g-3 align-items-center">
                <div class="col-lg-8">
                    <div class="d-flex flex-column gap-2">
                        <div>
                            <span class="badge bg-primary-subtle text-primary border">Status do dossiê: {{ strtoupper($dossie->status ?? 'ATIVO') }}</span>
                            <span class="badge bg-light text-dark border">Atualizado em {{ !empty($dossie->ultima_atualizacao_em) ? \Carbon\Carbon::parse($dossie->ultima_atualizacao_em)->format('d/m/Y H:i') : now()->format('d/m/Y H:i') }}</span>
                        </div>
                        <h5 class="mb-0">{{ $funcionario->nome }}</h5>
                        <div class="row g-2 text-muted small">
                            <div class="col-md-3"><strong>CPF:</strong> {{ $funcionario->cpf ?? '-' }}</div>
                            <div class="col-md-3"><strong>Função:</strong> {{ $funcionario->funcao ?? '-' }}</div>
                            <div class="col-md-3"><strong>Salário:</strong> {{ $fmtMoney($funcionario->salario ?? 0) }}</div>
                            <div class="col-md-3"><strong>E-mail:</strong> {{ $funcionario->email ?? '-' }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="border rounded p-2 h-100"><small class="text-muted d-block">Documentos</small><strong>{{ $stats['documentos'] }}</strong></div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-2 h-100"><small class="text-muted d-block">Vencidos</small><strong>{{ $stats['documentos_vencidos'] }}</strong></div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-2 h-100"><small class="text-muted d-block">Movimentações</small><strong>{{ $stats['movimentacoes'] }}</strong></div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-2 h-100"><small class="text-muted d-block">Holerites</small><strong>{{ $stats['holerites'] }}</strong></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-xl-8">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Timeline do dossiê</h6>
                    <span class="text-muted small">{{ $timeline->count() }} eventos</span>
                </div>
                <div class="card-body">
                    @forelse($timeline as $item)
                        <div class="border-start border-3 ps-3 mb-3">
                            <div class="d-flex justify-content-between flex-wrap gap-2">
                                <div>
                                    <div class="fw-semibold">{{ $item['titulo'] }}</div>
                                    <div class="small text-muted">{{ $fmtDate($item['data']) }} • {{ strtoupper($item['categoria']) }} • origem: {{ $item['origem'] }}</div>
                                </div>
                                @if(!empty($item['can_delete_evento']) || !empty($item['can_delete_documento']))
                                    <div class="d-flex align-items-start gap-2">
                                        @if(!empty($item['can_delete_evento']) && !empty($item['evento_id']))
                                            <form action="{{ route('rh.dossie.eventos.destroy', [$funcionario->id, $item['evento_id']]) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir este evento da timeline do dossiê?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Excluir evento">🗑️</button>
                                            </form>
                                        @endif
                                        @if(!empty($item['can_delete_documento']) && !empty($item['documento_id']))
                                            <form action="{{ route('rh.dossie.documentos.destroy', [$funcionario->id, $item['documento_id']]) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir este documento do dossiê?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Excluir documento">🗑️</button>
                                            </form>
                                        @endif
                                    </div>
                                @endif
                            </div>
                            <div class="mt-1">{{ $item['descricao'] ?: 'Sem descrição complementar.' }}</div>
                        </div>
                    @empty
                        <div class="alert alert-light border mb-0">Ainda não há eventos consolidados no dossiê.</div>
                    @endforelse
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent"><h6 class="mb-0">Ficha admissional</h6></div>
                <div class="card-body">
                    @if($ficha)
                        <div class="row g-3 small">
                            <div class="col-md-3"><strong>Admissão:</strong><br>{{ $fmtDate($ficha->data_admissao) }}</div>
                            <div class="col-md-3"><strong>Data nascimento:</strong><br>{{ $fmtDate($ficha->data_nascimento) }}</div>
                            <div class="col-md-3"><strong>PIS:</strong><br>{{ $ficha->pis_numero ?? '-' }}</div>
                            <div class="col-md-3"><strong>CTPS:</strong><br>{{ ($ficha->ctps_numero ?? '-') . ' / ' . ($ficha->ctps_serie ?? '-') }}</div>
                            <div class="col-md-3"><strong>CNH:</strong><br>{{ $ficha->cnh_numero ?? '-' }}</div>
                            <div class="col-md-3"><strong>Validade CNH:</strong><br>{{ $fmtDate($ficha->cnh_validade) }}</div>
                            <div class="col-md-3"><strong>Banco:</strong><br>{{ $ficha->banco ?? '-' }}</div>
                            <div class="col-md-3"><strong>Agência:</strong><br>{{ $ficha->agencia ?? '-' }}</div>
                            <div class="col-md-6"><strong>Naturalidade:</strong><br>{{ trim(($ficha->naturalidade ?? '-') . ' / ' . ($ficha->uf_naturalidade ?? '-')) }}</div>
                            <div class="col-md-6"><strong>Observações:</strong><br>{{ $ficha->observacoes ?? '-' }}</div>
                        </div>
                    @else
                        <div class="alert alert-light border mb-0">Sem ficha admissional vinculada.</div>
                    @endif
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent"><h6 class="mb-0">Documentos do colaborador</h6></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead><tr><th>Nome</th><th>Tipo</th><th>Categoria</th><th>Validade</th><th>Arquivo</th><th>Ações</th></tr></thead>
                            <tbody>
                            @forelse($documentos as $doc)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $doc->nome }}</div>
                                        <div class="small text-muted">{{ $doc->observacao ?? '-' }}</div>
                                    </td>
                                    <td>{{ $doc->tipo }}</td>
                                    <td>{{ $doc->categoria ?? '-' }}</td>
                                    <td>
                                        {{ $fmtDate($doc->validade) }}
                                        @if(!empty($doc->validade) && \Carbon\Carbon::parse($doc->validade)->startOfDay()->lt(now()->startOfDay()))
                                            <span class="badge bg-danger ms-1">Vencido</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if(!empty($doc->arquivo))
                                            <a href="{{ route('rh.dossie.documentos.download', [$funcionario->id, $doc->id]) }}" class="btn btn-sm btn-outline-primary">Baixar</a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        <form action="{{ route('rh.dossie.documentos.destroy', [$funcionario->id, $doc->id]) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir este documento do dossiê?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Excluir documento">🗑️</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted">Nenhum documento cadastrado.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-transparent"><h6 class="mb-0">Movimentações</h6></div>
                        <div class="card-body table-responsive">
                            <table class="table table-sm table-striped">
                                <thead><tr><th>Data</th><th>Tipo</th><th>Resumo</th></tr></thead>
                                <tbody>
                                @forelse($movimentacoes as $item)
                                    <tr>
                                        <td>{{ $fmtDate($item->data_movimentacao) }}</td>
                                        <td>{{ \App\Models\RHMovimentacao::tipos()[$item->tipo] ?? ucfirst($item->tipo) }}</td>
                                        <td>{{ $item->descricao }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center text-muted">Sem movimentações.</td></tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-transparent"><h6 class="mb-0">Dependentes</h6></div>
                        <div class="card-body table-responsive">
                            <table class="table table-sm table-striped">
                                <thead><tr><th>Nome</th><th>Parentesco</th><th>Nascimento</th></tr></thead>
                                <tbody>
                                @forelse($dependentes as $dep)
                                    <tr>
                                        <td>{{ $dep->nome }}</td>
                                        <td>{{ $dep->parentesco ?? '-' }}</td>
                                        <td>{{ $fmtDate($dep->data_nascimento) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center text-muted">Sem dependentes cadastrados.</td></tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-transparent"><h6 class="mb-0">Férias</h6></div>
                        <div class="card-body table-responsive">
                            <table class="table table-sm table-striped">
                                <thead><tr><th>Período</th><th>Gozo</th><th>Status</th></tr></thead>
                                <tbody>
                                @forelse($ferias as $item)
                                    <tr>
                                        <td>{{ $fmtDate($item->periodo_aquisitivo_inicio) }} até {{ $fmtDate($item->periodo_aquisitivo_fim) }}</td>
                                        <td>{{ $fmtDate($item->data_inicio) }} até {{ $fmtDate($item->data_fim) }}</td>
                                        <td>{{ $item->status }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center text-muted">Sem férias registradas.</td></tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-transparent"><h6 class="mb-0">Folha / holerites</h6></div>
                        <div class="card-body table-responsive">
                            <table class="table table-sm table-striped">
                                <thead><tr><th>Competência</th><th>Valor</th><th>Pagamento</th></tr></thead>
                                <tbody>
                                @forelse($holerites as $item)
                                    <tr>
                                        <td>{{ str_pad((string) $item->mes, 2, '0', STR_PAD_LEFT) }}/{{ $item->ano }}</td>
                                        <td>{{ $fmtMoney($item->valor_final) }}</td>
                                        <td>{{ $item->forma_pagamento }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center text-muted">Sem apurações de folha.</td></tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent"><h6 class="mb-0">Anexar documento ao dossiê</h6></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('rh.dossie.documentos.store', $funcionario->id) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label">Nome do documento</label>
                            <input type="text" name="nome" class="form-control" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Tipo</label>
                            <input type="text" name="tipo" class="form-control" placeholder="Ex.: ASO, RG, Contrato" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Categoria</label>
                            <select name="categoria" class="form-control">
                                @foreach($categoriasDocumento as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Validade</label>
                            <input type="date" name="validade" class="form-control">
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Arquivo</label>
                            <input type="file" name="arquivo" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Observação</label>
                            <textarea name="observacao" class="form-control" rows="3"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Anexar documento</button>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent"><h6 class="mb-0">Registrar evento manual</h6></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('rh.dossie.eventos.store', $funcionario->id) }}">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label">Categoria</label>
                            <select name="categoria" class="form-control" required>
                                @foreach($categoriasEvento as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Título</label>
                            <input type="text" name="titulo" class="form-control" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Data do evento</label>
                            <input type="date" name="data_evento" class="form-control" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Descrição</label>
                            <textarea name="descricao" class="form-control" rows="4"></textarea>
                        </div>
                        <div class="form-check mb-3">
                            <input type="hidden" name="visibilidade_portal" value="0">
                            <input class="form-check-input" type="checkbox" value="1" name="visibilidade_portal" id="visibilidade_portal">
                            <label class="form-check-label" for="visibilidade_portal">Disponibilizar no portal do funcionário</label>
                        </div>
                        <button type="submit" class="btn btn-dark w-100">Registrar evento</button>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent"><h6 class="mb-0">Ocorrências e desligamentos</h6></div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="small text-muted mb-2">Faltas / ocorrências</div>
                        @forelse($faltas as $item)
                            <div class="border rounded p-2 mb-2">
                                <div class="fw-semibold">{{ $fmtDate($item->data_referencia) }} • {{ $item->tipo }}</div>
                                <div class="small">{{ $item->descricao ?? 'Sem descrição.' }}</div>
                            </div>
                        @empty
                            <div class="text-muted small">Sem faltas registradas.</div>
                        @endforelse
                    </div>
                    <div>
                        <div class="small text-muted mb-2">Desligamentos</div>
                        @forelse($desligamentos as $item)
                            <div class="border rounded p-2 mb-2">
                                <div class="fw-semibold">{{ $fmtDate($item->data_desligamento) }} • {{ $item->tipo }}</div>
                                <div class="small">{{ $item->motivo }}</div>
                            </div>
                        @empty
                            <div class="text-muted small">Sem desligamentos registrados.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
