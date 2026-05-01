<?php $__env->startSection('content'); ?>
<div class="page-content">
    <div class="card"><div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h5 class="mb-0"><?php echo e($documento->nome); ?></h5>
                <small class="text-muted"><?php echo e($documento->tipo); ?> · <?php echo e($documento->origem ?: 'manual'); ?></small>
            </div>
            <div class="d-flex gap-2">
                <?php if(!empty($documento->arquivo)): ?>
                <a class="btn btn-primary" target="_blank" href="<?php echo e(route('rh.documentos.download', $documento->id)); ?>">Abrir PDF</a>
                <?php endif; ?>
                <a class="btn btn-secondary" href="<?php echo e(route('rh.documentos.index')); ?>">Voltar</a>
            </div>
        </div>
        <div class="border rounded p-4 bg-white"><?php echo $documento->conteudo_html ?: nl2br(e($documento->conteudo_texto ?: 'Sem conteúdo renderizado.')); ?></div>
    </div></div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('default.layout',['title' => 'RH - Pré-visualização do Documento'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home2/wesl4494/public_html/padariacasadopao.com/resources/views/rh/documentos/preview.blade.php ENDPATH**/ ?>