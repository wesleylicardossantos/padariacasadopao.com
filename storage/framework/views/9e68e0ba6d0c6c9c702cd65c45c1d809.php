<?php if($lotes->isNotEmpty()): ?>
    <div class="card border mb-4">
        <div class="card-header bg-light d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <strong>Painel profissional de envio</strong>
                <div class="small text-muted">Acompanhe fila, progresso, falhas, cancelamento e exportação.</div>
            </div>
            <?php if($lotes->contains(fn ($lote) => in_array($lote->status, ['na_fila', 'processando']))): ?>
                <span class="badge bg-success">Atualização automática ativa</span>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <?php $__currentLoopData = $lotes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lote): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $cancelados = (int) $lote->envios->where('status', 'cancelado')->count();
                    $totalProcessado = (int) $lote->enviados + (int) $lote->falhas + (int) $lote->sem_email + $cancelados;
                    $percentual = $lote->total > 0 ? min(100, (int) round(($totalProcessado / $lote->total) * 100)) : 0;
                ?>
                <div class="border rounded p-3 mb-3" data-lote-id="<?php echo e($lote->id); ?>">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                        <div>
                            <strong>Lote #<?php echo e($lote->id); ?></strong>
                            <span class="text-muted">· <?php echo e(str_pad($lote->mes, 2, '0', STR_PAD_LEFT)); ?>/<?php echo e($lote->ano); ?></span>
                            <div class="small text-muted"><?php echo e($lote->observacao); ?> <?php if($lote->solicitado_por): ?> · solicitado por <?php echo e($lote->solicitado_por); ?> <?php endif; ?></div>
                        </div>
                        <div class="d-flex gap-2 align-items-center flex-wrap">
                            <?php
                                $badge = [
                                    'na_fila' => 'secondary',
                                    'processando' => 'warning text-dark',
                                    'concluido' => 'success',
                                    'concluido_com_falhas' => 'danger',
                                    'cancelado' => 'dark',
                                ][$lote->status] ?? 'dark';
                            ?>
                            <span class="badge bg-<?php echo e($badge); ?>"><?php echo e(ucfirst(str_replace('_', ' ', $lote->status))); ?></span>
                            <a href="<?php echo e(route('apuracaoMensal.holerites_competencia.email.exportar', $lote->id)); ?>" class="btn btn-sm btn-outline-success">
                                <i class="bx bx-spreadsheet"></i> Exportar Excel
                            </a>
                            <?php if(in_array($lote->status, ['na_fila', 'processando'])): ?>
                                <form method="POST" action="<?php echo e(route('apuracaoMensal.holerites_competencia.email.cancelar', $lote->id)); ?>" onsubmit="return confirm('Cancelar este lote? Os envios pendentes serão interrompidos.');">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bx bx-x-circle"></i> Cancelar lote
                                    </button>
                                </form>
                            <?php endif; ?>
                            <?php if(((int) $lote->falhas + (int) $lote->sem_email) > 0): ?>
                                <form method="POST" action="<?php echo e(route('apuracaoMensal.holerites_competencia.email.reenfileirar', $lote->id)); ?>">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="btn btn-sm btn-outline-primary">
                                        <i class="bx bx-refresh"></i> Reenfileirar falhas
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="progress mb-3" style="height: 10px;">
                        <div class="progress-bar" role="progressbar" style="width: <?php echo e($percentual); ?>%;" aria-valuenow="<?php echo e($percentual); ?>" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>

                    <div class="row g-2 small mb-3">
                        <div class="col-md-2"><span class="badge bg-light text-dark w-100 p-2">Total: <?php echo e($lote->total); ?></span></div>
                        <div class="col-md-2"><span class="badge bg-secondary w-100 p-2">Fila: <?php echo e($lote->pendentes); ?></span></div>
                        <div class="col-md-2"><span class="badge bg-warning text-dark w-100 p-2">Processando: <?php echo e($lote->processando); ?></span></div>
                        <div class="col-md-2"><span class="badge bg-success w-100 p-2">Enviados: <?php echo e($lote->enviados); ?></span></div>
                        <div class="col-md-2"><span class="badge bg-danger w-100 p-2">Falhas: <?php echo e($lote->falhas); ?></span></div>
                        <div class="col-md-1"><span class="badge bg-dark w-100 p-2">Sem e-mail: <?php echo e($lote->sem_email); ?></span></div>
                        <div class="col-md-1"><span class="badge bg-dark w-100 p-2">Cancelados: <?php echo e($cancelados); ?></span></div>
                    </div>

                    <?php if($lote->envios->isNotEmpty()): ?>
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
                                    <?php $__currentLoopData = $lote->envios; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $envio): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td><?php echo e($envio->funcionario->nome ?? 'Funcionário'); ?></td>
                                            <td><?php echo e($envio->email ?: 'Não informado'); ?></td>
                                            <td>
                                                <?php
                                                    $envioBadge = [
                                                        'enviado' => 'success',
                                                        'falha' => 'danger',
                                                        'sem_email' => 'dark',
                                                        'processando' => 'warning text-dark',
                                                        'cancelado' => 'dark',
                                                        'fila' => 'secondary',
                                                    ][$envio->status] ?? 'secondary';
                                                ?>
                                                <span class="badge bg-<?php echo e($envioBadge); ?>"><?php echo e(ucfirst(str_replace('_', ' ', $envio->status))); ?></span>
                                            </td>
                                            <td><?php echo e($envio->tentativas); ?></td>
                                            <td>
                                                <?php if($envio->ultima_falha): ?>
                                                    <span class="text-danger"><?php echo e($envio->ultima_falha); ?></span>
                                                <?php elseif($envio->enviado_em): ?>
                                                    Enviado em <?php echo e(\Carbon\Carbon::parse($envio->enviado_em)->format('d/m/Y H:i')); ?>

                                                <?php elseif($envio->ultima_tentativa_em): ?>
                                                    Tentativa em <?php echo e(\Carbon\Carbon::parse($envio->ultima_tentativa_em)->format('d/m/Y H:i')); ?>

                                                <?php else: ?>
                                                    --
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
<?php endif; ?>
<?php /**PATH /home2/wesl4494/public_html/padariacasadopao.com/resources/views/apuracao_mensal/partials/painel_lotes.blade.php ENDPATH**/ ?>