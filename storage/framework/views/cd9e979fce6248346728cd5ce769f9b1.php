<?php $__env->startSection('content'); ?>
<?php
    $fmtDate = function ($value) {
        if (empty($value)) return '-';
        try { return \Carbon\Carbon::parse($value)->format('d/m/Y'); } catch (\Throwable $e) { return (string) $value; }
    };
    $fmtMoney = fn ($value) => 'R$ ' . number_format((float) $value, 2, ',', '.');
?>
<div class="page-content">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h4 class="mb-0">Dossiê do Funcionário</h4>
            <small class="text-muted">Visão consolidada de ficha, dependentes, documentos, ocorrências, férias, folha e timeline do colaborador.</small>
        </div>
        <div class="d-flex gap-2">
            <a href="<?php echo e(route('funcionarios.index')); ?>" class="btn btn-light border">Funcionários</a>
            <a href="<?php echo e(route('rh.dashboard')); ?>" class="btn btn-secondary">Voltar RH</a>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <div class="row g-3 align-items-center">
                <div class="col-lg-8">
                    <div class="d-flex flex-column gap-2">
                        <div>
                            <span class="badge bg-primary-subtle text-primary border">Status do dossiê: <?php echo e(strtoupper($dossie->status ?? 'ATIVO')); ?></span>
                            <span class="badge bg-light text-dark border">Atualizado em <?php echo e(!empty($dossie->ultima_atualizacao_em) ? \Carbon\Carbon::parse($dossie->ultima_atualizacao_em)->format('d/m/Y H:i') : now()->format('d/m/Y H:i')); ?></span>
                        </div>
                        <h5 class="mb-0"><?php echo e($funcionario->nome); ?></h5>
                        <div class="row g-2 text-muted small">
                            <div class="col-md-3"><strong>CPF:</strong> <?php echo e($funcionario->cpf ?? '-'); ?></div>
                            <div class="col-md-3"><strong>Função:</strong> <?php echo e($funcionario->funcao ?? '-'); ?></div>
                            <div class="col-md-3"><strong>Salário:</strong> <?php echo e($fmtMoney($funcionario->salario ?? 0)); ?></div>
                            <div class="col-md-3"><strong>E-mail:</strong> <?php echo e($funcionario->email ?? '-'); ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="border rounded p-2 h-100"><small class="text-muted d-block">Documentos</small><strong><?php echo e($stats['documentos']); ?></strong></div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-2 h-100"><small class="text-muted d-block">Vencidos</small><strong><?php echo e($stats['documentos_vencidos']); ?></strong></div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-2 h-100"><small class="text-muted d-block">Movimentações</small><strong><?php echo e($stats['movimentacoes']); ?></strong></div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-2 h-100"><small class="text-muted d-block">Holerites</small><strong><?php echo e($stats['holerites']); ?></strong></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-xl-8">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Timeline do dossiê</h6>
                    <span class="text-muted small"><?php echo e($timeline->count()); ?> eventos</span>
                </div>
                <div class="card-body">
                    <?php $__empty_1 = true; $__currentLoopData = $timeline; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="border-start border-3 ps-3 mb-3">
                            <div class="d-flex justify-content-between flex-wrap gap-2">
                                <div>
                                    <div class="fw-semibold"><?php echo e($item['titulo']); ?></div>
                                    <div class="small text-muted"><?php echo e($fmtDate($item['data'])); ?> • <?php echo e(strtoupper($item['categoria'])); ?> • origem: <?php echo e($item['origem']); ?></div>
                                </div>
                                <?php if(!empty($item['can_delete_evento']) || !empty($item['can_delete_documento'])): ?>
                                    <div class="d-flex align-items-start gap-2">
                                        <?php if(!empty($item['can_delete_evento']) && !empty($item['evento_id'])): ?>
                                            <form action="<?php echo e(route('rh.dossie.eventos.destroy', [$funcionario->id, $item['evento_id']])); ?>" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir este evento da timeline do dossiê?')">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('DELETE'); ?>
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Excluir evento">🗑️</button>
                                            </form>
                                        <?php endif; ?>
                                        <?php if(!empty($item['can_delete_documento']) && !empty($item['documento_id'])): ?>
                                            <form action="<?php echo e(route('rh.dossie.documentos.destroy', [$funcionario->id, $item['documento_id']])); ?>" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir este documento do dossiê?')">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('DELETE'); ?>
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Excluir documento">🗑️</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="mt-1"><?php echo e($item['descricao'] ?: 'Sem descrição complementar.'); ?></div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="alert alert-light border mb-0">Ainda não há eventos consolidados no dossiê.</div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent"><h6 class="mb-0">Ficha admissional</h6></div>
                <div class="card-body">
                    <?php if($ficha): ?>
                        <div class="row g-3 small">
                            <div class="col-md-3"><strong>Admissão:</strong><br><?php echo e($fmtDate($ficha->data_admissao)); ?></div>
                            <div class="col-md-3"><strong>Data nascimento:</strong><br><?php echo e($fmtDate($ficha->data_nascimento)); ?></div>
                            <div class="col-md-3"><strong>PIS:</strong><br><?php echo e($ficha->pis_numero ?? '-'); ?></div>
                            <div class="col-md-3"><strong>CTPS:</strong><br><?php echo e(($ficha->ctps_numero ?? '-') . ' / ' . ($ficha->ctps_serie ?? '-')); ?></div>
                            <div class="col-md-3"><strong>CNH:</strong><br><?php echo e($ficha->cnh_numero ?? '-'); ?></div>
                            <div class="col-md-3"><strong>Validade CNH:</strong><br><?php echo e($fmtDate($ficha->cnh_validade)); ?></div>
                            <div class="col-md-3"><strong>Banco:</strong><br><?php echo e($ficha->banco ?? '-'); ?></div>
                            <div class="col-md-3"><strong>Agência:</strong><br><?php echo e($ficha->agencia ?? '-'); ?></div>
                            <div class="col-md-6"><strong>Naturalidade:</strong><br><?php echo e(trim(($ficha->naturalidade ?? '-') . ' / ' . ($ficha->uf_naturalidade ?? '-'))); ?></div>
                            <div class="col-md-6"><strong>Observações:</strong><br><?php echo e($ficha->observacoes ?? '-'); ?></div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-light border mb-0">Sem ficha admissional vinculada.</div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent"><h6 class="mb-0">Documentos do colaborador</h6></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead><tr><th>Nome</th><th>Tipo</th><th>Categoria</th><th>Validade</th><th>Arquivo</th><th>Ações</th></tr></thead>
                            <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $documentos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $doc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td>
                                        <div class="fw-semibold"><?php echo e($doc->nome); ?></div>
                                        <div class="small text-muted"><?php echo e($doc->observacao ?? '-'); ?></div>
                                    </td>
                                    <td><?php echo e($doc->tipo); ?></td>
                                    <td><?php echo e($doc->categoria ?? '-'); ?></td>
                                    <td>
                                        <?php echo e($fmtDate($doc->validade)); ?>

                                        <?php if(!empty($doc->validade) && \Carbon\Carbon::parse($doc->validade)->startOfDay()->lt(now()->startOfDay())): ?>
                                            <span class="badge bg-danger ms-1">Vencido</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if(!empty($doc->arquivo)): ?>
                                            <a href="<?php echo e(route('rh.dossie.documentos.download', [$funcionario->id, $doc->id])); ?>" class="btn btn-sm btn-outline-primary">Baixar</a>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <form action="<?php echo e(route('rh.dossie.documentos.destroy', [$funcionario->id, $doc->id])); ?>" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir este documento do dossiê?')">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Excluir documento">🗑️</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr><td colspan="6" class="text-center text-muted">Nenhum documento cadastrado.</td></tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-transparent"><h6 class="mb-0">Movimentações</h6></div>
                        <div class="card-body table-responsive">
                            <table class="table table-sm table-striped">
                                <thead><tr><th>Data</th><th>Tipo</th><th>Resumo</th></tr></thead>
                                <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $movimentacoes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><?php echo e($fmtDate($item->data_movimentacao)); ?></td>
                                        <td><?php echo e(\App\Models\RHMovimentacao::tipos()[$item->tipo] ?? ucfirst($item->tipo)); ?></td>
                                        <td><?php echo e($item->descricao); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr><td colspan="3" class="text-center text-muted">Sem movimentações.</td></tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-transparent"><h6 class="mb-0">Dependentes</h6></div>
                        <div class="card-body table-responsive">
                            <table class="table table-sm table-striped">
                                <thead><tr><th>Nome</th><th>Parentesco</th><th>Nascimento</th></tr></thead>
                                <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $dependentes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dep): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><?php echo e($dep->nome); ?></td>
                                        <td><?php echo e($dep->parentesco ?? '-'); ?></td>
                                        <td><?php echo e($fmtDate($dep->data_nascimento)); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr><td colspan="3" class="text-center text-muted">Sem dependentes cadastrados.</td></tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-transparent"><h6 class="mb-0">Férias</h6></div>
                        <div class="card-body table-responsive">
                            <table class="table table-sm table-striped">
                                <thead><tr><th>Período</th><th>Gozo</th><th>Status</th></tr></thead>
                                <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $ferias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><?php echo e($fmtDate($item->periodo_aquisitivo_inicio)); ?> até <?php echo e($fmtDate($item->periodo_aquisitivo_fim)); ?></td>
                                        <td><?php echo e($fmtDate($item->data_inicio)); ?> até <?php echo e($fmtDate($item->data_fim)); ?></td>
                                        <td><?php echo e($item->status); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr><td colspan="3" class="text-center text-muted">Sem férias registradas.</td></tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-transparent"><h6 class="mb-0">Folha / holerites</h6></div>
                        <div class="card-body table-responsive">
                            <table class="table table-sm table-striped">
                                <thead><tr><th>Competência</th><th>Valor</th><th>Pagamento</th></tr></thead>
                                <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $holerites; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><?php echo e(str_pad((string) $item->mes, 2, '0', STR_PAD_LEFT)); ?>/<?php echo e($item->ano); ?></td>
                                        <td><?php echo e($fmtMoney($item->valor_final)); ?></td>
                                        <td><?php echo e($item->forma_pagamento); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr><td colspan="3" class="text-center text-muted">Sem apurações de folha.</td></tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent"><h6 class="mb-0">Anexar documento ao dossiê</h6></div>
                <div class="card-body">
                    <form method="POST" action="<?php echo e(route('rh.dossie.documentos.store', $funcionario->id)); ?>" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>
                        <div class="mb-2">
                            <label class="form-label">Nome do documento</label>
                            <input type="text" name="nome" class="form-control" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Tipo</label>
                            <input type="text" name="tipo" class="form-control" placeholder="Ex.: ASO, RG, Contrato" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Categoria</label>
                            <select name="categoria" class="form-control">
                                <?php $__currentLoopData = $categoriasDocumento; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($key); ?>"><?php echo e($label); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Validade</label>
                            <input type="date" name="validade" class="form-control">
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Arquivo</label>
                            <input type="file" name="arquivo" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Observação</label>
                            <textarea name="observacao" class="form-control" rows="3"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Anexar documento</button>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent"><h6 class="mb-0">Registrar evento manual</h6></div>
                <div class="card-body">
                    <form method="POST" action="<?php echo e(route('rh.dossie.eventos.store', $funcionario->id)); ?>">
                        <?php echo csrf_field(); ?>
                        <div class="mb-2">
                            <label class="form-label">Categoria</label>
                            <select name="categoria" class="form-control" required>
                                <?php $__currentLoopData = $categoriasEvento; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($key); ?>"><?php echo e($label); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Título</label>
                            <input type="text" name="titulo" class="form-control" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Data do evento</label>
                            <input type="date" name="data_evento" class="form-control" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Descrição</label>
                            <textarea name="descricao" class="form-control" rows="4"></textarea>
                        </div>
                        <div class="form-check mb-3">
                            <input type="hidden" name="visibilidade_portal" value="0">
                            <input class="form-check-input" type="checkbox" value="1" name="visibilidade_portal" id="visibilidade_portal">
                            <label class="form-check-label" for="visibilidade_portal">Disponibilizar no portal do funcionário</label>
                        </div>
                        <button type="submit" class="btn btn-dark w-100">Registrar evento</button>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent"><h6 class="mb-0">Ocorrências e desligamentos</h6></div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="small text-muted mb-2">Faltas / ocorrências</div>
                        <?php $__empty_1 = true; $__currentLoopData = $faltas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <div class="border rounded p-2 mb-2">
                                <div class="fw-semibold"><?php echo e($fmtDate($item->data_referencia)); ?> • <?php echo e($item->tipo); ?></div>
                                <div class="small"><?php echo e($item->descricao ?? 'Sem descrição.'); ?></div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="text-muted small">Sem faltas registradas.</div>
                        <?php endif; ?>
                    </div>
                    <div>
                        <div class="small text-muted mb-2">Desligamentos</div>
                        <?php $__empty_1 = true; $__currentLoopData = $desligamentos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <div class="border rounded p-2 mb-2">
                                <div class="fw-semibold"><?php echo e($fmtDate($item->data_desligamento)); ?> • <?php echo e($item->tipo); ?></div>
                                <div class="small"><?php echo e($item->motivo); ?></div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="text-muted small">Sem desligamentos registrados.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('default.layout',['title' => 'RH - Dossiê do Funcionário'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home2/wesl4494/public_html/padariacasadopao.com/resources/views/rh/dossie/show.blade.php ENDPATH**/ ?>