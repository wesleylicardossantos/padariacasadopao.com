<?php $__env->startSection('content'); ?>
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    <a href="<?php echo e(route('categorias.create')); ?>" type="button" class="btn btn-success">
                        <i class="bx bx-plus"></i> Nova categoria
                    </a>
                </div>
            </div>
            <div class="col">
                <h6 class="mb-0 text-uppercase">Categorias</h6>
                <?php echo Form::open()->fill(request()->all())->get(); ?>

                <div class="row">
                    <div class="col-md-4">
                        <?php echo Form::text('nome', 'Pesquisar por nome'); ?>

                    </div>
                    
                    <div class="col-md-4 text-left ">
                        <br>
                        <button class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
                        <a id="clear-filter" class="btn btn-danger" href="<?php echo e(route('categorias.index')); ?>"><i class="bx bx-eraser"></i> Limpar</a>
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
                                        <th width="75%">Nome</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__empty_1 = true; $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><?php echo e($item->nome); ?></td>
                                        <td>
                                            <form action="<?php echo e(route('categorias.destroy', $item->id)); ?>" method="post" id="form-<?php echo e($item->id); ?>">
                                                <?php echo method_field('delete'); ?>
                                                <a href="<?php echo e(route('categorias.edit', $item)); ?>" class="btn btn-warning btn-sm text-white">
                                                    <i class="bx bx-edit"></i>
                                                </a>
                                                <?php echo csrf_field(); ?>
                                                <button type="button" class="btn btn-delete btn-sm btn-danger">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                                <a href="<?php echo e(route('subcategoria.index', [$item->id])); ?>" class="btn btn-info btn-sm text-white" id="info">
                                                    <i class="bx bx-menu-alt-left"></i>
                                                </a>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="2" class="text-center">Nada encontrado</td>
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

<?php echo $__env->make('default.layout', ['title' => 'Categorias'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home2/wesl4494/public_html/padariacasadopao.com/resources/views/categorias/index.blade.php ENDPATH**/ ?>