<div class="modal fade" id="modal-lista_pre_venda" aria-modal="true" role="dialog" style="overflow:scroll;"
    tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Pré vendas Recebidas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body row">
                <div class="table-responsive">
                    <table class="table mb-0 table-striped">
                        <thead>
                            <tr>
                                <th>Vendedor</th>
                                <th>Valor</th>
                                <th>Data</th>
                                <th>Observação</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $preVendas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><?php echo e($item->vendedor->nome); ?></td>
                                    <td><?php echo e(__moeda($item->valor_total)); ?></td>
                                    <td><?php echo e(__data_pt($item->created_at)); ?></td>
                                    <td><?php echo e($item->observacao); ?></td>
                                    <td>
                                        
                                        <form method="get" action="<?php echo e(route('frenteCaixa.index')); ?>">
                                            <input type="hidden" value="<?php echo e($item->id); ?>" name="prevenda_id">
                                            <button class="btn btn-dark">Setar</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button data-bs-dismiss="modal" type="button" class="btn btn-primary px-5">OK</button>
            </div>
        </div>
    </div>
</div>
<?php /**PATH /home2/wesl4494/public_html/padariacasadopao.com/resources/views/modals/frontBox/_lista_pre_venda.blade.php ENDPATH**/ ?>