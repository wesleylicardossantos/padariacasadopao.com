<?php $__env->startSection('content'); ?>
<?php
    $snapshot = $snapshot ?? [];
    $labelize = fn($label) => strtoupper(str_replace('_', ' ', (string) $label));
    $formatValue = function ($value) {
        if (is_bool($value)) return $value ? 'SIM' : 'NÃO';
        if (is_numeric($value)) return is_float($value + 0) ? number_format((float) $value, 2, ',', '.') : number_format((int) $value, 0, ',', '.');
        if (is_array($value)) return count($value) . ' registros';
        if (is_object($value)) return 'Objeto';
        return $value ?: '-';
    };
?>

<div class="erp-saas-page">
    <div class="erp-saas-header">
        <div>
            <h2 class="erp-saas-title">SaaS Scale Ops Center</h2>
            <p class="erp-saas-subtitle">Indicadores de escala e prontidão operacional do tenant.</p>
        </div>
        <div><a href="javascript:history.back()" class="btn btn-sm btn-outline-secondary">Voltar</a></div>
    </div>

    <div class="row g-3">
        <?php $__empty_1 = true; $__currentLoopData = $snapshot; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $label => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="col-12 col-md-6 col-xl-4">
                <div class="erp-saas-kpi">
                    <div class="card-body">
                        <div class="erp-saas-label"><?php echo e($labelize($label)); ?></div>
                        <div class="erp-saas-value"><?php echo e($formatValue($value)); ?></div>
                        <?php if(is_array($value) && !empty($value)): ?>
                            <div class="erp-saas-table mt-3">
                                <table class="table table-sm mb-0">
                                    <tbody>
                                        <?php $__currentLoopData = array_slice($value, 0, 5, true); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $itemLabel => $itemValue): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <tr>
                                                <td><?php echo e(str_replace('_', ' ', (string) $itemLabel)); ?></td>
                                                <td class="text-end fw-bold"><?php echo e(is_scalar($itemValue) ? $formatValue($itemValue) : (is_array($itemValue) ? count($itemValue) . ' itens' : '-')); ?></td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="col-12"><div class="alert alert-info mb-0">Nenhum indicador de escala disponível para este tenant.</div></div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('default.layout', ['title' => 'SaaS Scale Ops Center'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home2/wesl4494/public_html/padariacasadopao.com/resources/views/enterprise/saas/scale.blade.php ENDPATH**/ ?>