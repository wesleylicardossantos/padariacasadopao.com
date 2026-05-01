<?php $__env->startSection('content'); ?>
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
            </div>
            <?php echo Form::open()->fill(request()->all())
            ->get(); ?>

            <h5 class="">Ajuste de estoque</h5>
            <div class="row">
                <div class="col-md-6">
                    <?php echo Form::select('produto_id', 'Pesquise o produto'); ?>

                </div>
                <div class="col-md-4">
                    <br>
                    <button class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
                    <a id="clear-filter" class="btn btn-danger" href="<?php echo e(route('estoque.index')); ?>"><i class="bx bx-eraser"></i> Limpar</a>
                </div>
            </div>
            <?php echo Form::close(); ?>

            <hr>
            <h6 class="mt-4">Estoque</h6>
            <p>Total de registros: <strong><?php echo e($data->total()); ?></strong></p>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Produto</th>
                                <?php if(empresaComFilial()): ?>
                                <th>Local</th>
                                <?php endif; ?>
                                <th>Categoria</th>
                                <th>Quantidade</th>
                                <th>Custo</th>
                                <th>Venda</th>
                                <th>Sub custo</th>
                                <th>Sub venda</th>
                                <th>Movimentação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td><?php echo e($item->produto->nome); ?><?php echo e($item->produto->grade ? " (" . $item->produto->str_grade . ")" : ""); ?></td>
                                <?php if(empresaComFilial()): ?>
                                <td><?php echo e($item->filial ? $item->filial->descricao : 'Matriz'); ?></td>
                                <?php endif; ?>
                                <td><?php echo e($item->produto->categoria->nome); ?></td>
                                <td>
                                    <?php if(!$item->produto->unidadeQuebrada()): ?>
                                    <?php echo e(__estoque($item->quantidade)); ?>

                                    <?php else: ?>
                                    <?php echo e(__estoque($item->quantidade)); ?>

                                    <?php endif; ?>
                                </td>
                                <td><?php echo e(__moeda($item->valor_compra)); ?></td>
                                <td><?php echo e(__moeda($item->produto->valor_venda)); ?></td>
                                <td><?php echo e(__moeda(($item->valor_compra) * ($item->quantidade))); ?></td>
                                <td><?php echo e(__moeda(($item->produto->valor_venda) * ($item->quantidade))); ?></td>
                                <td>
                                    <a href="<?php echo e(route('produtos.movimentacao', $item->produto->id)); ?>" type="btn" class="btn btn-primary btn-sm">
                                        <i class="bx bx-list-ul"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="m-3">
            <h5>Total de produtos</h5>
            <h6 id="total_custo">Custo: <?php echo e(__moeda($somaEstoque['compra'])); ?></h6>
            <h6 id="total_venda">Venda: <?php echo e(__moeda($somaEstoque['venda'])); ?></h6>
            <a target="_blank" class="btn btn-info" href="<?php echo e(route('estoque.create')); ?>">Apontamento
                manual</a>
            <a target="_blank" class="btn btn-primary" href="<?php echo e(route('estoque.listaApontamento')); ?>">Listar
                alterações</a>
            <a type="button" class="btn btn-danger" href="">Zerar estoque completo</a>
        </div>
        <div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('default.layout', ['title' => 'Estoque'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home2/wesl4494/public_html/padariacasadopao.com/resources/views/estoque/index.blade.php ENDPATH**/ ?>