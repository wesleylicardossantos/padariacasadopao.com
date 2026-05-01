<?php $__env->startSection('content'); ?>
<div class="page-content">
    <?php if(!$hasTable): ?>
    <div class="alert alert-warning">A tabela <strong>rh_documentos</strong> ainda não existe. Execute o SQL do módulo RH.</div>
    <?php endif; ?>

    <div class="row g-3 mb-3">
        <div class="col-md-4"><div class="card"><div class="card-body"><small class="text-muted d-block">Templates jurídicos ativos</small><h4 class="mb-0"><?php echo e($templatesAtivos); ?></h4></div></div></div>
        <div class="col-md-4"><div class="card"><div class="card-body"><small class="text-muted d-block">Documentos gerados com IA</small><h4 class="mb-0"><?php echo e($documentosIa); ?></h4></div></div></div>
        <div class="col-md-4"><div class="card"><div class="card-body"><small class="text-muted d-block">Integração com dossiê</small><h4 class="mb-0">Automática</h4></div></div></div>
    </div>

    <div class="card mb-3"><div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <h5 class="mb-0">Documentos Inteligentes RH</h5>
                <small class="text-muted">Geração com IA, templates jurídicos BR e salvamento automático no dossiê.</small>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a class="btn btn-outline-primary" href="<?php echo e(route('rh.documentos.templates.index')); ?>">Templates Jurídicos</a>
                <a class="btn btn-success" href="<?php echo e(route('rh.documentos.create')); ?>">Gerar documento</a>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-7">
                <div class="border rounded p-3 h-100">
                    <h6 class="mb-2">Templates prontos</h6>
                    <div class="row g-2">
                        <?php $__empty_1 = true; $__currentLoopData = $templates->take(8); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $template): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <div class="col-md-6">
                                <div class="border rounded p-2 h-100">
                                    <small class="text-muted text-uppercase"><?php echo e($template->categoria); ?></small>
                                    <div class="fw-bold"><?php echo e($template->nome); ?></div>
                                    <div class="small text-muted"><?php echo e($template->descricao); ?></div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="col-12"><div class="text-muted">Nenhum template jurídico disponível ainda.</div></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="border rounded p-3 h-100 bg-light">
                    <h6 class="mb-2">Fluxo de uso</h6>
                    <ol class="mb-0 ps-3">
                        <li>Selecione o funcionário e o template jurídico.</li>
                        <li>Ative a IA para revisar ou complementar o documento.</li>
                        <li>Gere o PDF A4 automaticamente.</li>
                        <li>O sistema salva o documento no dossiê do funcionário.</li>
                    </ol>
                </div>
            </div>
        </div>
    </div></div>

    <div class="card"><div class="card-body p-4">
        <?php echo Form::open()->fill(request()->all())->get(); ?>

        <div class="row g-3 align-items-end mb-3">
            <div class="col-md-6"><?php echo Form::select('funcionario_id', 'Funcionário', ['' => 'Todos'] + $funcionarios->pluck('nome','id')->all())->attrs(['class' => 'select2']); ?></div>
            <div class="col-md-6"><button class="btn btn-primary">Filtrar</button> <a class="btn btn-danger" href="<?php echo e(route('rh.documentos.index')); ?>">Limpar</a></div>
        </div>
        <?php echo Form::close(); ?>


        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead><tr><th>Funcionário</th><th>Tipo</th><th>Nome</th><th>Origem</th><th>Status</th><th>Validade</th><th>Arquivo</th><th>Ações</th></tr></thead>
                <tbody>
                <?php if($hasTable): ?>
                    <?php $__empty_1 = true; $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e($item->funcionario_nome); ?></td>
                        <td><?php echo e($item->tipo); ?></td>
                        <td><?php echo e($item->nome); ?></td>
                        <td><?php echo e($item->origem ?: '-'); ?></td>
                        <td><?php echo e($item->status ?: '-'); ?></td>
                        <td><?php echo e(!empty($item->validade) ? \Carbon\Carbon::parse($item->validade)->format('d/m/Y') : '-'); ?></td>
                        <td>
                            <?php if(!empty($item->arquivo)): ?>
                            <a href="<?php echo e(route('rh.documentos.download', $item->id)); ?>" target="_blank" class="btn btn-sm btn-primary">PDF</a>
                            <?php else: ?>
                            <span class="text-muted">Sem arquivo</span>
                            <?php endif; ?>
                        </td>
                        <td class="d-flex gap-1 flex-wrap">
                            <?php if(!empty($item->conteudo_html)): ?>
                            <a class="btn btn-outline-secondary btn-sm" href="<?php echo e(route('rh.documentos.preview', $item->id)); ?>"><i class="bx bx-show"></i></a>
                            <?php endif; ?>
                            <form method="POST" action="<?php echo e(route('rh.documentos.destroy', $item->id)); ?>" onsubmit="return confirm('Remover documento?')">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button class="btn btn-danger btn-sm"><i class="bx bx-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="8" class="text-center text-muted">Sem documentos.</td></tr>
                    <?php endif; ?>
                <?php else: ?>
                    <tr><td colspan="8" class="text-center text-muted">Módulo ainda não instalado no banco.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if($hasTable && method_exists($data, 'links')): ?>
        <?php echo e($data->appends(request()->all())->links()); ?>

        <?php endif; ?>
    </div></div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('default.layout',['title' => 'RH - Documentos Inteligentes'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home2/wesl4494/public_html/padariacasadopao.com/resources/views/rh/documentos/index.blade.php ENDPATH**/ ?>