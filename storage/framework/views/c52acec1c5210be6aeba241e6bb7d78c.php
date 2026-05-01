<div class="modal fade" id="modal-abrir_caixa" aria-modal="true" role="dialog" style="overflow:scroll;" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Abrir Caixa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <?php echo Form::open()
            ->post()
            ->route('caixa.store')
            ->multipart(); ?>

            <div class="modal-body">
                <?php echo __view_locais_select_pdv(); ?>

                <div class="col-md-12 mt-3">
                    <?php echo Form::tel('valor', 'Valor')->attrs(['class' => 'moeda']); ?>

                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary px-5 w-100">Abrir</button>
            </div>
            <?php echo Form::close(); ?>


        </div>
    </div>
</div>

<?php $__env->startSection('js'); ?>
<script src="/js/caixa.js"></script>
<?php $__env->stopSection(); ?>
<?php /**PATH /home2/wesl4494/public_html/padariacasadopao.com/resources/views/modals/_abrir_caixa.blade.php ENDPATH**/ ?>