<div class="modal fade" id="modal-produto" aria-modal="true" role="dialog" style="overflow:scroll;" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Novo Produto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body .md-produto">
                <?php echo $__env->make('produtos._forms', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            </div>

        </div>
    </div>
</div>
<?php /**PATH /home2/wesl4494/public_html/padariacasadopao.com/resources/views/modals/_produto.blade.php ENDPATH**/ ?>