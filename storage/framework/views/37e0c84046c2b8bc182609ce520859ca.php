<?php $__env->startSection('content'); ?>
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
            </div>
            <div class="">
                <h6 class="mb-0 text-uppercase">Compras</h6>
                <?php echo Form::open()
                ->fill(request()
                ->all())
                ->get(); ?>

                <div class="row">
                    <div class="col-md-5 mt-3">
                        <?php echo Form::select('fornecedor_id', 'Pesquisar por fornecedor'); ?>

                    </div>
                    <div class="col-md-2 mt-3">
                        <?php echo Form::date('start_date', 'Data inicial'); ?>

                    </div>
                    <div class="col-md-2 mt-3">
                        <?php echo Form::date('end_date', 'Data final'); ?>

                    </div>
                    <?php if(empresaComFilial()): ?>
                    <?php echo __view_locais_select_filtro("Local", isset($filial_id) ? $filial_id : ''); ?>

                    <?php endif; ?>
                    <div class="col-md-3 text-left mt-3">
                        <br>
                        <button class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
                        <a id="clear-filter" class="btn btn-danger" href="<?php echo e(route('compras.index')); ?>"><i class="bx bx-eraser"></i>Limpar</a>
                    </div>
                </div>
                <?php echo Form::close(); ?>

                <hr />
                <h6>Lista de compras</h6>
                
                <div class="row">
                    <div class="ms-auto">
                        <a href="<?php echo e(route('compraManual.index')); ?>" type="button" class="btn btn-success">
                            <i class="bx bx-plus"></i> Nova compra
                        </a>
                    </div>
                </div>
                <div class="card mt-3">
                    <div class="card-body">
                        <div class="table-responsive tbl-400">
                            <table class="table mb-0 table-striped">
                                <thead class="">
                                    <tr>
                                        <th>Ações</th>
                                        <th>Fornecedor</th>
                                        <th>Data da compra</th>
                                        <th>Total</th>
                                        <?php if(empresaComFilial()): ?>
                                        <th>Local</th>
                                        <?php endif; ?>
                                        <th>Desconto</th>
                                        <th>Usuário</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__empty_1 = true; $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Ações</button>
                                                <ul class="dropdown-menu">
                                                    <form action="<?php echo e(route('compras.destroy', $item->id)); ?>" method="post" id="form-<?php echo e($item->id); ?>">
                                                        <?php echo method_field('delete'); ?>
                                                        <?php echo csrf_field(); ?>
                                                        <li>
                                                            <a class="dropdown-item" href="<?php echo e(route('compras.edit', $item->id)); ?>">Editar</a>
                                                        </li>
                                                        <?php if($item->estado == 'novo' || $item->estado == 'rejeitado' || !$item->estado): ?>
                                                        <li>
                                                            <button class="dropdown-item btn-delete">Apagar</button>
                                                        </li>
                                                        <?php endif; ?>
                                                        <li>
                                                            <a class="dropdown-item" href="<?php echo e(route('compras.nfe-entrada', $item->id)); ?>">Emitir NFe Entrada</a>
                                                        </li>
                                                    </form>
                                                </ul>
                                            </div>
                                        </td>
                                        <td><?php echo e($item->fornecedor->razao_social); ?></td>
                                        <td><?php echo e(__data_pt($item->created_at, 1)); ?></td>
                                        <td><?php echo e(__moeda($item->total)); ?></td>
                                        <?php if(empresaComFilial()): ?>
                                        <td>
                                            <?php echo e($item->filial_id ? $item->filial->descricao : 'Matriz'); ?>

                                        </td>
                                        <?php endif; ?>
                                        <td><?php echo e(__moeda($item->desconto)); ?></td>
                                        <td><?php echo e($item->usuario->nome); ?></td>
                                        <td><?php echo e(strtoupper($item->estado)); ?></td>
                                    </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="8" class="text-center">Nada encontrado</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <?php if(isset($data->appends)): ?>
            <?php echo $data->appends(request()->all())->links(); ?>

            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('default.layout', ['title' => 'Compras'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home2/wesl4494/public_html/padariacasadopao.com/resources/views/compras/index.blade.php ENDPATH**/ ?>