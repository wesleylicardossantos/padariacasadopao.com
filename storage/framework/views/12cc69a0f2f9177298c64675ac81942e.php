<?php $__env->startSection('content'); ?>
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="row">
                    <h6 class="mb-0 text-uppercase">Detalhes da Venda</h6>
                </div>
            </div>
            <div class="mt-5">
                <h6>Venda Nº: <strong style="color: rgb(20, 60, 241)"><?php echo e($item->id); ?> </strong></h6>
                <h6>Chave NFCe: <?php echo e($item->chave != "" ? $item->chave : '--'); ?> </h6>
                <h5>Estado:
                    <?php if($item->estado_emissao == 'novo'): ?>
                    <span class="btn bn-xl btn-inline btn-primary">Novo</span>
                    <?php elseif($item->estado_emissao == 'aprovado'): ?>
                    <span class="btn btn-xl btn-inline btn-success">Aprovado</span>
                    <?php elseif($item->estado_emissao == 'cancelado'): ?>
                    <span class="btn btn-xl btn-inline btn-danger">Cancelado</span>
                    <?php else: ?>
                    <span class="btn btn-xl btn-inline btn-warning">Rejeitado</span>
                    <?php endif; ?>
                </h5>
                <?php if($adm): ?>
                <a href="<?php echo e(route('nfce.estadoFiscal', $item->id)); ?>" class="btn btn-danger">
                    <i class="bx bx-error"></i>
                    Alterar estado fiscal da venda
                </a>
                <?php endif; ?>
            </div>
            <hr>
            <div class="table-responsive">
                <h5>Itens da venda</h5>
                <?php
                $somaItens = 0;
                ?>
                <table class="table mb-0 table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Produto</th>
                            <th>Quantidade</th>
                            <th>Valor</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $item->itens; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($i->id); ?></td>
                            <td><?php echo e($i->produto->nome); ?></td>
                            <td><?php echo e($i->quantidade); ?></td>
                            <td><?php echo e(__moeda($i->valor)); ?></td>
                            <td><?php echo e(__moeda($i->valor * $i->quantidade)); ?></td>
                        </tr>
                        <?php
                        $somaItens += $i->valor * $i->quantidade
                        ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="5" class="text-center">Nada encontrado</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <h5 class="mt-3">Soma total: <strong style="color: rgb(20, 60, 241)"><?php echo e(__moeda($somaItens)); ?></strong></h5>
            <hr>
            <div class="col-5">
                <?php if($item->NFcNumero && $item->estado == 'APROVADO'): ?>
                <a target="_blank" href="<?php echo e(route('nfce.imprimir', $item->id)); ?>" class="btn btn-success">
                    <i class="bx bx-printer"></i>
                    Imprimir fiscal
                </a>
                <?php endif; ?>
                <a style="margin-left: 5px;" target="_blank" href="<?php echo e(route('nfce.imprimirNaoFiscal', $item->id)); ?>" class="btn btn-info">
                    <i class="bx bx-printer"></i>
                    Imprimir não fiscal
                </a>
                <?php if($item->isComprovanteAssessor()): ?>
                <a style="margin-left: 5px;" target="_blank" href="<?php echo e(route('nfce.imprimirComprovanteAssessor', $item->id)); ?>" class="btn btn-primary">
                    <i class="bx bx-printer"></i>
                    Imprimir comprovante assessor
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('default.layout', ['title' => 'Detalhes de Vendas'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home2/wesl4494/public_html/padariacasadopao.com/resources/views/frontBox/show.blade.php ENDPATH**/ ?>