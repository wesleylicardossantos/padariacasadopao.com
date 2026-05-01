<div class="modal fade" id="modal-soma_detalhada" aria-modal="true" role="dialog" style="overflow:scroll;" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Soma detalhada</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body row">
                <?php $__currentLoopData = $somaTiposPagamento; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
				<?php if($s > 0): ?>
				<h4 class="center-align"><?php echo e(App\Models\VendaCaixa::getTipoPagamento($key)); ?> = <strong class="red-text">R$ <?php echo e(__moeda($s)); ?></strong></h4>
				<?php endif; ?>
				<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </div>
</div>

<?php /**PATH /home2/wesl4494/public_html/padariacasadopao.com/resources/views/modals/frontBox/_soma_detalhada.blade.php ENDPATH**/ ?>