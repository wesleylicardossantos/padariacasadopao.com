<?php $__env->startSection('content'); ?>
<div class="page-content">
    <div class="card">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <div>
                    <h6 class="mb-0 text-uppercase">RH - Cálculo automático de férias</h6>
                    <small class="text-muted">Estimativa de avos e período aquisitivo por funcionário.</small>
                </div>
                <a href="/rh/dashboard-v4" class="btn btn-secondary">Voltar</a>
            </div>

            <div class="table-responsive">
                <table class="table table-striped">
                    <thead><tr><th>Funcionário</th><th>Data admissão</th><th>Período aquisitivo</th><th>Avos</th><th>Última férias</th></tr></thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($item['funcionario']->nome); ?></td>
                            <td><?php echo e($item['data_admissao'] ? $item['data_admissao']->format('d/m/Y') : '-'); ?></td>
                            <td><?php echo e($item['periodo_inicio'] ? $item['periodo_inicio']->format('d/m/Y') : '-'); ?> até <?php echo e($item['periodo_fim'] ? $item['periodo_fim']->format('d/m/Y') : '-'); ?></td>
                            <td><?php echo e($item['avos']); ?>/12</td>
                            <td>
                                <?php if($item['ultima_ferias']): ?>
                                    <?php echo e(\Carbon\Carbon::parse($item['ultima_ferias']->data_inicio)->format('d/m/Y')); ?> até <?php echo e(\Carbon\Carbon::parse($item['ultima_ferias']->data_fim)->format('d/m/Y')); ?>

                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="5" class="text-center">Nenhum funcionário encontrado.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('default.layout',['title' => 'RH - Cálculo de Férias'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home2/wesl4494/public_html/padariacasadopao.com/resources/views/rh/ferias/calculo.blade.php ENDPATH**/ ?>