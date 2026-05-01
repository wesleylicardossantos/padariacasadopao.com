<?php $__env->startSection('content'); ?>
<style>
.rh-alert-card{border:1px solid #e8edf5;border-radius:16px;box-shadow:0 8px 24px rgba(15,23,42,.04)}
</style>
<div class="page-content">
    <div class="card rh-alert-card">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <div>
                    <h5 class="mb-0">RH - Alertas Inteligentes</h5>
                    <small class="text-muted">Monitoramento de vencimentos, férias próximas e itens críticos.</small>
                </div>
                <div class="d-flex gap-2">
                    <a href="/rh/dashboard-v5" class="btn btn-dark">Dashboard V5</a>
                    <a href="/rh" class="btn btn-secondary">Voltar</a>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Tipo</th>
                            <th>Funcionário</th>
                            <th>Descrição</th>
                            <th>Dias</th>
                            <th>Prioridade</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $alertas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($item['tipo']); ?></td>
                            <td><?php echo e($item['funcionario']); ?></td>
                            <td><?php echo e($item['descricao']); ?></td>
                            <td><?php echo e($item['dias']); ?></td>
                            <td><span class="badge bg-<?php echo e($item['gravidade']); ?>"><?php echo e(strtoupper($item['gravidade'])); ?></span></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="5" class="text-center">Nenhum alerta encontrado no momento.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('default.layout',['title' => 'RH - Alertas'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home2/wesl4494/public_html/padariacasadopao.com/resources/views/rh/alertas/index.blade.php ENDPATH**/ ?>