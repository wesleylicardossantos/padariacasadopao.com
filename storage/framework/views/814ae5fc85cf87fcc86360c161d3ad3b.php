<?php $__env->startSection('content'); ?>
<style>.rh-card{border:1px solid #e9edf5;border-radius:14px;box-shadow:0 8px 24px rgba(15,23,42,.04)}</style>
<div class="page-content">
    <div class="card rh-card">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <div>
                    <h6 class="mb-0 text-uppercase">RH - Salários</h6>
                    <small class="text-muted">Gestão salarial com reajuste registrado em histórico.</small>
                </div>
                <a href="<?php echo e(route('rh.salarios.create')); ?>" class="btn btn-success"><i class="bx bx-plus"></i> Novo reajuste</a>
            </div>

            <form method="GET" action="<?php echo e(route('rh.salarios.index')); ?>">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Pesquisar por nome</label>
                        <input type="text" class="form-control" name="nome" value="<?php echo e($nome ?? ''); ?>">
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
                            <th>Função</th>
                            <th>Salário atual</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($item->nome); ?></td>
                            <td><?php echo e($item->funcao ?? '-'); ?></td>
                            <td>R$ <?php echo e(number_format((float)$item->salario, 2, ',', '.')); ?></td>
                            <td><?php echo (!isset($item->ativo) || $item->ativo) ? '<span class="badge bg-success">Ativo</span>' : '<span class="badge bg-danger">Inativo</span>'; ?></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="4" class="text-center">Nenhum funcionário encontrado.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php echo $data->appends(request()->all())->links(); ?>

        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('default.layout',['title' => 'RH - Salários'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home2/wesl4494/public_html/padariacasadopao.com/resources/views/rh/salarios/index.blade.php ENDPATH**/ ?>