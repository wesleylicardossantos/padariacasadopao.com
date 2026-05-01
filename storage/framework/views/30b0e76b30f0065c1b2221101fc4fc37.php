<?php $__env->startSection('content'); ?>
<div class="page-content">
    <div class="card">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <div>
                    <h6 class="mb-0 text-uppercase">Holerites da competência</h6>
                    <small class="text-muted">Competência <?php echo e(str_pad($mes, 2, '0', STR_PAD_LEFT)); ?>/<?php echo e($ano); ?></small>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="<?php echo e(route('apuracaoMensal.index', ['mes_competencia' => $mes, 'ano_competencia' => $ano])); ?>" class="btn btn-light">
                        <i class="bx bx-arrow-back"></i> Voltar
                    </a>
                    <a href="<?php echo e(route('apuracaoMensal.holerites_competencia.zip', ['mes_competencia' => $mes, 'ano_competencia' => $ano])); ?>" class="btn btn-danger">
                        <i class="bx bx-download"></i> Baixar ZIP
                    </a>
                    <form method="POST" action="<?php echo e(route('apuracaoMensal.holerites_competencia.email')); ?>">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="mes_competencia" value="<?php echo e($mes); ?>">
                        <input type="hidden" name="ano_competencia" value="<?php echo e($ano); ?>">
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-envelope"></i> Enviar em fila
                        </button>
                    </form>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card border shadow-none mb-0">
                        <div class="card-body">
                            <div class="text-muted">Total de holerites</div>
                            <h4 class="mb-0"><?php echo e($data->count()); ?></h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border shadow-none mb-0">
                        <div class="card-body">
                            <div class="text-muted">Último lote</div>
                            <h4 class="mb-0"><?php echo e(optional($lotes->first())->id ? '#' . $lotes->first()->id : '--'); ?></h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border shadow-none mb-0">
                        <div class="card-body">
                            <div class="text-muted">Enviados no último lote</div>
                            <h4 class="mb-0 text-success"><?php echo e((int) optional($lotes->first())->enviados); ?></h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border shadow-none mb-0">
                        <div class="card-body">
                            <div class="text-muted">Falhas / sem e-mail</div>
                            <h4 class="mb-0 text-danger"><?php echo e((int) optional($lotes->first())->falhas + (int) optional($lotes->first())->sem_email); ?></h4>
                        </div>
                    </div>
                </div>
            </div>

            <div id="painel-lotes-wrapper">
                <?php echo $__env->make('apuracao_mensal.partials.painel_lotes', ['lotes' => $lotes, 'mes' => $mes, 'ano' => $ano], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            </div>

            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Funcionário</th>
                            <th>Mês/Ano</th>
                            <th>Valor final</th>
                            <th>E-mail</th>
                            <th>Portal</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($item->funcionario->nome ?? 'Funcionário'); ?></td>
                                <td><?php echo e(str_pad($mes, 2, '0', STR_PAD_LEFT)); ?>/<?php echo e($ano); ?></td>
                                <td><?php echo e(__moeda($item->valor_final)); ?></td>
                                <td><?php echo e($item->funcionario->email ?: 'Não informado'); ?></td>
                                <td>
                                    <div class="d-flex flex-wrap gap-1">
                                        <form method="POST" action="<?php echo e(route('rh.portal_externo.enviar_acesso', $item->funcionario_id)); ?>">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="canal" value="whatsapp">
                                            <button type="submit" class="btn btn-sm btn-success">
                                                <i class="bx bxl-whatsapp"></i> WhatsApp
                                            </button>
                                        </form>
                                        <form method="POST" action="<?php echo e(route('rh.portal_externo.enviar_acesso', $item->funcionario_id)); ?>">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="canal" value="email">
                                            <button type="submit" class="btn btn-sm btn-outline-primary">
                                                <i class="bx bx-envelope"></i> E-mail
                                            </button>
                                        </form>
                                    </div>
                                </td>
                                <td>
                                    <a target="_blank" href="<?php echo e(route('rh.holerite.show', ['id' => $item->funcionario_id, 'mes' => $mes, 'ano' => $ano])); ?>" class="btn btn-sm btn-danger">
                                        <i class="bx bxs-file-pdf"></i> PDF
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="6" class="text-center">Nenhuma apuração encontrada para essa competência.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
<script>
(function () {
    const url = <?php echo json_encode(route('apuracaoMensal.holerites_competencia.painel', ['mes_competencia' => $mes, 'ano_competencia' => $ano])) ?>;
    const wrapper = document.getElementById('painel-lotes-wrapper');
    if (!wrapper) return;

    let timer = null;
    let running = false;

    async function refreshPanel() {
        if (running) return;
        running = true;
        try {
            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            });
            if (!response.ok) return;
            const data = await response.json();
            if (typeof data.html === 'string') {
                wrapper.innerHTML = data.html;
            }
            if (!data.active && timer) {
                clearInterval(timer);
                timer = null;
            }
        } catch (e) {
            console.error('Falha ao atualizar painel de lotes.', e);
        } finally {
            running = false;
        }
    }

    if (wrapper.textContent.includes('Atualização automática ativa')) {
        timer = setInterval(refreshPanel, 10000);
    }
})();
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('default.layout',['title' => 'Holerites da competência'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home2/wesl4494/public_html/padariacasadopao.com/resources/views/apuracao_mensal/holerites.blade.php ENDPATH**/ ?>