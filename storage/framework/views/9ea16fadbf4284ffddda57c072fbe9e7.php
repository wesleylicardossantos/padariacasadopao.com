<?php $__env->startSection('content'); ?>
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto d-flex flex-wrap gap-2 align-items-end">
                    <form method="POST" action="<?php echo e(route('apuracaoMensal.gerar_automatica')); ?>" class="row g-2 align-items-end">
                        <?php echo csrf_field(); ?>
                        <div class="col-auto">
                            <label class="form-label mb-1">Mês</label>
                            <input type="number" min="1" max="12" name="mes_competencia" class="form-control" value="<?php echo e(now()->month); ?>" required>
                        </div>
                        <div class="col-auto">
                            <label class="form-label mb-1">Ano</label>
                            <input type="number" min="2000" max="2100" name="ano_competencia" class="form-control" value="<?php echo e(now()->year); ?>" required>
                        </div>
                        <div class="col-auto">
                            <label class="form-label mb-1">Vencimento folha</label>
                            <input type="date" name="vencimento_folha" class="form-control" value="<?php echo e(now()->endOfMonth()->format('Y-m-d')); ?>">
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
                    <form method="POST" action="<?php echo e(route('apuracaoMensal.integrar_financeiro')); ?>" class="row g-2 align-items-end">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="mes_competencia" value="<?php echo e(request('mes_competencia', now()->month)); ?>">
                        <input type="hidden" name="ano_competencia" value="<?php echo e(request('ano_competencia', now()->year)); ?>">
                        <input type="hidden" name="vencimento_folha" value="<?php echo e(request('vencimento_folha', now()->endOfMonth()->format('Y-m-d'))); ?>">
                        <div class="col-auto">
                            <label class="form-label mb-1 d-block">&nbsp;</label>
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-wallet"></i> Integrar folha com financeiro
                            </button>
                        </div>
                    </form>
                    <form method="GET" action="<?php echo e(route('apuracaoMensal.holerites_competencia')); ?>" class="row g-2 align-items-end">
                        <input type="hidden" name="mes_competencia" value="<?php echo e(request('mes_competencia', now()->month)); ?>">
                        <input type="hidden" name="ano_competencia" value="<?php echo e(request('ano_competencia', now()->year)); ?>">
                        <div class="col-auto">
                            <label class="form-label mb-1 d-block">&nbsp;</label>
                            <button type="submit" class="btn btn-outline-danger">
                                <i class="bx bxs-file-pdf"></i> Holerites da competência
                            </button>
                        </div>
                    </form>
                    <a href="<?php echo e(route('rh.folha.processamento.index')); ?>" type="button" class="btn btn-outline-dark">
                        <i class="bx bx-cog"></i> Processamento da folha
                    </a>
                    <a href="<?php echo e(route('apuracaoMensal.create')); ?>" type="button" class="btn btn-success">
                        <i class="bx bx-plus"></i> Nova apuração
                    </a>
                </div>
            </div>
            <div class="col">
                <h6 class="mb-0 text-uppercase">Apuração mensal</h6>
                <?php echo Form::open()->fill(request()->all())->get(); ?>

                <div class="row mt-2">
                    <div class="col-md-5">
                        <?php echo Form::text('nome', 'Nome'); ?>

                    </div>
                    <div class="col-md-2">
                        <?php echo Form::date('start_date', 'Data inicial'); ?>

                    </div>
                    <div class="col-md-2">
                        <?php echo Form::date('end_date', 'Data final'); ?>

                    </div>
                    <div class="col-md-3 text-left">
                        <br>
                        <button class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
                        <a id="clear-filter" class="btn btn-danger" href="<?php echo e(route('apuracaoMensal.index')); ?>"><i class="bx bx-eraser"></i> Limpar</a>
                    </div>
                </div>
                <?php echo Form::close(); ?>



                <?php if(isset($lotesRecentes) && $lotesRecentes->isNotEmpty()): ?>
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
                                    <?php $__currentLoopData = $lotesRecentes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lote): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td>#<?php echo e($lote->id); ?></td>
                                        <td><?php echo e(str_pad($lote->mes, 2, '0', STR_PAD_LEFT)); ?>/<?php echo e($lote->ano); ?></td>
                                        <td><?php echo e(ucfirst(str_replace('_', ' ', $lote->status))); ?></td>
                                        <td><?php echo e($lote->enviados); ?></td>
                                        <td><?php echo e($lote->falhas); ?></td>
                                        <td><?php echo e($lote->sem_email); ?></td>
                                    </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
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
                                    <?php $__empty_1 = true; $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><?php echo e($item->funcionario->nome); ?></td>
                                        <td><?php echo e($item->created_at); ?></td>
                                        <td><?php echo e(__moeda($item->valor_final)); ?></td>
                                        <td> <?php echo e(strtoupper($item->mes)); ?>/<?php echo e($item->ano); ?> </td>
                                        <td>
                                            <span class="codigo" style="width: 150px;" id="id">
                                                <?php if($item->conta_pagar_id == 0): ?>
                                                <span class="btn btn-danger btn-sm">Não</span>
                                                <?php else: ?>
                                                <span class="btn btn-success btn-sm">Sim</span>
                                                <a target="_blank" href="/contasPagar/edit/<?php echo e($item->conta_pagar_id); ?>">#<?php echo e($item->conta_pagar_id); ?></a>
                                                <?php endif; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                <a target="_blank" href="<?php echo e(route('rh.holerite.show', ['id' => $item->funcionario_id, 'mes' => $item->mes, 'ano' => $item->ano])); ?>" class="btn btn-sm btn-dark" title="Gerar PDF do holerite">
                                                    <i class="bx bxs-file-pdf"></i>
                                                </a>
                                                <form action="<?php echo e(route('apuracaoMensal.destroy', $item->id)); ?>" method="post" id="form-<?php echo e($item->id); ?>">
                                                    <?php echo method_field('delete'); ?>
                                                    <?php echo csrf_field(); ?>
                                                    <button type="button" class="btn btn-delete btn-sm btn-danger">
                                                        <i class="bx bx-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="6" class="text-center">Nenhuma apuração cadastrada para esta empresa. Use <strong>Gerar automática</strong> para criar a competência e, se quiser, já integrar com o financeiro.</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('default.layout',['title' => 'Apuração mensal'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home2/wesl4494/public_html/padariacasadopao.com/resources/views/apuracao_mensal/index.blade.php ENDPATH**/ ?>