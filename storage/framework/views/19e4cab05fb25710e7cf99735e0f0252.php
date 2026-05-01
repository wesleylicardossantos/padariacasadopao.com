<?php $__env->startSection('content'); ?>
<?php
    $mes = (int) ($mes ?? date('m'));
    $ano = (int) ($ano ?? date('Y'));
    $coberturaFolha = (float) ($coberturaFolha ?? 0);
    $contasReceber = (float) ($contasReceber ?? 0);
    $contasReceberLiquidas = (float) ($contasReceberLiquidas ?? 0);
    $contasPagar = (float) ($contasPagar ?? 0);
    $contasPagas = (float) ($contasPagas ?? 0);
    $custosRh = $custosRh ?? [];
    $folhaTotal = (float) ($folhaTotal ?? 0);
    $resultadoAposFolha = (float) ($resultadoAposFolha ?? 0);
    $resultadoCaixa = (float) ($resultadoCaixa ?? 0);
    $alertasFinanceiros = $alertasFinanceiros ?? [];
    $pesoFolha = (float) ($pesoFolha ?? 0);
    $pesoFolhaCaixa = (float) ($pesoFolhaCaixa ?? 0);
    $capitalComprometido = (float) ($capitalComprometido ?? 0);
    $categoriasPagar = $categoriasPagar ?? [];
    $categoriasReceber = $categoriasReceber ?? [];
    $serieFinanceira = $serieFinanceira ?? [];
    $resumo = collect($resumo ?? []);
?>
<style>
.ca-page{background:#f6f9fc;padding:18px;border-radius:24px}.ca-card{background:#fff;border:1px solid #e6edf7;border-radius:20px;box-shadow:0 10px 30px rgba(15,23,42,.05)}
.ca-kpi .label{font-size:.74rem;text-transform:uppercase;letter-spacing:.04em;color:#6b7a90;font-weight:700}.ca-kpi .value{font-size:1.45rem;font-weight:800;color:#0f172a}.ca-kpi .hint{font-size:.82rem;color:#6b7a90}
.ca-pill{display:inline-flex;align-items:center;padding:.38rem .75rem;border-radius:999px;font-size:.78rem;font-weight:700}.ok{background:#ecfdf3;color:#067647}.warn{background:#fff7e8;color:#b54708}.bad{background:#fef3f2;color:#b42318}
.soft-table thead th{font-size:.78rem;text-transform:uppercase;color:#6b7a90;border-bottom-color:#e6edf7}.soft-table td,.soft-table th{padding:.85rem 1rem;vertical-align:middle}
.alert-chip{border-radius:14px;padding:.75rem .9rem;border:1px solid #fde68a;background:#fffaf0;color:#92400e}
</style>

<div class="page-content ca-page">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
        <div>
            <h4 class="mb-1">Resumo integrado RH + Financeiro</h4>
            <div class="text-muted">Competência <?php echo e(str_pad($mes,2,'0',STR_PAD_LEFT)); ?>/<?php echo e($ano); ?> · visão unificada de folha, caixa e despesas.</div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="/rh/folha?mes=<?php echo e($mes); ?>&ano=<?php echo e($ano); ?>" class="btn btn-outline-secondary">Folha</a>
            <a href="/rh/ia-decisao?mes=<?php echo e($mes); ?>&ano=<?php echo e($ano); ?>" class="btn btn-primary">IA de decisão</a>
        </div>
    </div>

    <form method="GET" action="/rh/folha/resumo-financeiro" class="card ca-card mb-3">
        <div class="card-body p-4">
            <div class="row g-3 align-items-end">
                <div class="col-md-2"><label class="form-label">Mês</label><input type="number" class="form-control" name="mes" min="1" max="12" value="<?php echo e($mes); ?>"></div>
                <div class="col-md-2"><label class="form-label">Ano</label><input type="number" class="form-control" name="ano" value="<?php echo e($ano); ?>"></div>
                <div class="col-md-3"><button class="btn btn-primary w-100">Atualizar análise</button></div>
                <div class="col-md-5 text-md-end"><span class="ca-pill <?php echo e($coberturaFolha >= 1.5 ? 'ok' : ($coberturaFolha >= 1 ? 'warn' : 'bad')); ?>">Cobertura da folha: <?php echo e(number_format((float)$coberturaFolha,2,',','.')); ?>x</span></div>
            </div>
        </div>
    </form>

    <div class="row g-3 mb-3">
        <div class="col-lg-3 col-md-6"><div class="card ca-card ca-kpi"><div class="card-body"><div class="label">Receita prevista</div><div class="value">R$ <?php echo e(number_format((float)$contasReceber,2,',','.')); ?></div><div class="hint">Recebida: R$ <?php echo e(number_format((float)$contasReceberLiquidas,2,',','.')); ?></div></div></div></div>
        <div class="col-lg-3 col-md-6"><div class="card ca-card ca-kpi"><div class="card-body"><div class="label">Despesas previstas</div><div class="value">R$ <?php echo e(number_format((float)$contasPagar,2,',','.')); ?></div><div class="hint">Pagas: R$ <?php echo e(number_format((float)$contasPagas,2,',','.')); ?></div></div></div></div>
        <div class="col-lg-3 col-md-6"><div class="card ca-card ca-kpi"><div class="card-body"><div class="label">RH total</div><div class="value">R$ <?php echo e(number_format((float)($custosRh['total_rh'] ?? 0),2,',','.')); ?></div><div class="hint">Folha líquida: R$ <?php echo e(number_format((float)$folhaTotal,2,',','.')); ?></div></div></div></div>
        <div class="col-lg-3 col-md-6"><div class="card ca-card ca-kpi"><div class="card-body"><div class="label">Resultado após folha</div><div class="value">R$ <?php echo e(number_format((float)$resultadoAposFolha,2,',','.')); ?></div><div class="hint">Resultado caixa: R$ <?php echo e(number_format((float)$resultadoCaixa,2,',','.')); ?></div></div></div></div>
    </div>

    <?php if(!empty($alertasFinanceiros)): ?>
    <div class="row g-3 mb-3">
        <?php $__currentLoopData = $alertasFinanceiros; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alerta): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="col-lg-6"><div class="alert-chip"><?php echo e($alerta); ?></div></div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <?php endif; ?>

    <div class="row g-3 mb-3">
        <div class="col-lg-4"><div class="card ca-card ca-kpi"><div class="card-body"><div class="label">Peso da folha</div><div class="value"><?php echo e(number_format((float)$pesoFolha,2,',','.')); ?>%</div><div class="hint">Sobre receita prevista</div></div></div></div>
        <div class="col-lg-4"><div class="card ca-card ca-kpi"><div class="card-body"><div class="label">Peso da folha no caixa</div><div class="value"><?php echo e(number_format((float)$pesoFolhaCaixa,2,',','.')); ?>%</div><div class="hint">Sobre receita recebida</div></div></div></div>
        <div class="col-lg-4"><div class="card ca-card ca-kpi"><div class="card-body"><div class="label">Capital comprometido</div><div class="value"><?php echo e(number_format((float)$capitalComprometido,2,',','.')); ?>%</div><div class="hint">Despesas + RH / receita prevista</div></div></div></div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-lg-6">
            <div class="card ca-card h-100">
                <div class="card-header bg-transparent border-0 pt-4 px-4"><h5 class="mb-0">Composição do RH</h5></div>
                <div class="card-body pt-2 px-4 pb-4">
                    <div class="row g-3">
                        <div class="col-6"><small class="text-muted">Salários</small><div class="fw-bold fs-5">R$ <?php echo e(number_format((float)($custosRh['salarios'] ?? 0),2,',','.')); ?></div></div>
                        <div class="col-6"><small class="text-muted">Eventos</small><div class="fw-bold fs-5">R$ <?php echo e(number_format((float)($custosRh['eventos'] ?? 0),2,',','.')); ?></div></div>
                        <div class="col-6"><small class="text-muted">Encargos</small><div class="fw-bold fs-5">R$ <?php echo e(number_format((float)($custosRh['encargos'] ?? 0),2,',','.')); ?></div></div>
                        <div class="col-6"><small class="text-muted">Benefícios</small><div class="fw-bold fs-5">R$ <?php echo e(number_format((float)($custosRh['beneficios'] ?? 0),2,',','.')); ?></div></div>
                        <div class="col-6"><small class="text-muted">Provisões</small><div class="fw-bold fs-5">R$ <?php echo e(number_format((float)($custosRh['provisoes'] ?? 0),2,',','.')); ?></div></div>
                        <div class="col-6"><small class="text-muted">FGTS estimado</small><div class="fw-bold fs-5">R$ <?php echo e(number_format((float)($custosRh['fgts'] ?? 0),2,',','.')); ?></div></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card ca-card h-100">
                <div class="card-header bg-transparent border-0 pt-4 px-4"><h5 class="mb-0">Categorias financeiras</h5></div>
                <div class="card-body pt-0 px-0 pb-2">
                    <div class="table-responsive">
                        <table class="table soft-table mb-0">
                            <thead><tr><th>Categoria</th><th>Valor</th><th>Qtd.</th></tr></thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $categoriasPagar; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr><td><?php echo e($item['categoria']); ?></td><td>R$ <?php echo e(number_format((float)$item['valor'],2,',','.')); ?></td><td><?php echo e($item['quantidade']); ?></td></tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr><td colspan="3" class="text-center py-4 text-muted">Sem dados de contas a pagar.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card ca-card mb-3">
        <div class="card-header bg-transparent border-0 pt-4 px-4"><h5 class="mb-0">Funcionários e impacto na folha</h5></div>
        <div class="card-body pt-0 px-0 pb-2">
            <div class="table-responsive">
                <table class="table soft-table mb-0">
                    <thead>
                        <tr>
                            <th>Funcionário</th>
                            <th>Base</th>
                            <th>Eventos</th>
                            <th>Descontos</th>
                            <th>INSS</th>
                            <th>IRRF</th>
                            <th>Líquido</th>
                            <th>Holerite</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $resumo; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $linha): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($linha['funcionario']->nome); ?></td>
                            <td>R$ <?php echo e(number_format((float)$linha['salario_base'],2,',','.')); ?></td>
                            <td>R$ <?php echo e(number_format((float)$linha['eventos'],2,',','.')); ?></td>
                            <td>R$ <?php echo e(number_format((float)$linha['descontos'],2,',','.')); ?></td>
                            <td>R$ <?php echo e(number_format((float)$linha['inss'],2,',','.')); ?></td>
                            <td>R$ <?php echo e(number_format((float)$linha['irrf'],2,',','.')); ?></td>
                            <td><strong>R$ <?php echo e(number_format((float)$linha['liquido'],2,',','.')); ?></strong></td>
                            <td><a href="/rh/holerite/<?php echo e($linha['funcionario']->id); ?>?mes=<?php echo e($mes); ?>&ano=<?php echo e($ano); ?>" target="_blank" class="btn btn-sm btn-primary">Holerite</a></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="8" class="text-center py-4 text-muted">Nenhum funcionário encontrado.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('default.layout',['title' => 'RH - Resumo Financeiro Integrado'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home2/wesl4494/public_html/padariacasadopao.com/resources/views/rh/folha/resumo_financeiro.blade.php ENDPATH**/ ?>