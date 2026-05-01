<?php $__env->startSection('content'); ?>
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="col">
                    <h6 class="mb-0 text-uppercase">Lista de Vendas de Frente de Caixa</h6>

                    <?php echo Form::open()->fill(request()->all())->get(); ?>

                    <div class="row mt-3">
                        <div class="col-md-2">
                            <?php echo Form::date('start_date', 'Data inicial')->value(date('Y-m-d')); ?>

                        </div>
                        <div class="col-md-2">
                            <?php echo Form::date('end_date', 'Data final')->value(date('Y-m-d')); ?>

                        </div>
                        <div class="col-md-2">
                            <?php echo Form::tel('valor', 'Valor')->attrs(['class' => 'moeda']); ?>

                        </div>
                        <div class="col-md-3">
                            <?php echo Form::select('estado', 'Estado', [
                            'novo' => 'Novas',
                            'rejeitado' => 'Rejeitadas',
                            'cancelado' => 'Canceladas',
                            'aprovado' => 'Aprovadas',
                            '' => 'Todos',
                            ])->attrs(['class' => 'select2']); ?>

                        </div>
                        <div class="col-md-2">
                            <?php echo Form::tel('numero_nfe', 'Número NFCe')->attrs(['class' => '']); ?>

                        </div>
                        <div class="col-md-3 text-left mt-1">
                            <br>
                            <button class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
                            <a id="clear-filter" class="btn btn-danger" href="<?php echo e(route('frenteCaixa.list')); ?>">Limpar</a>
                        </div>
                    </div>
                    <?php echo Form::close(); ?>

                </div>
            </div>

            <?php if($config->arquivo != null): ?>
            <div class="col-12">
                <button class="btn btn-dark btn-consulta-status">
                    Consultar Status Sefaz
                </button>
            </div>
            <?php endif; ?>

            <?php if($contigencia != null): ?>
            <h3 class="text-danger mt-3">Contigência ativada</h3>
            <span class="text-danger">Tipo: <?php echo e($contigencia->tipo); ?></span><br>
            <span class="text-danger">Data de ínicio: <?php echo e(__data_pt($contigencia->created_at)); ?></span>
            <?php endif; ?>
            <hr>
            <div class="row row-cols-auto g-3" style="margin-left: 60px">
                <div class="col">
                    <a class="btn btn-info px-3 radius-10" href="<?php echo e(route('frenteCaixa.index')); ?>">Frente de caixa</a>
                </div>
                <div class="col">
                    <a class="btn btn-primary px-3 radius-10" href="/relatorios/filtroVendaDiaria?start_date=<?php echo e(date('Y-m-d')); ?>&nr_resultados='">Baixar
                        relatório</a>
                </div>
                <div class="col">
                    <button class="btn btn-dark px-3 radius-10" data-bs-toggle="modal" data-bs-target="#modal-soma_detalhada">Soma detalhada</button>
                </div>
                <div class="col">
                    <a class="btn btn-success px-3 radius-10" href="<?php echo e(route('caixa.list')); ?>">Caixas fechados</a>
                </div>
            </div>
            <hr>
            <div class="table-responsive tbl-400">
                <table class="table mb-0 table-striped">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Data</th>
                            <th>Tipo de pagamento</th>
                            <th>Estado</th>
                            <th>Nº NFCe</th>
                            <th>Usuário</th>
                            <th>Valor</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($item->cliente->razao_social ?? 'Consumidor Final'); ?></td>
                            <td><?php echo e(__data_pt($item->created_at, 1)); ?></td>
                            <td><?php echo e($item->getTipoPagamento($item->tipo_pagamento)); ?></td>
                            <td>
                                <?php echo $item->estadoEmissao($item->estado_emissao); ?>

                                <?php if($item->contigencia): ?>
                                <br>
                                <span class="text-danger">contigência</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo e($item->numero_nfce ?? '0'); ?></td>
                            <td><?php echo e($item->usuario->nome); ?></td>
                            <td><?php echo e(__moeda($item->valor_total)); ?></td>

                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Ações</button>
                                    <ul class="dropdown-menu" style="z-index: 999">

                                        <?php if($item->estado_emissao != 'aprovado'): ?>
                                        <li>
                                            <a title="XML Temporário" target="_blank" href="<?php echo e(route('nfce.xml-temp', [$item->id])); ?>" class="dropdown-item">
                                                XML Temporário
                                            </a>
                                        </li>
                                        <?php endif; ?>

                                        <?php if($item->numero_nfce && $item->estado_emissao == 'aprovado'): ?>
                                        <li>
                                            <a title="CUPOM FISCAL" target="_blank" href="<?php echo e(route('nfce.imprimir', [$item->id])); ?>" class="dropdown-item">
                                                Imprimir
                                            </a>
                                        </li>
                                        <li>
                                            <a id="btn_consulta_<?php echo e($item->id); ?>" title="CONSULTAR NFCE" onclick="consultarNFCe('<?php echo e($item->id); ?>')" href="#!" class="dropdown-item">
                                                Consultar NFCe
                                            </a>
                                        </li>
                                        <li>
                                            <a title="BAIXAR XML" href="<?php echo e(route('nfce.baixar-xml', [$item->id])); ?>" class="dropdown-item">
                                                Baixar XML
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="<?php echo e(route('nfce.state-fiscal', $item->id)); ?>">Alterar estado fiscal</a>
                                        </li>
                                        <?php endif; ?>
                                        <li>
                                            <a title="CUPOM NÃO FISCAL" target="_blank" href="<?php echo e(route('frenteCaixa.imprimir-nao-fiscal', [$item->id])); ?>" class="dropdown-item">
                                                Cupom Não Fiscal
                                            </a>
                                        </li>
                                        <?php if(!$item->numero_nfce): ?>
                                        <li>
                                            <a title="GERAR NFCE" id="btn_envia_<?php echo e($item->id); ?>" class="dropdown-item" onclick='swal("Atenção!", "Deseja enviar esta venda para Sefaz?", "warning").then((sim) => {if(sim){ emitirNFCe(<?php echo e($item->id); ?>) }else{return false} })' href="#!">
                                                Gerar NFCe
                                            </a>
                                        </li>
                                        <?php endif; ?>
                                        <li>
                                            <a title="DETALHES" target="_blank" href="<?php echo e(route('nfce.show', [$item->id])); ?>" class="dropdown-item">
                                                Detalhes
                                            </a>
                                        </li>
                                        <?php if($item->rascunho): ?>
                                        <li>
                                            <a title="EDITAR RASCUNHO" href="/frenteCaixa/edit/<?php echo e($item->id); ?>" class="dropdown-item">
                                                Editar Rascunho
                                            </a>
                                        </li>

                                        <?php endif; ?>

                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>

                                        <li>
                                            <a class="dropdown-item text-warning" href="#" onclick="return fbAcaoMotivo('estornar', <?php echo e($item->id); ?>)">
                                                <i class="bx bx-undo"></i> Estornar
                                            </a>
                                        </li>

                                        <li>
                                            <?php if(is_adm()): ?>
                                            <a class="dropdown-item text-danger" href="#" onclick="return fbAcaoMotivo('force', <?php echo e($item->id); ?>)">
                                                <i class="bx bx-trash"></i> Excluir
                                            </a>
                                            <?php else: ?>
                                            <a class="dropdown-item text-danger" href="#" onclick="return fbAcaoMotivo('soft', <?php echo e($item->id); ?>)">
                                                <i class="bx bx-trash"></i> Excluir
                                            </a>
                                            <?php endif; ?>
                                        </li>

                                        <?php if(is_adm()): ?>
                                        <li>
                                            <a class="dropdown-item text-danger" href="#" onclick="return fbAcaoMotivo('force', <?php echo e($item->id); ?>)">
                                                <i class="bx bx-x-circle"></i> Excluir definitivo
                                            </a>
                                        </li>
                                        <?php endif; ?>
                                    </ul>
                                </div>

                                
                                <form id="fb_form_soft_<?php echo e($item->id); ?>" action="<?php echo e(route('frenteCaixa.destroy', $item->id)); ?>" method="POST" style="display:none;">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <input type="hidden" name="motivo" value="">
                                </form>
                                <form id="fb_form_force_<?php echo e($item->id); ?>" action="<?php echo e(route('frenteCaixa.forceDestroy', $item->id)); ?>" method="POST" style="display:none;">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <input type="hidden" name="motivo" value="">
                                </form>
                                <form id="fb_form_estornar_<?php echo e($item->id); ?>" action="<?php echo e(route('frenteCaixa.estornar', $item->id)); ?>" method="POST" style="display:none;">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="motivo" value="">
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="10" class="text-center">Nada encontrado</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php echo $data->appends(request()->all())->links(); ?>

        </div>
    </div>
</div>

<?php echo $__env->make('modals.frontBox._soma_detalhada', ['not_submit' => true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>


<?php $__env->stopSection(); ?>
<?php $__env->startSection('js'); ?>
<script type="text/javascript" src="/js/nfce.js"></script>

<script>
    function fbAcaoMotivo(acao, id) {
        let titulo = 'Confirmação';
        let texto = 'Informe o motivo para continuar:';

        if (acao === 'estornar') {
            titulo = 'Estornar venda';
        }
        if (acao === 'soft') {
            titulo = 'Excluir venda';
        }
        if (acao === 'force') {
            titulo = 'Excluir venda';
            texto = 'A venda será excluída mesmo com o caixa aberto. Informe o motivo:';
        }

        // SweetAlert (v1) com input customizado
        const input = document.createElement('input');
        input.className = 'swal-content__input';
        input.placeholder = 'Motivo (obrigatório)';

        return swal({
            title: titulo,
            text: texto,
            content: input,
            icon: 'warning',
            buttons: {
                cancel: 'Cancelar',
                confirm: {
                    text: 'Confirmar',
                    closeModal: false
                }
            }
        }).then(function (confirmou) {
            if (!confirmou) return null;

            const motivo = (input.value || '').trim();
            if (motivo.length < 3) {
                swal('Atenção', 'Informe um motivo com pelo menos 3 caracteres.', 'error');
                return null;
            }

            let formId = null;
            if (acao === 'soft') formId = 'fb_form_soft_' + id;
            if (acao === 'force') formId = 'fb_form_force_' + id;
            if (acao === 'estornar') formId = 'fb_form_estornar_' + id;

            const form = document.getElementById(formId);
            if (!form) {
                swal('Erro', 'Formulário não encontrado.', 'error');
                return null;
            }

            const hidden = form.querySelector('input[name="motivo"]');
            if (hidden) hidden.value = motivo;

            form.submit();
            return null;
        });
    }
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('default.layout', ['title' => 'Lista de Vendas'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home2/wesl4494/public_html/padariacasadopao.com/resources/views/frontBox/list.blade.php ENDPATH**/ ?>