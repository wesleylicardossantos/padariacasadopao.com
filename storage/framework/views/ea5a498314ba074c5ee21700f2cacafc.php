<?php $__env->startSection('content'); ?>
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    <a href="<?php echo e(route('eventoSalario.create')); ?>" type="button" class="btn btn-success">
                        <i class="bx bx-plus"></i> Novo evento
                    </a>
                </div>
            </div>
            <div class="col">
                <h5 class="">Lista de eventos</h5>
                <p style="color: rgb(14, 14, 226)">Eventos: <?php echo e(sizeof($data)); ?></p>
                <?php echo Form::open()->fill(request()->all())
                ->get(); ?>

                <div class="row">
                    <div class="col-md-5">
                        <?php echo Form::text('nome', 'Pesquisar por nome'); ?>

                    </div>
                    <div class="col-md-3 text-left ">
                        <br>
                        <button class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
                        <a id="clear-filter" class="btn btn-danger" href="<?php echo e(route('eventoSalario.index')); ?>"><i class="bx bx-eraser"></i>Limpar</a>
                    </div>
                </div>
                <?php echo Form::close(); ?>

                <hr>
                <div class="mt-3">
                    <div class="table-responsive">
                        <table class="table mb-0 table-striped">
                            <thead class="">
                                <tr>
                                    <th>Nome</th>
                                    <th>Tipo</th>
                                    <th>Método</th>
                                    <th>Ativo</th>
                                    <th>Condição</th>
                                    <th>Tipo valor</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><?php echo e($item->nome); ?></td>
                                    <td><?php echo e($item->tipo); ?></td>
                                    <td><?php echo e($item->metodo); ?></td>
                                    <td>
                                        <?php if($item->ativo): ?>
                                        <span class="btn btn-success btn-sm">Sim</span>
                                        <?php else: ?>
                                        <span class="btn btn-danger btn-sm">Não</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo e($item->condicao); ?></td>
                                    <td><?php echo e($item->tipo_valor); ?></td>

                                    <td>
                                        <form action="<?php echo e(route('eventoSalario.destroy', $item->id)); ?>" method="post" id="form-<?php echo e($item->id); ?>">
                                            <?php echo method_field('delete'); ?>
                                            <a href="<?php echo e(route('eventoSalario.edit', $item)); ?>" class="btn btn-warning btn-sm text-white">
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
            <?php echo $data->appends(request()->all())->links(); ?>

        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('default.layout',['title' => 'Eventos'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home2/wesl4494/public_html/padariacasadopao.com/resources/views/evento_salario/index.blade.php ENDPATH**/ ?>