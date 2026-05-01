<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="mb-1">RBAC Profissional do RH</h3>
            <small class="text-muted">Empresa <?php echo e($empresaId ?: 'global'); ?></small>
        </div>
        <form method="POST" action="<?php echo e(route('rh.acl.sync')); ?>">
            <?php echo csrf_field(); ?>
            <button class="btn btn-primary">Sincronizar papéis padrão</button>
        </form>
    </div>

    <div class="row g-3">
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header">Papéis e permissões</div>
                <div class="card-body">
                    <?php $__currentLoopData = $papeis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $papel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="border rounded p-2 mb-2">
                            <div class="fw-bold"><?php echo e($papel->nome); ?> <?php if($papel->is_admin): ?><span class="badge bg-dark">admin</span><?php endif; ?></div>
                            <div class="small text-muted mb-2"><?php echo e($papel->descricao); ?></div>
                            <div class="d-flex flex-wrap gap-1">
                                <?php $__currentLoopData = $papel->permissoes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $perm): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <span class="badge bg-info text-dark"><?php echo e($perm->codigo); ?></span>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header">Atribuir papel ao usuário</div>
                <div class="card-body">
                    <form method="POST" action="<?php echo e(route('rh.acl.assign')); ?>" class="row g-2 mb-4">
                        <?php echo csrf_field(); ?>
                        <div class="col-md-5">
                            <label class="form-label">Usuário</label>
                            <select name="usuario_id" class="form-select" required>
                                <?php $__currentLoopData = $usuarios; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $usuario): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($usuario->id); ?>"><?php echo e($usuario->nome); ?> (#<?php echo e($usuario->id); ?>)</option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Papel</label>
                            <select name="papel_id" class="form-select" required>
                                <?php $__currentLoopData = $papeis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $papel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($papel->id); ?>"><?php echo e($papel->nome); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button class="btn btn-success w-100">Atribuir</button>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-sm table-striped">
                            <thead><tr><th>Usuário</th><th>Papéis ativos</th></tr></thead>
                            <tbody>
                            <?php $__currentLoopData = $usuarios; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $usuario): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><?php echo e($usuario->nome); ?></td>
                                    <td>
                                        <?php $__empty_1 = true; $__currentLoopData = ($assignments[$usuario->id] ?? collect()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $assignment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                            <span class="badge bg-secondary">papel #<?php echo e($assignment->papel_id); ?></span>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                            <span class="text-muted">Sem papel atribuído</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('default.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home2/wesl4494/public_html/padariacasadopao.com/resources/views/rh/acl/index.blade.php ENDPATH**/ ?>