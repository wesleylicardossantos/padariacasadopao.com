<?php $__env->startSection('content'); ?>
<div class="page-content">
    <div class="card">
        <div class="card-body p-4">
            <?php
                $sessionUser = session('user_logged');
                $isRhAdmin = ((int) (optional(auth()->user())->adm ?? 0) === 1)
                    || ((int) data_get($sessionUser, 'adm', 0) === 1)
                    || (!empty(data_get($sessionUser, 'login')) && function_exists('isSuper') && isSuper(data_get($sessionUser, 'login')));
            ?>

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <div>
                    <h6 class="mb-0 text-uppercase">RH - Desligamentos</h6>
                    <small class="text-muted">Registro formal de saídas, cálculo de rescisão e documentos.</small>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <?php if(!empty($rescisaoInstalada)): ?>
                        <a href="<?php echo e(route('rh.desligamentos.dashboard_executivo')); ?>" class="btn btn-outline-primary">Dashboard executivo</a>
                        <a href="<?php echo e(route('rh.desligamentos.exportar_fgts')); ?>" class="btn btn-outline-success">Exportar FGTS/SEFIP</a>
                    <?php endif; ?>
                    <a href="<?php echo e(route('rh.desligamentos.create')); ?>" class="btn btn-danger">Novo desligamento</a>
                </div>
            </div>

            <?php if(!empty($semTabela)): ?>
                <div class="alert alert-danger">Tabela de desligamentos ainda não instalada. Execute o SQL do patch RH.</div>
            <?php endif; ?>

            <form method="GET" action="<?php echo e(route('rh.desligamentos.index')); ?>">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4"><label class="form-label">Funcionário</label><input type="text" class="form-control" name="funcionario" value="<?php echo e($funcionario ?? ''); ?>"></div>
                    <div class="col-md-2"><button class="btn btn-primary w-100">Filtrar</button></div>
                </div>
            </form>

            <hr>

            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Funcionário</th>
                            <th>Data</th>
                            <th>Tipo</th>
                            <th>Motivo</th>
                            <th>Observação</th>
                            <?php if($isRhAdmin): ?>
                                <th class="text-center" style="width: 200px;">Ação</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($semTabela)): ?>
                            <tr><td colspan="<?php echo e($isRhAdmin ? 6 : 5); ?>" class="text-center">Estrutura RH pendente.</td></tr>
                        <?php else: ?>
                            <?php $__empty_1 = true; $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e(optional($item->funcionario)->nome); ?></td>
                                <td><?php echo e(\Carbon\Carbon::parse($item->data_desligamento)->format('d/m/Y')); ?></td>
                                <td><?php echo e($item->tipo); ?></td>
                                <td><?php echo e($item->motivo); ?></td>
                                <td><?php echo e($item->observacao); ?></td>
                                <?php if($isRhAdmin): ?>
                                    <td class="text-center">
                                        <?php if(!empty($item->rescisao_id) && !empty($rescisaoInstalada)): ?>
                                            <a href="<?php echo e(route('rh.desligamentos.show', $item->rescisao_id)); ?>" class="btn btn-sm btn-outline-primary" title="Detalhes"><i class="bx bx-show"></i></a>
                                        <?php endif; ?>
                                        <form method="POST" action="<?php echo e(route('rh.desligamentos.destroy', $item->id)); ?>" class="d-inline" onsubmit="return confirm('Deseja excluir este desligamento?');">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Excluir">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="<?php echo e($isRhAdmin ? 6 : 5); ?>" class="text-center">Nenhum desligamento encontrado.</td></tr>
                            <?php endif; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if(empty($semTabela) && method_exists($data, 'links')): ?>
            <?php echo $data->appends(request()->all())->links(); ?>

            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('default.layout',['title' => 'RH - Desligamentos'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home2/wesl4494/public_html/padariacasadopao.com/resources/views/rh/desligamentos/index.blade.php ENDPATH**/ ?>