<div class="modal fade" id="modal-fornecedor" aria-modal="true" role="dialog" style="overflow:scroll;" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Novo Fornecedor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="form-fornecedor-modal">
                <div class="modal-body">
                    <?php echo $__env->make('fornecedores._forms', ['not_submit' => true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary px-5" id="btn-store-fornecedor">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript" src="/js/fornecedor.js"></script>
<?php /**PATH /home2/wesl4494/public_html/padariacasadopao.com/resources/views/modals/_fornecedor.blade.php ENDPATH**/ ?>