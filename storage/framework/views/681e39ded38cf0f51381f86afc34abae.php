<div class="modal fade" id="modal-pag_multi_pdv" aria-modal="true" role="dialog" style="overflow:scroll;" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="color:blue">Pagamento Múltiplo R$: <strong class="total-venda-modal"><?php if(isset($item)): ?> <?php echo e(__moeda($item->valor_total)); ?><?php endif; ?></strong></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="row">
                    <div class="col-md-3">
                        <?php echo Form::select('tipo_pagamento_row', 'Tipo de Pagamento', ['' => 'Selecione'] + App\Models\Venda::tiposPagamento())->attrs([
                        'class' => 'form-select',
                        ]); ?>

                    </div>
                    <div class="col-md-2">
                        <?php echo Form::tel('valor_integral_row', 'Valor')->attrs(['class' => 'moeda']); ?>

                    </div>
                    <div class="col-md-2">
                        <?php echo Form::date('data_vencimento_row', 'Vencimento')->attrs(['class' => ''])->value(date('Y-m-d')); ?>

                    </div>
                    <div class="col-md-4" >
                        <?php echo Form::text('obs_row', 'Observação')->attrs(['class' => '']); ?>

                    </div>
                    <div class="col-md-1">
                        <br>
                        <button type="button" class="btn btn-info btn-add-payment" ><i class="bx bx-plus"></i></button>
                    </div>
                    <div class="table-responsive">
                        <table class="table mb-0 table-striped mt-2 table-payment">
                            <thead>
                                <tr>
                                    <th>Tipo de Pagamento</th>
                                    <th>Vencimento</th>
                                    <th>Valor</th>
                                    <th>Observações</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>

                                <?php if(isset($item) && $item != null && $item->fatura): ?>

                                <?php $__currentLoopData = $item->fatura; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fatura): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td>
                                        <input readonly type="sel" name="tipo_pagamento_row[]" class="form-control"
                                        value="<?php echo e($fatura->forma_pagamento); ?>">
                                    </td>
                                    <td>
                                        <input readonly type="date" name="vencimento_parcela_row[]" class="form-control"
                                        value="<?php echo e($fatura->vencimento); ?>">
                                    </td>
                                    <td>
                                        <input readonly type="text" name="valor_integral_row[]"
                                        class="form-control valor_integral"
                                        value="<?php echo e(__moeda($fatura->valor)); ?>">
                                    </td>
                                    <td>
                                        <input readonly type="text" name="obs_row[]" class="form-control"
                                        value="<?php echo e($fatura->obs_row); ?>">
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-danger btn-delete-row">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php endif; ?>

                            </tbody>
                            <tfoot>
                                <tr>
                                    <td>Soma pagamento</td>
                                    <?php if(isset($data)): ?>
                                    <td class="sum-payment">R$ <?php echo e(__moeda($data->valor_total)); ?></td>
                                    <?php else: ?>
                                    <td class="sum-payment">R$ 0,00</td>
                                    <?php endif; ?>
                                </tr>
                            </tfoot>
                        </table>
                        <div class="mt-3">
                            <h6 style="color: rgb(218, 19, 19); size:25px" class="mt-2">Diferença: <strong class="sum-restante"></strong></h6>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button data-bs-dismiss="modal" id="btn-pag_row" type="button" disabled class="btn btn-primary">OK</button>
            </div>
        </div>
    </div>
</div>
</div>

<?php /**PATH /home2/wesl4494/public_html/padariacasadopao.com/resources/views/modals/frontBox/_pag_multi_pdv.blade.php ENDPATH**/ ?>