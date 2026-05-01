<?php $__env->startSection('content'); ?>
<div class="page-content">
    <div class="card"><div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <h5 class="mb-0">Templates Jurídicos RH</h5>
                <small class="text-muted">Base padrão BR para contratos, rescisões e documentos internos.</small>
            </div>
            <div class="d-flex gap-2">
                <a class="btn btn-outline-secondary" href="<?php echo e(route('rh.documentos.index')); ?>">Voltar</a>
                <a class="btn btn-primary" href="<?php echo e(route('rh.documentos.templates.create')); ?>">Novo template</a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead><tr><th>Nome</th><th>Categoria</th><th>Tipo</th><th>IA</th><th>Versão</th><th>Status</th><th>Ações</th></tr></thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td>
                                <div class="fw-bold"><?php echo e($item->nome); ?></div>
                                <div class="small text-muted"><?php echo e($item->descricao); ?></div>
                            </td>
                            <td><?php echo e($item->categoria); ?></td>
                            <td><?php echo e($item->tipo_documento); ?></td>
                            <td><?php echo e($item->usa_ia ? 'Sim' : 'Não'); ?></td>
                            <td><?php echo e($item->versao); ?></td>
                            <td><?php echo e($item->ativo ? 'Ativo' : 'Inativo'); ?></td>
                            <td class="d-flex gap-1 flex-wrap">
                                <a class="btn btn-sm btn-outline-primary" href="<?php echo e(route('rh.documentos.templates.edit', $item->id)); ?>"><i class="bx bx-edit"></i></a>
                                <form method="POST" action="<?php echo e(route('rh.documentos.templates.destroy', $item->id)); ?>" onsubmit="return confirm('Remover template?')">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button class="btn btn-sm btn-danger"><i class="bx bx-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="7" class="text-center text-muted">Nenhum template encontrado.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php echo e($data->links()); ?>

    </div></div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('default.layout',['title' => 'RH - Templates Jurídicos'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home2/wesl4494/public_html/padariacasadopao.com/resources/views/rh/documentos/templates/index.blade.php ENDPATH**/ ?>