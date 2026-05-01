<div class="modal fade" id="modal-fluxo_diario" aria-modal="true" role="dialog" style="overflow:scroll;" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Fluxo Diário</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <?php if($abertura != null): ?>

            <div class="modal-body row">
                <div class="col-12">
                    <h6>Abertura de Caixa: <strong class="" style="color: blue"><?php echo e(__moeda($abertura->valor)); ?></strong></h6>
                </div>
                <hr>
                <div class="col-12">
                    <?php $__currentLoopData = $sangrias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <h6>Sangria: R$<strong style="color: red"><?php echo e(__moeda($item->valor)); ?></strong> - <?php echo e(__data_pt($item->created_at)); ?></h6>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <hr>
                <div class="col-12">
                    <?php $__currentLoopData = $suprimentos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <h6>Suprimentos: <strong style="color: green"><?php echo e(isset($item) ? $item->valor : '0,00'); ?></strong></h6>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <hr>
                <div class="table-responsive">
                    <h4>Vendas</h4>
                    <table class="table mb-0 table-striped">
                        <thead>
                            <tr>
                                <th>Hórario</th>
                                <th>Valor</th>
                                <th>Tipo de Pagamento</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $vendas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td><?php echo e($item->created_at); ?></td>
                                <td><?php echo e(__moeda($item->valor_total)); ?></td>
                                <td><?php echo e($item->getTipoPagamento($item->tipo_pagamento)); ?></td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button data-bs-dismiss="modal" type="button" class="btn btn-primary px-5">OK</button>
            </div>
            <?php else: ?>
            <div>
                <div class="modal-body">
                    <h5>Abrir caixa</h5>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php /**PATH /home2/wesl4494/public_html/padariacasadopao.com/resources/views/modals/frontBox/_fluxo_diario.blade.php ENDPATH**/ ?>