<?php $__env->startSection('content'); ?>
<div class="page-content">
    <div class="card">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                <div>
                    <h4 class="mb-1">Processamento da folha</h4>
                    <div class="text-muted">Central administrativa para processar, acompanhar e fechar a competência mensal.</div>
                </div>
                <div class="d-flex gap-2">
                    <a href="<?php echo e(route('apuracaoMensal.index')); ?>" class="btn btn-light"><i class="bx bx-arrow-back"></i> Voltar para apuração</a>
                    <a href="<?php echo e(route('apuracaoMensal.holerites_competencia', ['mes_competencia' => $mes, 'ano_competencia' => $ano])); ?>" class="btn btn-danger">
                        <i class="bx bxs-file-pdf"></i> Holerites da competência
                    </a>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card border shadow-none h-100 mb-0">
                        <div class="card-body">
                            <div class="text-muted">Competência</div>
                            <h3 class="mb-0"><?php echo e(str_pad($mes, 2, '0', STR_PAD_LEFT)); ?>/<?php echo e($ano); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border shadow-none h-100 mb-0">
                        <div class="card-body">
                            <div class="text-muted">Status</div>
                            <h3 class="mb-0 text-capitalize"><?php echo e($status); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border shadow-none h-100 mb-0">
                        <div class="card-body">
                            <div class="text-muted">Funcionários processados</div>
                            <h3 class="mb-0"><?php echo e($resumo['registros']); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border shadow-none h-100 mb-0">
                        <div class="card-body">
                            <div class="text-muted">Líquido total</div>
                            <h3 class="mb-0 text-success"><?php echo e(__moeda($resumo['total_liquido'])); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border shadow-none h-100 mb-0">
                        <div class="card-body">
                            <div class="text-muted">Integrações financeiras</div>
                            <h4 class="mb-1"><?php echo e($financeiroResumo['sincronizados'] ?? 0); ?></h4>
                            <div class="small text-muted">Apurações com conta a pagar vinculada</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border shadow-none h-100 mb-0">
                        <div class="card-body">
                            <div class="text-muted">Pendentes de integração</div>
                            <h4 class="mb-1 text-warning"><?php echo e($financeiroResumo['pendentes'] ?? 0); ?></h4>
                            <div class="small text-muted">Itens sem conta a pagar gerada</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border shadow-none h-100 mb-0">
                        <div class="card-body">
                            <div class="text-muted">Valor integrado</div>
                            <h4 class="mb-1 text-primary"><?php echo e(__moeda($financeiroResumo['valor_integrado'] ?? 0)); ?></h4>
                            <div class="small text-muted">Líquido já refletido no financeiro</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border mb-4">
                <div class="card-header bg-light"><strong>Ações da competência</strong></div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-lg-7">
                            <form method="POST" action="<?php echo e(route('rh.folha.processamento.processar')); ?>" class="row g-3">
                                <?php echo csrf_field(); ?>
                                <div class="col-md-3">
                                    <label class="form-label">Mês</label>
                                    <input type="number" min="1" max="12" name="mes_competencia" class="form-control" value="<?php echo e($mes); ?>" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Ano</label>
                                    <input type="number" min="2000" max="2100" name="ano_competencia" class="form-control" value="<?php echo e($ano); ?>" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Vencimento</label>
                                    <input type="date" name="vencimento_folha" class="form-control" value="<?php echo e(now()->setMonth($mes)->setYear($ano)->endOfMonth()->format('Y-m-d')); ?>">
                                </div>
                                <div class="col-md-3 d-flex align-items-end">
                                    <button type="submit" class="btn btn-dark w-100">
                                        <i class="bx bx-play-circle"></i> Processar folha
                                    </button>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="1" name="sobrescrever" id="sobrescrever_competencia">
                                        <label class="form-check-label" for="sobrescrever_competencia">Sobrescrever apurações existentes</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="1" name="integrar_financeiro" id="integrar_financeiro_competencia" checked>
                                        <label class="form-check-label" for="integrar_financeiro_competencia">Integração financeira automática</label>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="col-lg-5">
                            <div class="p-3 border rounded bg-light h-100">
                                <div class="small text-muted mb-2">Fechamento da competência</div>
                                <?php if(($fechamento->status ?? null) === 'fechado'): ?>
                                    <div class="alert alert-success py-2">Folha fechada. Reabra apenas se precisar recalcular.</div>
                                    <form method="POST" action="<?php echo e(url('/rh/folha/reabrir')); ?>" class="d-flex gap-2">
                                        <?php echo csrf_field(); ?>
                                        <input type="hidden" name="mes" value="<?php echo e($mes); ?>">
                                        <input type="hidden" name="ano" value="<?php echo e($ano); ?>">
                                        <button type="submit" class="btn btn-outline-warning">
                                            <i class="bx bx-reset"></i> Reabrir competência
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <div class="alert alert-warning py-2">Folha aberta ou processada. Após fechar, alterações ficam bloqueadas.</div>
                                    <form method="POST" action="<?php echo e(url('/rh/folha/fechar')); ?>" class="row g-2">
                                        <?php echo csrf_field(); ?>
                                        <input type="hidden" name="mes" value="<?php echo e($mes); ?>">
                                        <input type="hidden" name="ano" value="<?php echo e($ano); ?>">
                                        <div class="col-12">
                                            <textarea name="observacao" class="form-control" rows="3" placeholder="Observação do fechamento (opcional)"></textarea>
                                        </div>
                                        <div class="col-12">
                                            <button type="submit" class="btn btn-success">
                                                <i class="bx bx-lock-alt"></i> Fechar folha
                                            </button>
                                        </div>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card border shadow-none h-100 mb-0">
                        <div class="card-body">
                            <div class="text-muted">Proventos</div>
                            <h4 class="mb-0"><?php echo e(__moeda($resumo['total_proventos'])); ?></h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border shadow-none h-100 mb-0">
                        <div class="card-body">
                            <div class="text-muted">Descontos</div>
                            <h4 class="mb-0"><?php echo e(__moeda($resumo['total_descontos'])); ?></h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border shadow-none h-100 mb-0">
                        <div class="card-body">
                            <div class="text-muted">Conta a pagar do fechamento</div>
                            <h4 class="mb-0"><?php echo e($fechamento?->conta_pagar_id ? '#' . $fechamento->conta_pagar_id : '--'); ?></h4>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <strong>Apurações da competência</strong>
                    <form method="GET" class="d-flex gap-2">
                        <input type="number" min="1" max="12" name="mes_competencia" class="form-control form-control-sm" value="<?php echo e($mes); ?>">
                        <input type="number" min="2000" max="2100" name="ano_competencia" class="form-control form-control-sm" value="<?php echo e($ano); ?>">
                        <button class="btn btn-sm btn-outline-secondary" type="submit">Atualizar</button>
                    </form>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Funcionário</th>
                                    <th>Proventos</th>
                                    <th>Descontos</th>
                                    <th>Líquido</th>
                                    <th>Conta a pagar</th>
                                    <th class="text-end">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $apuracoes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><?php echo e($item->funcionario->nome ?? ('#' . $item->funcionario_id)); ?></td>
                                        <td><?php echo e(__moeda($item->total_proventos ?? $item->valor_final)); ?></td>
                                        <td><?php echo e(__moeda($item->total_descontos ?? 0)); ?></td>
                                        <td><strong><?php echo e(__moeda($item->liquido ?? $item->valor_final)); ?></strong></td>
                                        <td><?php echo e($item->conta_pagar_id ? '#' . $item->conta_pagar_id : '--'); ?></td>
                                        <td class="text-end">
                                            <a target="_blank" href="<?php echo e(route('rh.holerite.show', ['id' => $item->funcionario_id, 'mes' => $item->mes, 'ano' => $item->ano])); ?>" class="btn btn-sm btn-danger">
                                                <i class="bx bxs-file-pdf"></i> Holerite
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-4">Nenhuma apuração encontrada para a competência selecionada.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer"><?php echo e($apuracoes->links()); ?></div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('default.layout',['title' => 'Processamento da folha'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home2/wesl4494/public_html/padariacasadopao.com/resources/views/rh/folha_processamento/index.blade.php ENDPATH**/ ?>