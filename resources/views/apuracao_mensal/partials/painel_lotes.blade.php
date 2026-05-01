@if($lotes->isNotEmpty())
    <div class="card border mb-4">
        <div class="card-header bg-light d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <strong>Painel profissional de envio</strong>
                <div class="small text-muted">Acompanhe fila, progresso, falhas, cancelamento e exportação.</div>
            </div>
            @if($lotes->contains(fn ($lote) => in_array($lote->status, ['na_fila', 'processando'])))
                <span class="badge bg-success">Atualização automática ativa</span>
            @endif
        </div>
        <div class="card-body">
            @foreach($lotes as $lote)
                @php
                    $cancelados = (int) $lote->envios->where('status', 'cancelado')->count();
                    $totalProcessado = (int) $lote->enviados + (int) $lote->falhas + (int) $lote->sem_email + $cancelados;
                    $percentual = $lote->total > 0 ? min(100, (int) round(($totalProcessado / $lote->total) * 100)) : 0;
                @endphp
                <div class="border rounded p-3 mb-3" data-lote-id="{{ $lote->id }}">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                        <div>
                            <strong>Lote #{{ $lote->id }}</strong>
                            <span class="text-muted">· {{ str_pad($lote->mes, 2, '0', STR_PAD_LEFT) }}/{{ $lote->ano }}</span>
                            <div class="small text-muted">{{ $lote->observacao }} @if($lote->solicitado_por) · solicitado por {{ $lote->solicitado_por }} @endif</div>
                        </div>
                        <div class="d-flex gap-2 align-items-center flex-wrap">
                            @php
                                $badge = [
                                    'na_fila' => 'secondary',
                                    'processando' => 'warning text-dark',
                                    'concluido' => 'success',
                                    'concluido_com_falhas' => 'danger',
                                    'cancelado' => 'dark',
                                ][$lote->status] ?? 'dark';
                            @endphp
                            <span class="badge bg-{{ $badge }}">{{ ucfirst(str_replace('_', ' ', $lote->status)) }}</span>
                            <a href="{{ route('apuracaoMensal.holerites_competencia.email.exportar', $lote->id) }}" class="btn btn-sm btn-outline-success">
                                <i class="bx bx-spreadsheet"></i> Exportar Excel
                            </a>
                            @if(in_array($lote->status, ['na_fila', 'processando']))
                                <form method="POST" action="{{ route('apuracaoMensal.holerites_competencia.email.cancelar', $lote->id) }}" onsubmit="return confirm('Cancelar este lote? Os envios pendentes serão interrompidos.');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bx bx-x-circle"></i> Cancelar lote
                                    </button>
                                </form>
                            @endif
                            @if(((int) $lote->falhas + (int) $lote->sem_email) > 0)
                                <form method="POST" action="{{ route('apuracaoMensal.holerites_competencia.email.reenfileirar', $lote->id) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-primary">
                                        <i class="bx bx-refresh"></i> Reenfileirar falhas
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>

                    <div class="progress mb-3" style="height: 10px;">
                        <div class="progress-bar" role="progressbar" style="width: {{ $percentual }}%;" aria-valuenow="{{ $percentual }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>

                    <div class="row g-2 small mb-3">
                        <div class="col-md-2"><span class="badge bg-light text-dark w-100 p-2">Total: {{ $lote->total }}</span></div>
                        <div class="col-md-2"><span class="badge bg-secondary w-100 p-2">Fila: {{ $lote->pendentes }}</span></div>
                        <div class="col-md-2"><span class="badge bg-warning text-dark w-100 p-2">Processando: {{ $lote->processando }}</span></div>
                        <div class="col-md-2"><span class="badge bg-success w-100 p-2">Enviados: {{ $lote->enviados }}</span></div>
                        <div class="col-md-2"><span class="badge bg-danger w-100 p-2">Falhas: {{ $lote->falhas }}</span></div>
                        <div class="col-md-1"><span class="badge bg-dark w-100 p-2">Sem e-mail: {{ $lote->sem_email }}</span></div>
                        <div class="col-md-1"><span class="badge bg-dark w-100 p-2">Cancelados: {{ $cancelados }}</span></div>
                    </div>

                    @if($lote->envios->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table table-sm table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th>Funcionário</th>
                                        <th>E-mail</th>
                                        <th>Status</th>
                                        <th>Tentativas</th>
                                        <th>Última ocorrência</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($lote->envios as $envio)
                                        <tr>
                                            <td>{{ $envio->funcionario->nome ?? 'Funcionário' }}</td>
                                            <td>{{ $envio->email ?: 'Não informado' }}</td>
                                            <td>
                                                @php
                                                    $envioBadge = [
                                                        'enviado' => 'success',
                                                        'falha' => 'danger',
                                                        'sem_email' => 'dark',
                                                        'processando' => 'warning text-dark',
                                                        'cancelado' => 'dark',
                                                        'fila' => 'secondary',
                                                    ][$envio->status] ?? 'secondary';
                                                @endphp
                                                <span class="badge bg-{{ $envioBadge }}">{{ ucfirst(str_replace('_', ' ', $envio->status)) }}</span>
                                            </td>
                                            <td>{{ $envio->tentativas }}</td>
                                            <td>
                                                @if($envio->ultima_falha)
                                                    <span class="text-danger">{{ $envio->ultima_falha }}</span>
                                                @elseif($envio->enviado_em)
                                                    Enviado em {{ \Carbon\Carbon::parse($envio->enviado_em)->format('d/m/Y H:i') }}
                                                @elseif($envio->ultima_tentativa_em)
                                                    Tentativa em {{ \Carbon\Carbon::parse($envio->ultima_tentativa_em)->format('d/m/Y H:i') }}
                                                @else
                                                    --
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
@endif
