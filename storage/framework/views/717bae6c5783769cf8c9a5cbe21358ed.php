<?php $__env->startSection('content'); ?>
<style>.rh-card{border:1px solid #e9edf5;border-radius:14px;box-shadow:0 8px 24px rgba(15,23,42,.04)}</style>
<div class="page-content">
    <div class="card rh-card">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <div>
                    <h6 class="mb-0 text-uppercase">RH - Movimentações</h6>
                    <small class="text-muted">Promoções, reajustes, mudanças de cargo, demissões e ocorrências.</small>
                </div>
                <a href="<?php echo e(route('rh.movimentacoes.create')); ?>" class="btn btn-success"><i class="bx bx-plus"></i> Nova movimentação</a>
            </div>

            <?php if(!empty($semTabela)): ?>
                <div class="alert alert-danger">A estrutura RH V2 ainda não foi instalada. Execute o SQL do patch e recarregue.</div>
            <?php endif; ?>

            <form method="GET" action="<?php echo e(route('rh.movimentacoes.index')); ?>">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Pesquisar por nome</label>
                        <input type="text" class="form-control" name="nome" value="<?php echo e($nome ?? ''); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tipo</label>
                        <select class="form-select" name="tipo">
                            <option value="">Todos</option>
                            <?php $__currentLoopData = \App\Models\RHMovimentacao::tipos(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($key); ?>" <?php if(($tipo ?? '') == $key): ?> selected <?php endif; ?>><?php echo e($label); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary w-100">Filtrar</button>
                    </div>
                </div>
            </form>

            <hr>

            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Funcionário</th>
                            <th>Tipo</th>
                            <th>Descrição</th>
                            <th>Cargo</th>
                            <th>Valores</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($semTabela)): ?>
                        <tr><td colspan="7" class="text-center">Estrutura RH V2 pendente.</td></tr>
                        <?php else: ?>
                            <?php $__empty_1 = true; $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e(\Carbon\Carbon::parse($item->data_movimentacao)->format('d/m/Y')); ?></td>
                                <td><?php echo e(optional($item->funcionario)->nome); ?></td>
                                <td><?php echo e(\App\Models\RHMovimentacao::tipos()[$item->tipo] ?? ucfirst($item->tipo)); ?></td>
                                <td><?php echo e($item->descricao); ?></td>
                                <td><?php echo e($item->cargo_anterior ?: '-'); ?><?php echo e($item->cargo_novo ? ' → '.$item->cargo_novo : ''); ?></td>
                                <td>
                                    <?php if($item->valor_anterior !== null || $item->valor_novo !== null): ?>
                                        R$ <?php echo e(number_format((float)$item->valor_anterior,2,',','.')); ?> → R$ <?php echo e(number_format((float)$item->valor_novo,2,',','.')); ?>

                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td><a href="<?php echo e(route('rh.movimentacoes.edit', $item->id)); ?>" class="btn btn-sm btn-warning">Editar</a></td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="7" class="text-center">Nenhuma movimentação encontrada.</td></tr>
                            <?php endif; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if(empty($semTabela)): ?>
            <?php echo $data->appends(request()->all())->links(); ?>

            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('default.layout',['title' => 'RH - Movimentações'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home2/wesl4494/public_html/padariacasadopao.com/resources/views/rh/movimentacoes/index.blade.php ENDPATH**/ ?>