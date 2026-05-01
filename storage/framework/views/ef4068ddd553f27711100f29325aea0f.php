<?php $__env->startSection('content'); ?>
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    <a href="<?php echo e(route('naturezas.create')); ?>" type="button" class="btn btn-success">
                        <i class="bx bx-plus"></i> Nova natureza de operação
                    </a>
                </div>
            </div>
            <div class="col">
                <h6 class="mb-0 text-uppercase">Naturezas de operação</h6>
                <?php echo Form::open()->fill(request()->all())
                ->get(); ?>

                <div class="row">
                    <div class="col-md-3">
                        <?php echo Form::text('natureza', 'Pesquisar por descrição'); ?>

                    </div>
                    <div class="col-md-3 text-left ">
                        <br>
                        <button class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
                        <a id="clear-filter" class="btn btn-danger" href="<?php echo e(route('naturezas.index')); ?>"><i class="bx bx-eraser"></i> Limpar</a>
                    </div>
                </div>
                <?php echo Form::close(); ?>

                <hr />
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table mb-0 table-striped">
                                <thead class="">
                                    <tr>
                                        <th width="50%">Descrição</th>
                                        <th width="25%">CFOP Saída</th>
                                        <th width="25%">CFOP Entrada</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__empty_1 = true; $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><?php echo e($item->natureza); ?></td>
                                        <td><?php echo e($item->CFOP_saida_estadual); ?>/<?php echo e($item->CFOP_saida_inter_estadual); ?></td>
                                        <td><?php echo e($item->CFOP_entrada_estadual); ?>/<?php echo e($item->CFOP_entrada_inter_estadual); ?></td>
                                        <td>
                                            <form action="<?php echo e(route('naturezas.destroy', $item->id)); ?>" method="post" id="form-<?php echo e($item->id); ?>">
                                                <?php echo method_field('delete'); ?>
                                                <a href="<?php echo e(route('naturezas.edit', $item)); ?>" class="btn btn-warning btn-sm text-white">
                                                    <i class="bx bx-edit"></i>
                                                </a>

                                                <?php echo csrf_field(); ?>
                                                <button type="button" class="btn btn-delete btn-sm btn-danger">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="4" class="text-center">Nada encontrado</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <?php echo $data->appends(request()->all())->links(); ?>

        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('default.layout',['title' => 'Naturezas de operação'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home2/wesl4494/public_html/padariacasadopao.com/resources/views/naturezas/index.blade.php ENDPATH**/ ?>