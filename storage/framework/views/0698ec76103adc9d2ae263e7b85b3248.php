<?php $__env->startSection('content'); ?>
<div class="page-content">
    <div class="card">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <div>
                    <h6 class="mb-0 text-uppercase">RH - Absenteísmo</h6>
                    <small class="text-muted">Controle de faltas, atrasos, atestados e saídas antecipadas.</small>
                </div>
                <a href="<?php echo e(route('rh.faltas.create')); ?>" class="btn btn-success">Nova ocorrência</a>
            </div>

            <?php if(!empty($semTabela)): ?>
                <div class="alert alert-danger">Tabela de absenteísmo ainda não instalada. Execute o SQL do patch RH V4.</div>
            <?php endif; ?>

            <form method="GET" action="<?php echo e(route('rh.faltas.index')); ?>">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4"><label class="form-label">Funcionário</label><input type="text" class="form-control" name="funcionario" value="<?php echo e($funcionario ?? ''); ?>"></div>
                    <div class="col-md-3">
                        <label class="form-label">Tipo</label>
                        <select class="form-select" name="tipo">
                            <option value="">Todos</option>
                            <?php $__currentLoopData = \App\Models\RHFalta::tipos(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($key); ?>" <?php if(($tipo ?? '') == $key): ?> selected <?php endif; ?>><?php echo e($label); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-md-2"><button class="btn btn-primary w-100">Filtrar</button></div>
                </div>
            </form>

            <hr>

            <div class="table-responsive">
                <table class="table table-striped">
                    <thead><tr><th>Funcionário</th><th>Tipo</th><th>Data</th><th>Horas</th><th>Descrição</th></tr></thead>
                    <tbody>
                        <?php if(!empty($semTabela)): ?>
                            <tr><td colspan="5" class="text-center">Estrutura RH V4 pendente.</td></tr>
                        <?php else: ?>
                            <?php $__empty_1 = true; $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e(optional($item->funcionario)->nome); ?></td>
                                <td><?php echo e(\App\Models\RHFalta::tipos()[$item->tipo] ?? ucfirst($item->tipo)); ?></td>
                                <td><?php echo e(\Carbon\Carbon::parse($item->data_referencia)->format('d/m/Y')); ?></td>
                                <td><?php echo e($item->quantidade_horas ?? '-'); ?></td>
                                <td><?php echo e($item->descricao); ?></td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="5" class="text-center">Nenhuma ocorrência encontrada.</td></tr>
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

<?php echo $__env->make('default.layout',['title' => 'RH - Absenteísmo'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home2/wesl4494/public_html/padariacasadopao.com/resources/views/rh/faltas/index.blade.php ENDPATH**/ ?>