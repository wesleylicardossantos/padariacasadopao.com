<?php $__env->startSection('content'); ?>
<?php
    $money = fn($v) => 'R$ ' . number_format((float) ($v ?? 0), 2, ',', '.');
    $percent = fn($v) => number_format((float) ($v ?? 0), 2, ',', '.') . '%';
    $kpis = $kpis ?? [];
    $rh = $rh ?? [];
    $billing = $billing ?? [];
    $alerts = array_values(array_unique(array_filter($alerts ?? [])));
    $lucro = (float) ($kpis['lucro'] ?? 0);
    $margem = (float) ($kpis['margem'] ?? 0);
?>

<div class="erp-saas-page">
    <div class="erp-saas-header">
        <div>
            <h2 class="erp-saas-title">Dashboard SaaS Executivo</h2>
            <p class="erp-saas-subtitle">Visão consolidada com dados reais do tenant, RH, financeiro e operação.</p>
        </div>
        <div class="text-end">
            <div class="small text-muted">Empresa <?php echo e($empresaId ?? '-'); ?> · <?php echo e(str_pad((string)($mes ?? date('m')), 2, '0', STR_PAD_LEFT)); ?>/<?php echo e($ano ?? date('Y')); ?></div>
            <div class="small text-muted">Atualizado em <?php echo e($updatedAt ?? now()->format('d/m/Y H:i:s')); ?></div>
            <div class="mt-2"><a href="javascript:history.back()" class="btn btn-sm btn-outline-secondary">Voltar</a></div>
        </div>
    </div>

    <?php if(!empty($alerts)): ?>
        <div class="erp-saas-alert p-3 mb-4">
            <strong>Atenções executivas:</strong>
            <ul class="mb-0 mt-2">
                <?php $__currentLoopData = $alerts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alert): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($alert); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="row g-3 mb-4">
        <div class="col-12 col-md-6 col-xl-3"><div class="erp-saas-kpi"><div class="card-body"><div class="erp-saas-label">Receita</div><div class="erp-saas-value"><?php echo e($money($kpis['receita'] ?? 0)); ?></div><div class="erp-saas-note">Vendas/financeiro do período</div></div></div></div>
        <div class="col-12 col-md-6 col-xl-3"><div class="erp-saas-kpi"><div class="card-body"><div class="erp-saas-label">Custo RH</div><div class="erp-saas-value"><?php echo e($money($kpis['rh'] ?? 0)); ?></div><div class="erp-saas-note">Folha e encargos apurados</div></div></div></div>
        <div class="col-12 col-md-6 col-xl-3"><div class="erp-saas-kpi"><div class="card-body"><div class="erp-saas-label">Lucro operacional</div><div class="erp-saas-value <?php echo e($lucro < 0 ? 'text-danger' : 'text-success'); ?>"><?php echo e($money($lucro)); ?></div><div class="erp-saas-note">Receita menos custo RH</div></div></div></div>
        <div class="col-12 col-md-6 col-xl-3"><div class="erp-saas-kpi"><div class="card-body"><div class="erp-saas-label">Margem</div><div class="erp-saas-value <?php echo e($margem < 5 ? 'text-danger' : 'text-success'); ?>"><?php echo e($percent($margem)); ?></div><div class="erp-saas-note">Margem operacional</div></div></div></div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-md-6 col-xl-3"><div class="erp-saas-kpi"><div class="card-body"><div class="erp-saas-label">Funcionários ativos</div><div class="erp-saas-value"><?php echo e($rh['funcionariosAtivos'] ?? 0); ?></div></div></div></div>
        <div class="col-12 col-md-6 col-xl-3"><div class="erp-saas-kpi"><div class="card-body"><div class="erp-saas-label">Recebimentos</div><div class="erp-saas-value"><?php echo e($money($kpis['recebimentos'] ?? 0)); ?></div></div></div></div>
        <div class="col-12 col-md-6 col-xl-3"><div class="erp-saas-kpi"><div class="card-body"><div class="erp-saas-label">Usuários</div><div class="erp-saas-value"><?php echo e($kpis['usuarios'] ?? 0); ?></div></div></div></div>
        <div class="col-12 col-md-6 col-xl-3"><div class="erp-saas-kpi"><div class="card-body"><div class="erp-saas-label">Clientes</div><div class="erp-saas-value"><?php echo e($kpis['clientes'] ?? 0); ?></div></div></div></div>
    </div>

    <div class="row g-3">
        <div class="col-12 col-xl-6"><div class="erp-saas-card"><div class="card-body"><h5 class="erp-saas-section-title">RH Executivo</h5><div class="erp-saas-table table-responsive"><table class="table table-sm mb-0"><tbody>
            <tr><td>Folha atual</td><td class="text-end fw-bold"><?php echo e($money($rh['rhAtual'] ?? $kpis['rh'] ?? 0)); ?></td></tr>
            <tr><td>Peso da folha</td><td class="text-end fw-bold"><?php echo e($percent($rh['pesoFolhaAtual'] ?? 0)); ?></td></tr>
            <tr><td>Margem RH</td><td class="text-end fw-bold"><?php echo e($percent($rh['margemAtual'] ?? $margem)); ?></td></tr>
            <tr><td>Lucro após RH</td><td class="text-end fw-bold <?php echo e($lucro < 0 ? 'text-danger' : 'text-success'); ?>"><?php echo e($money($lucro)); ?></td></tr>
        </tbody></table></div></div></div></div>
        <div class="col-12 col-xl-6"><div class="erp-saas-card"><div class="card-body"><h5 class="erp-saas-section-title">SaaS / Assinatura</h5><div class="erp-saas-table table-responsive"><table class="table table-sm mb-0"><tbody>
            <tr><td>Status</td><td class="text-end fw-bold"><span class="erp-saas-status <?php echo e(($billing['status'] ?? 'ACTIVE') === 'INACTIVE' ? 'erp-saas-status-warn' : 'erp-saas-status-ok'); ?>"><?php echo e($billing['status'] ?? 'ACTIVE'); ?></span></td></tr>
            <tr><td>Cobranças aprovadas</td><td class="text-end fw-bold"><?php echo e($billing['aprovadas'] ?? 0); ?></td></tr>
            <tr><td>Cobranças pendentes</td><td class="text-end fw-bold"><?php echo e($billing['pendentes'] ?? 0); ?></td></tr>
            <tr><td>Saúde do tenant</td><td class="text-end fw-bold"><?php echo e($tenantHealth['status'] ?? 'HEALTHY'); ?></td></tr>
        </tbody></table></div></div></div></div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('default.layout', ['title' => 'Dashboard SaaS Executivo'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home2/wesl4494/public_html/padariacasadopao.com/resources/views/enterprise/saas/executive.blade.php ENDPATH**/ ?>