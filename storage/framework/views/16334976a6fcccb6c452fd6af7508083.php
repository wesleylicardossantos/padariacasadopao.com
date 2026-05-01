<?php $__env->startSection('content'); ?>
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                </div>
            </div>
            <div class="col">
                <?php if(isset($config)): ?>
                <input type="hidden" id="pass" value="<?php echo e($config->senha_remover); ?>">
                <?php endif; ?>
                <h6 class="mb-0 text-uppercase">Pesquisar vendas</h6>
                <?php echo Form::open()->fill(request()->all())
                ->get(); ?>

                <div class="row mt-3">
                    <div class="col-md-2">
                        <?php echo Form::select('tipo', 'Tipo de pesquisa',
                        [0 => 'Razão Social',
                        1 => 'Nome Fantasia',
                        ])
                        ->attrs(['class' => 'select2']); ?>

                    </div>
                    <div class="col-md-4">
                        <?php echo Form::select('cliente_id', 'Cliente'); ?>

                    </div>
                    <div class="col-md-2">
                        <?php echo Form::select('pesquisa_data', 'Pesquisa por data',
                        ['created_at' => 'Data Registro',
                        'data_entrega' => 'Data Entrega'])
                        ->attrs(['class' => 'select2']); ?>

                    </div>
                    <div class="col-md-2">
                        <?php echo Form::date('start_date', 'Data inicial'); ?>

                    </div>
                    <div class="col-md-2">
                        <?php echo Form::date('end_date', 'Data final'); ?>

                    </div>
                    <div class="col-md-2 mt-3">
                        <?php echo Form::select('estado_emissao', 'Estado',
                        ['' => 'Todas',
                        'rejeitado' => 'Rejeitadas',
                        'cancelado' => 'Canceladas',
                        'aprovado' => 'Aprovadas',
                        ])
                        ->attrs(['class' => 'select2']); ?>

                    </div>
                    <div class="col-md-2 mt-3">
                        <?php echo Form::tel('numero_nfe', 'Número NFe')->attrs(['class' => '']); ?>

                    </div>
                    <div class="col-md-2 mt-3">
                        <?php echo Form::date('data_emissao', 'Data emissão')->attrs(['class' => '']); ?>

                    </div>

                    <?php if(empresaComFilial()): ?>
                    <?php echo __view_locais_select_filtro("Local", isset($filial_id) ? $filial_id : ''); ?>

                    <?php endif; ?>

                    <div class="col-md-5 text-left mt-3">
                        <br>
                        <button class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
                        <a id="clear-filter" class="btn btn-danger" href="<?php echo e(route('vendas.index')); ?>"><i class="bx bx-eraser"></i> Limpar</a>
                    </div>
                </div>
                <?php echo Form::close(); ?>

                <hr />
                <h6>Lista de Vendas</h6>
                
                <div class="row">
                    <div class="col-12">
                        <a href="<?php echo e(route('vendas.create')); ?>" type="button" class="btn btn-success">
                            <i class="bx bx-plus"></i> Nova venda
                        </a>
                        <?php if($config->arquivo != null): ?>
                        <button class="btn btn-dark float-right btn-consulta-status">
                            Consultar Status Sefaz
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if($contigencia != null): ?>
                <h3 class="text-danger mt-3">Contigência ativada</h3>
                <span class="text-danger">Tipo: <?php echo e($contigencia->tipo); ?></span><br>
                <span class="text-danger">Data de ínicio: <?php echo e(__data_pt($contigencia->created_at)); ?></span>
                <?php endif; ?>
                <div class="card mt-3">
                    <div class="card-body">
                        <div class="table-responsive tbl-400">
                            <table class="table mb-0 table-striped">
                                <thead class="">
                                    <tr>
                                        <th>#</th>
                                        <th>Ações</th>
                                        <th>Cliente</th>
                                        <th>Data de Registro</th>
                                        <th>Tipo de Pagamento</th>
                                        <?php if(empresaComFilial()): ?>
                                        <th>Local</th>
                                        <?php endif; ?>
                                        <th>Estado</th>
                                        <th>NFe</th>
                                        <th>Usuário</th>
                                        <th>Valor Integral</th>
                                        <th>Desconto</th>
                                        <th>Acréscimo</th>
                                        <th>Ecommerce</th>
                                        <th>Valor Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__empty_1 = true; $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td>
                                            <input type="checkbox" value="<?php echo e($item->id); ?>" data-email="<?php echo e($item->cliente->email); ?>" class="checkbox" name="" data-status="<?php echo e($item->estado_emissao); ?>" data-numero_nfe="<?php echo e($item->numero_nfe); ?>">
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Ações</button>
                                                <ul class="dropdown-menu" style="z-index: 999">
                                                    <form action="<?php echo e(route('vendas.destroy', $item->id)); ?>" method="post" id="form-<?php echo e($item->id); ?>">
                                                        <?php echo method_field('delete'); ?>
                                                        <?php echo csrf_field(); ?>
                                                        <li>
                                                            <a class="dropdown-item" href="<?php echo e(route('vendas.edit', $item->id)); ?>">Editar</a>
                                                        </li>
                                                        <?php if($item->estado_emissao == 'novo' || $item->estado_emissao == 'rejeitado'): ?>
                                                        <li>
                                                            <button class="dropdown-item btn-delete">Apagar</button>
                                                        </li>
                                                        <?php endif; ?>
                                                        <li>
                                                            <a target="_blank" class="dropdown-item" href="<?php echo e(route('vendas.xml-temp', $item->id)); ?>">XML temporário</a>
                                                        </li>

                                                        <li>
                                                            <a class="dropdown-item" href="<?php echo e(route('nfe.state-fiscal', $item->id)); ?>">Alterar estado fiscal</a>
                                                        </li>

                                                        <li>
                                                            <a target="_blank" class="dropdown-item" href="<?php echo e(route('vendas.print', $item->id)); ?>">Imprimir</a>
                                                        </li>

                                                        <li>
                                                            <a class="dropdown-item" href="<?php echo e(route('vendas.show', $item->id)); ?>">Ver</a>
                                                        </li>

                                                        <li>
                                                            <a class="dropdown-item" href="<?php echo e(route('vendas.clone', $item->id)); ?>">Duplicar</a>
                                                        </li>
                                                    </form>
                                                </ul>
                                            </div>
                                        </td>
                                        <td><?php echo e($item->cliente->razao_social); ?></td>
                                        <td><?php echo e(__data_pt($item->created_at, 1)); ?></td>
                                        <td><?php echo e($item->getTipoPagamento()); ?></td>
                                        <?php if(empresaComFilial()): ?>
                                        <td>
                                            <span class="codigo" style="width: 150px;">
                                                <?php echo e($item->filial_id ? $item->filial->descricao : 'Matriz'); ?>

                                            </span>
                                        </td>
                                        <?php endif; ?>
                                        <td>
                                            <?php echo $item->estadoEmissao(); ?>

                                            <?php if($item->contigencia): ?>
                                            <br>
                                            <span class="text-danger">contigência</span>
                                            <?php endif; ?>

                                        </td>
                                        <td><?php echo e($item->numero_nfe > 0 ? $item->numero_nfe : '--'); ?></td>
                                        <td><?php echo e($item->usuario->nome); ?></td>
                                        <td><?php echo e(__moeda($item->valor_total)); ?></td>
                                        <td><?php echo e(__moeda($item->desconto)); ?></td>
                                        <td><?php echo e(__moeda($item->acrescimo)); ?></td>
                                        <td>
                                            <?php if($item->pedido_nuvemshop_id > 0): ?>
                                            NUVEMSHOP
                                            <a href="<?php echo e(route('nuvemshop-pedidos.show', [$item->pedidoNuvemShop->pedido_id])); ?>" class="btn btn-link">ver pedido</a>
                                            <?php else: ?>
                                            NÃO
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo e(__moeda($item->valor_total - $item->desconto + $item->acrescimo)); ?></td>
                                    </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="14" class="text-center">Nada encontrado</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="row g-3 mt-2">
                            <div class="col-md-2">
                                <button id="btn-enviar" type="button" class="btn btn-info btn-action spinner-white spinner-right px-2" style="width: 100%" href="">Transmitir</button>
                            </div>
                            <div class="col-md-2">
                                <button id="btn-imprimir" class="btn btn-secondary btn-action spinner-white spinner-right" style="width: 100%">Imprimir</button>
                            </div>
                            <div class="col-md-2">
                                <button id="btn-consultar" type="button" class="btn btn-primary btn-action spinner-white spinner-right" style="width: 100%">Consultar</button>
                            </div>
                            <div class="col-md-2">
                                <button id="btn-cancelar" type="button" class="btn btn-danger btn-action spinner-white spinner-right" style="width: 100%">Cancelar</button>
                            </div>
                            <div class="col-md-2">
                                <button id="btn-corrigir" type="button" class="btn btn-warning spinner-white btn-action spinner-right" style="width: 100%" href="">CC-e</button>
                            </div>
                            <div class="col-md-2">
                                <button id="btn-inutilizar" type="button" class="btn btn-outline-secondary spinner-white spinner-right" style="width: 100%">Inutilizar NFe</button>
                            </div>
                            <div class="col-md-2">
                                <button type="button" data-href="<?php echo e(route('vendas.danfe-temp')); ?>" class="btn btn-dark spinner-white btn-action spinner-right px-1" style="width: 100%" id="btn-danfe-temp">Danfe Temporária</button>
                            </div>
                            <div class="col-md-2">
                                <button id="btn-enviar-email" type="button" data-bs-toggle="modal" data-bs-target="#modal-email" class="btn btn-primary spinner-white btn-action spinner-right" style="width: 100%">Enviar E-mail</button>
                            </div>

                            <div class="col-md-2">
                                <button id="btn-imprimir-cce" type="button" class="btn btn-warning spinner-white btn-action spinner-right" style="width: 100%">Imprimir CC-e</button>
                            </div>
                            <div class="col-md-2">
                                <button id="btn-imprimir-cancela" type="button" class="btn btn-danger spinner-white btn-action spinner-right" style="width: 100%">Imprimir Cancela</button>
                            </div>
                            <div class="col-md-2">
                                <button id="btn-baixar-xml" type="button" class="btn btn-success btn-action spinner-white spinner-right px-2" style="width: 100%">Baixar XML</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php if(isset($data->appends)): ?>
            <?php echo $data->appends(request()->all())->links(); ?>

            <?php endif; ?>
        </div>
    </div>
</div>
<div class="modal fade" id="modal-cancelar" aria-modal="true" role="dialog" style="overflow:scroll;" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cancelar NFe <strong class="text-danger numero_nfe"></strong></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="col-md-12">
                    <?php echo Form::text('motivo-cancela', 'Justificativa'); ?>

                </div>
            </div>
            <div class="modal-footer">
                <button id="btn-cancelar-send" type="button" class="btn btn-danger px-5">Cancelar</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="modal-corrigir" aria-modal="true" role="dialog" style="overflow:scroll;" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Corrigir NFe <strong class="text-warning numero_nfe"></strong></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="col-md-12">
                    <?php echo Form::text('motivo-corrige', 'Descrição da correção'); ?>

                </div>
            </div>
            <div class="modal-footer">
                <button id="btn-corrige-send" type="button" class="btn btn-warning px-5">Corrigir</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-inutilizar" aria-modal="true" role="dialog" style="overflow:scroll;" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">INUTILIZAÇÃO DE NÚMERO(s) DE NFe</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-3">
                        <?php echo Form::tel('numero_inicial', 'Nº inicial'); ?>

                    </div>
                    <div class="col-md-3">
                        <?php echo Form::tel('numero_final', 'Nº final'); ?>

                    </div>
                    <div class="col-md-12 mt-3">
                        <?php echo Form::text('motivo-inutiliza', 'Justificativa'); ?>

                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button id="btn-inutiliza-send" type="button" class="btn btn-primary px-5">Inutilizar</button>
            </div>
        </div>
    </div>
</div>

<?php echo $__env->make('modals._email', ['not_submit' => true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>


<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>

<script type="text/javascript" src="/js/nf.js"></script>
<script type="text/javascript" src="/js/vendas.js"></script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('default.layout',['title' => 'Vendas'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home2/wesl4494/public_html/padariacasadopao.com/resources/views/vendas/index.blade.php ENDPATH**/ ?>