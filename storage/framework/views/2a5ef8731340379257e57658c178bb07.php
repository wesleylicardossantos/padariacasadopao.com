<div class="modal fade" id="modal-suprimento_caixa" aria-modal="true" role="dialog" style="overflow:scroll;" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Suprimento Caixa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <?php echo Form::open()
                ->post()
                ->route('suprimentoCaixa.store'); ?>

            <div class="modal-body row">
                <div class="col-md-6">
                    <?php echo Form::tel('valor', 'Valor')->attrs(['class' => 'moeda']); ?>

                </div>
                <div class="col-md-12 mt-3">
                    <?php echo Form::text('observacao', 'Observação')->attrs(['class' => 'form-control']); ?>

                </div>
            </div>
            <div class="modal-footer">
                <button data-bs-dismiss="modal" type="submit" class="btn btn-primary px-5">Salvar</button>
            </div>
            <?php echo Form::close(); ?>

        </div>
    </div>
</div>

<?php /**PATH /home2/wesl4494/public_html/padariacasadopao.com/resources/views/modals/frontBox/_suprimento_caixa.blade.php ENDPATH**/ ?>