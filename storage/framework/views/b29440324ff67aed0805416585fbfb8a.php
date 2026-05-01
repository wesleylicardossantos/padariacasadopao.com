<?php $__env->startSection('content'); ?>
<div class="page-content">
    <div class="card">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <div>
                    <h6 class="mb-0 text-uppercase">RH - Férias</h6>
                    <small class="text-muted">Controle de períodos aquisitivos, programação e status.</small>
                </div>
                <?php ($isRhAdmin = ((int) (optional(auth()->user())->adm ?? 0) === 1)); ?>
                <a href="/rh/ferias/create" class="btn btn-success"><i class="bx bx-plus"></i> Nova programação</a>
            </div>

            <?php if(!empty($semTabela)): ?>
                <div class="alert alert-danger">Estrutura de férias ainda não instalada. Execute o SQL do patch RH V3.</div>
            <?php endif; ?>

            <?php if(!empty($schemaLegado)): ?>
                <div class="alert alert-warning">Base antiga detectada: listagem adaptada para a estrutura atual do banco.</div>
            <?php endif; ?>

            <form method="GET" action="/rh/ferias">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Funcionário</label>
                        <input type="text" class="form-control" name="funcionario" value="<?php echo e($funcionario ?? ''); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <option value="">Todos</option>
                            <option value="programada" <?php if(($status ?? '') == 'programada'): ?> selected <?php endif; ?>>Programada</option>
                            <option value="PROGRAMADA" <?php if(($status ?? '') == 'PROGRAMADA'): ?> selected <?php endif; ?>>PROGRAMADA</option>
                            <option value="gozo" <?php if(($status ?? '') == 'gozo'): ?> selected <?php endif; ?>>Em gozo</option>
                            <option value="concluida" <?php if(($status ?? '') == 'concluida'): ?> selected <?php endif; ?>>Concluída</option>
                            <option value="pendente" <?php if(($status ?? '') == 'pendente'): ?> selected <?php endif; ?>>Pendente</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary w-100">Filtrar</button>
                    </div>
                </div>
            </form>

            <hr>

            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Funcionário</th>
                            <th>Período aquisitivo</th>
                            <th>Gozo</th>
                            <th>Dias</th>
                            <th>Status</th>
                            <th>Obs.</th>
                            <?php if($isRhAdmin): ?>
                                <th class="text-center" style="width: 120px;">Ações</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if(!empty($semTabela)): ?>
                        <tr><td colspan="<?php echo e($isRhAdmin ? 7 : 6); ?>" class="text-center">Estrutura RH V3 pendente.</td></tr>
                    <?php else: ?>
                        <?php $__empty_1 = true; $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e(optional($item->funcionario)->nome); ?></td>
                            <td>
                                <?php if(!empty($item->periodo_aquisitivo_inicio) && !empty($item->periodo_aquisitivo_fim)): ?>
                                    <?php echo e(\Carbon\Carbon::parse($item->periodo_aquisitivo_inicio)->format('d/m/Y')); ?> até <?php echo e(\Carbon\Carbon::parse($item->periodo_aquisitivo_fim)->format('d/m/Y')); ?>

                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td><?php echo e(\Carbon\Carbon::parse($item->data_inicio)->format('d/m/Y')); ?> até <?php echo e(\Carbon\Carbon::parse($item->data_fim)->format('d/m/Y')); ?></td>
                            <td><?php echo e($item->dias ?? (\Carbon\Carbon::parse($item->data_inicio)->diffInDays(\Carbon\Carbon::parse($item->data_fim)) + 1)); ?></td>
                            <td><span class="badge bg-secondary"><?php echo e(ucfirst(strtolower($item->status))); ?></span></td>
                            <td><?php echo e($item->observacao); ?></td>
                            <?php if($isRhAdmin): ?>
                                <td class="text-center">
                                    <div class="d-inline-flex align-items-center gap-2">
                                        <a href="<?php echo e(route('rh.ferias.edit', $item->id)); ?>" class="btn btn-sm btn-outline-primary" title="Editar">
                                            <i class="bx bx-edit-alt"></i>
                                        </a>
                                        <form method="POST" action="<?php echo e(route('rh.ferias.destroy', $item->id)); ?>" class="d-inline" onsubmit="return confirm('Deseja excluir esta programação de férias?');">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Excluir">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="<?php echo e($isRhAdmin ? 7 : 6); ?>" class="text-center">Nenhuma programação encontrada.</td></tr>
                        <?php endif; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if(empty($semTabela)): ?>
            <?php echo $data->appends(request()->all())->links(); ?>

            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('default.layout',['title' => 'RH - Férias'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home2/wesl4494/public_html/padariacasadopao.com/resources/views/rh/ferias/index.blade.php ENDPATH**/ ?>