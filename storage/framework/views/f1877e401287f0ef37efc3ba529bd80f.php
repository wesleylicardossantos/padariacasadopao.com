<?php $__env->startSection('content'); ?>
<style>
    .produtos-table-wrapper {
        max-height: 400px;
        overflow-y: auto;
        overflow-x: auto;
    }

    .produtos-table-wrapper thead th {
        position: sticky;
        top: 0;
        z-index: 20;
        background: #f8f9fa;
        box-shadow: inset 0 -1px 0 #333;
        white-space: nowrap;
    }

    .produtos-table-wrapper table {
        margin-bottom: 0;
    }
</style>
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto col-12">
                    <a href="<?php echo e(route('produtos.create')); ?>" type="button" class="btn btn-success col-12 col-lg-2">
                        <i class="bx bx-plus"></i> Novo produto
                    </a>
                    <a href="<?php echo e(route('produtos.exportacaoBalanca')); ?>" type="button" class="btn btn-dark col-12 col-lg-2">
                        <i class="bx bx-upvote"></i> Exportar balança
                    </a>
                    <a href="<?php echo e(route('produtos.import')); ?>" type="button" class="btn btn-warning col-12 col-lg-2">
                        <i class="bx bx-file"></i> Importar produtos
                    </a>
                    <a href="<?php echo e(route ('divisaoGrade.index')); ?>" type="button" class="btn btn-info col-12 col-lg-2">
                        <i class="bx bx-dice-6"></i> Divisão de grade
                    </a>

                </div>
            </div>
            <div class="col">
                <h6 class="mb-0 text-uppercase">Produtos</h6>
                <?php echo Form::open()->fill(request()->all())
                ->get(); ?>

                <div class="row">
                    <div class="col-md-2">
                        <?php echo Form::select('tipo', 'Tipo de pesquisa',
                        ['nome' => 'Descrição',
                        'referencia' => 'Referência',
                        'codBarras' => 'Código de barras'
                        ])
                        ->attrs(['class' => 'select2']); ?>

                    </div>
                    <div class="col-md-4">
                        <?php echo Form::text('nome', 'Pesquisar por nome'); ?>

                    </div>
                    <div class="col-md-2">
                        <?php echo Form::select('categoria_id', 'Categoria', ['' => 'Todas'] + $categorias->pluck('nome', 'id')->all())->attrs(['class' => 'form-select']); ?>

                    </div>
                    <div class="col-md-2">
                        <?php echo Form::select('marca_id', 'Marca', ['' => 'Todas'] + $marcas->pluck('nome', 'id')->all())->attrs(['class' => 'form-select']); ?>

                    </div>
                    <div class="col-md-1">
                        <?php echo Form::select('estoque', 'Estoque', ['0' => '--', '1' => 'Positivo', '-1' => 'Negativo'])->attrs(['class' => 'form-select']); ?>

                    </div>
                    <div class="col-md-3">
                        <?php echo Form::select('classificacao', 'Classificação', [
                            'az' => 'Descrição A-Z',
                            'za' => 'Descrição Z-A',
                            'recentes' => 'Mais recentes',
                            'antigos' => 'Mais antigos'
                        ])->attrs(['class' => 'form-select']); ?>

                    </div>

                    <div class="mt-3">
                        <?php if(empresaComFilial()): ?>
                        <?php echo __view_locais_select_filtro("Local", isset($filial_id) ? $filial_id : ''); ?>

                        <?php endif; ?>
                    </div>
                    <div class="col-md-3 text-left ">
                        <br>
                        <button class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
                        <a id="clear-filter" class="btn btn-danger" href="<?php echo e(route('produtos.index')); ?>"><i class="bx bx-eraser"></i>Limpar</a>
                    </div>
                </div>

                <?php echo Form::close(); ?>


                <hr />
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive tbl-400 produtos-table-wrapper">
                            <table class="table mb-0 table-striped">
                                <thead class="">
                                    <tr>
                                        <th></th>
                                        <th>Ações</th>
                                        <th width="30%">Descrição</th>
                                        <th width="20%">Valor de compra</th>
                                        <th width="20%">Valor de venda</th>
                                        <th width="20%">Un. compra/venda</th>
                                        <th width="20%">Data cadastro</th>
                                        <th>Gerenciar estoque</th>
                                        <?php if(empresaComFilial()): ?>
                                        <th>Disponibilidade</th>
                                        <?php endif; ?>
                                        <th>Estoque atual</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__empty_1 = true; $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><img class="img-round" src="<?php echo e($p->img); ?>"></td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Ações</button>
                                                <ul class="dropdown-menu" style="z-index: 999">
                                                    <form action="<?php echo e(route('produtos.destroy', $p->id)); ?>" method="post" id="form-<?php echo e($p->id); ?>">
                                                        <?php echo method_field('delete'); ?>
                                                        <?php echo csrf_field(); ?>
                                                        <li>
                                                            <a class="dropdown-item" href="<?php echo e(route('produtos.edit', $p->id)); ?>">Editar</a>
                                                        </li>

                                                        <li>
                                                            <button class="dropdown-item btn-delete">Apagar</button>
                                                        </li>

                                                        <li>
                                                            <a class="dropdown-item" href="<?php echo e(route('produtos.movimentacao', $p->id)); ?>">Movimentação</a>
                                                        </li>

                                                        <li>
                                                            <a class="dropdown-item" href="<?php echo e(route('produtos.duplicar' ,$p->id)); ?>">Duplicar Produto</a>
                                                        </li>

                                                        <li>
                                                            <a class="dropdown-item" href="<?php echo e(route('produtos.etiqueta', $p->id)); ?>">Código de Barras</a>
                                                        </li>
                                                    </form>
                                                </ul>
                                            </div>
                                        </td>
                                        <td><?php echo e($p->nome); ?> <?php echo e($p->str_grade); ?></td>
                                        <td><?php echo e(__moeda($p->valor_compra)); ?></td>
                                        <td><?php echo e(__moeda($p->valor_venda)); ?></td>
                                        <td><?php echo e($p->unidade_compra); ?>/<?php echo e($p->unidade_venda); ?></td>
                                        <td><?php echo e(__data_pt($p->created_at)); ?></td>
                                        <td><?php echo e($p->gerenciar_estoque == 0 ? 'Não' : 'Sim'); ?></td>
                                        <?php if(empresaComFilial()): ?>
                                        <td>
                                            <span class="codigo" style="width: 200px;">
                                                <?php echo $p->locais_produto(); ?>

                                            </span>
                                        </td>
                                        <?php endif; ?>
                                        <td>
                                            <?php if(empresaComFilial()): ?>
                                            <?php echo e($p->estoquePorLocal($p->locais)); ?>

                                            <?php else: ?>
                                            <?php echo e($p->estoquePorLocal($filial_id)); ?>

                                            <?php endif; ?>
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

<?php echo $__env->make('default.layout',['title' => 'Produtos'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home2/wesl4494/public_html/padariacasadopao.com/resources/views/produtos/index.blade.php ENDPATH**/ ?>