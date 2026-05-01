<?php $__env->startSection('content'); ?>
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    <a href="<?php echo e(route('fornecedores.create')); ?>" type="button" class="btn btn-success">
                        <i class="bx bx-plus"></i> Novo fornecedor
                    </a>
                </div>
            </div>
            <div class="col">
                <h6 class="mb-0 text-uppercase">Fornecedores</h6>
                <?php echo Form::open()->fill(request()->all())
                ->get(); ?>

                <div class="row">
                    <div class="col-md-3">
                        <?php echo Form::text('razao_social', 'Pesquisar por razão'); ?>

                    </div>
                    <div class="col-md-3">
                        <?php echo Form::text('cpf_cnpj', 'Pesquisar por CPF/CNPJ')
                        ->attrs(['class' => 'cpf_cnpj'])
                        ->type('tel'); ?>

                    </div>
                    <div class="col-md-3 text-left ">
                        <br>
                        <button class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
                        <a id="clear-filter" class="btn btn-danger" href="<?php echo e(route('fornecedores.index')); ?>"><i class="bx bx-eraser"></i>Limpar</a>
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
                                        <th>Razão social</th>
                                        <th>CPF/CNPJ</th>
                                        <th>Data de cadastro</th>
                                        <th>Celular</th>
                                        <th>Endereço</th>
                                        <th>Cidade</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__empty_1 = true; $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><?php echo e($item->razao_social); ?></td>
                                        <td><?php echo e($item->cpf_cnpj); ?></td>
                                        <td><?php echo e(__data_pt($item->created_at)); ?></td>
                                        <td><?php echo e($item->celular); ?></td>
                                        <td><?php echo e($item->rua); ?>, <?php echo e($item->numero); ?> | <?php echo e($item->bairro); ?></td>
                                        <td><?php echo e($item->cidade->info); ?></td>
                                        <td>
                                            <form action="<?php echo e(route('fornecedores.destroy', $item->id)); ?>" method="post" id="form-<?php echo e($item->id); ?>">
                                                <?php echo method_field('delete'); ?>
                                                <a href="<?php echo e(route('fornecedores.edit', $item)); ?>" class="btn btn-warning btn-sm text-white">
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
                                        <td colspan="7" class="text-center">Nada encontrado</td>
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

<?php echo $__env->make('default.layout',['title' => 'Fornecedores'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home2/wesl4494/public_html/padariacasadopao.com/resources/views/fornecedores/index.blade.php ENDPATH**/ ?>