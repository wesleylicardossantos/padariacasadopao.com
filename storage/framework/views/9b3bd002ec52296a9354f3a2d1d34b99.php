<?php $__env->startSection('content'); ?>
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    <a href="<?php echo e(route('conta-pagar.create')); ?>" type="button" class="btn btn-success">
                        <i class="bx bx-plus"></i> Nova conta
                    </a>
                </div>
            </div>
            <div class="col">
                <h6 class="mb-0 text-uppercase">Contas a pagar</h6>
                <?php echo Form::open()
                ->fill(request()
                ->all())
                ->get(); ?>

                <div class="row">
                    <div class="col-md-4">
                        <?php echo Form::select('fornecedor_id', 'Fornecedor')
                        ->attrs(['class' => 'select2']); ?>

                    </div>
                    <div class="col-md-2">
                        <?php echo Form::select('type_search', 'Tipo de pesquisa',
                        [
                        'created_at' => 'Data de cadastro',
                        'data_vencimento' => 'Data de vencimento',
                        'data_pagamento' => 'Data de pagamento',
                        ])->attrs(['class' => 'form-select']); ?>

                    </div>

                    <div class="col-md-2">
                        <?php echo Form::date('start_date', 'Data inicial'); ?>

                    </div>
                    <div class="col-md-2">
                        <?php echo Form::date('end_date', 'Data final'); ?>

                    </div>
                    <div class="col-md-2">
                        <?php echo Form::select('status', 'Estado',
                        [
                        '' => 'Todos',
                        '1' => 'Pago',
                        '0' => 'Pendente',
                        ])->attrs(['class' => 'form-select']); ?>

                    </div>

                    <div class="mt-3">
                        <?php if(empresaComFilial()): ?>
                        <?php echo __view_locais_select_filtro("Local", isset($filial_id) ? $filial_id : ''); ?>

                        <?php endif; ?>
                    </div>

                    <div class="col-md-4 text-left ">
                        <br>
                        <button class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
                        <a id="clear-filter" class="btn btn-danger" href="<?php echo e(route('conta-pagar.index')); ?>"><i class="bx bx-eraser"></i> Limpar</a>
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
                                        <th width="">Fornecedor</th>
                                        <th width="">Categoria</th>
                                        <?php if(empresaComFilial()): ?>
                                        <th><span style="width: 150px;">Local</span></th>
                                        <?php endif; ?>
                                        <th width="">Valor integral</th>
                                        <th width="">Valor pago</th>
                                        <th width="">Data de cadastro</th>
                                        <th width="">Data de vencimento</th>
                                        <th width="">Data de pagamento</th>
                                        <th width="">Estado</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__empty_1 = true; $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><?php echo e($item->fornecedor ? $item->fornecedor->razao_social : '--'); ?></td>
                                        <td><?php echo e(optional($item->categoria)->nome ?? '--'); ?></td>
                                        <?php if(empresaComFilial()): ?>
                                        <td>
                                            <?php echo e($item->filial_id ? (optional($item->filial)->descricao ?? '--') : 'Matriz'); ?>

                                        </td>
                                        <?php endif; ?>
                                        <td><?php echo e(__moeda($item->valor_integral)); ?></td>
                                        <td><?php echo e(__moeda($item->valor_pago)); ?></td>
                                        <td><?php echo e(__data_pt($item->created_at)); ?></td>
                                        <td><?php echo e(__data_pt($item->data_vencimento, false)); ?></td>
                                        <td><?php echo e($item->status ? __data_pt($item->data_pagamento, false) : '--'); ?></td>
                                        <td>
                                            <?php if($item->status): ?>
                                            <span class="btn btn-success position-relative me-lg-5 btn-sm">
                                                <i class="bx bx-like"></i> Pago
                                            </span>
                                            <?php else: ?>
                                            <span class="btn btn-warning position-relative me-lg-5 btn-sm">
                                                <i class="bx bx-error"></i> Pendente
                                            </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <form action="<?php echo e(route('conta-pagar.destroy', $item->id)); ?>" method="post" id="form-<?php echo e($item->id); ?>">
                                                <?php echo method_field('delete'); ?>
                                                <?php echo csrf_field(); ?>

                                                <?php if(!$item->status): ?>
                                                <a title="Editar" href="<?php echo e(route('conta-pagar.edit', $item)); ?>" class="btn btn-warning btn-sm text-white">
                                                    <i class="bx bx-edit"></i>
                                                </a>
                                                <a title="Pagar conta" href="<?php echo e(route('conta-pagar.pay', $item)); ?>" class="btn btn-success btn-sm text-white">
                                                    <i class="bx bx-dollar"></i>
                                                </a>
                                                <?php endif; ?>

                                                <?php if(is_adm()): ?>
                                                <button type="button" class="btn btn-delete btn-sm btn-danger" title="Excluir conta">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                                <?php endif; ?>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="10" class="text-center">Nada encontrado</td>
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

<?php echo $__env->make('default.layout',['title' => 'Contas a Pagar'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home2/wesl4494/public_html/padariacasadopao.com/resources/views/conta_pagar/index.blade.php ENDPATH**/ ?>