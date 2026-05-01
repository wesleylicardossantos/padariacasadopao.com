<?php $__env->startSection('content'); ?>
<div class="page-content">
    <div class="card border-top border-0 border-4 border-primary">
        <div class="m-3">
            <div class="container <?php if(env('ANIMACAO')): ?> animate__animated <?php endif; ?> animate__bounce">
                <div class="col-lg-12">
                    <input type="hidden" name="id" value="<?php echo e(isset($cliente) ? $cliente->id : 0); ?>">
                    <div class="card-header">
                        <h3 class="card-title">Importando XML</h3>
                    </div>
                    <?php echo Form::open()
                    ->post()
                    ->route('compraFiscal.store')
                    ->multipart(); ?>

                    <div class="row">
                        <div class="card col-xl-12 m-3">
                            <h5 class="center-align mt-2">Nota Fiscal: <strong class="text-primary"><?php echo e($dadosNf['nNf']); ?></strong></h5>
                            <h5 class="center-align">Data de emissão: <strong class="text-primary"><?php echo e(\Carbon\Carbon::parse($dadosNf['data_emissao'])->format('d/m/Y H:i')); ?></strong></h5>
                            <h5 class="center-align">Chave: <strong class="text-primary"><?php echo e($dadosNf['chave']); ?></strong></h5>
                            <?php if(count($dadosAtualizados) > 0): ?>
                            <div class="row">
                                <div class="col-xl-12">
                                    <h6 class="text-success">Dados atualizados do fornecedor</h5>
                                        <?php $__currentLoopData = $dadosAtualizados; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <p class="red-text"><?php echo e($d); ?></p>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            </div>
                            <?php endif; ?>
                            <div class="row">
                                <div class="col s8">
                                    <h5>Fornecedor: <strong><?php echo e($dadosEmitente['razaoSocial']); ?></strong></h5>
                                    <h5>Nome Fantasia: <strong><?php echo e($dadosEmitente['nomeFantasia']); ?></strong></h5>
                                </div>
                                <div class="col s4">
                                    <h5>CNPJ: <strong><?php echo e($dadosEmitente['cnpj']); ?></strong></h5>
                                    <h5>IE: <strong><?php echo e($dadosEmitente['ie']); ?></strong></h5>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col s8">
                                    <h5>Logradouro: <strong><?php echo e($dadosEmitente['logradouro']); ?></strong></h5>
                                    <h5>Numero: <strong><?php echo e($dadosEmitente['numero']); ?></strong></h5>
                                    <h5>Bairro: <strong><?php echo e($dadosEmitente['bairro']); ?></strong></h5>
                                </div>
                                <div class="col s4">
                                    <h5>CEP: <strong><?php echo e($dadosEmitente['cep']); ?></strong></h5>
                                    <h5>Fone: <strong><?php echo e($dadosEmitente['fone']); ?></strong></h5>
                                </div>
                            </div>
                            <input type="hidden" name="pathXml" id="pathXml" value="<?php echo e($pathXml); ?>">
                            <input type="hidden" name="fornecedor_id" id="idFornecedor" value="<?php echo e($idFornecedor); ?>">
                            <input type="hidden" name="nNf" id="nNf" value="<?php echo e($dadosNf['nNf']); ?>">
                            <input type="hidden" name="data_emissao" id="data_emissao" value="<?php echo e($dadosNf['data_emissao']); ?>">
                            <input type="hidden" name="vDesc" id="vDesc" value="<?php echo e($dadosNf['vDesc']); ?>">
                            <input type="hidden" id="prodSemRegistro" value="<?php echo e($dadosNf['contSemRegistro']); ?>">
                            <input type="hidden" name="chave" id="chave" value="<?php echo e($dadosNf['chave']); ?>">
                        </div>

                        <div class="col-xl-12">
                            <div class="row">
                                
                                <div class="col-xl-12 m-3">
                                    <p class="text-danger">* Produtos em vermelho não possui cadastro no sistema.</p>
                                    <p> Produtos sem registro no sistema: <strong class="prodSemRegistro">
                                            <?php echo e($dadosNf['contSemRegistro']); ?></strong></p>

                                    <h5>Itens da NFe: <strong class="text-info"><?php echo e(sizeof($itens)); ?></strong></h5>
                                    <div id="kt_datatable" class="table-responsive">
                                        <table class="table mb-0 table-striped" style="">
                                            <thead class="datatable-head">
                                                <tr class="" style="left: 0px;">
                                                    <th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 70px;">#</span></th>
                                                    <th data-field="Country" class="datatable-cell datatable-cell-sort"><span style="width: 180px;">Produto</span></th>
                                                    <th data-field="ShipDate" class="datatable-cell datatable-cell-sort"><span style="width: 80px;">NCM</span></th>
                                                    <th data-field="CompanyName" class="datatable-cell datatable-cell-sort"><span style="width: 80px;">CFOP</span></th>
                                                    <th data-field="Status" class="datatable-cell datatable-cell-sort"><span style="width: 90px;">Cod Barra</span></th>
                                                    <th data-field="Type" data-autohide-disabled="false" class="datatable-cell datatable-cell-sort"><span style="width: 80px;">Un. Compra</span></th>
                                                    <th data-field="Type" data-autohide-disabled="false" class="datatable-cell datatable-cell-sort"><span style="width: 80px;">Valor</span></th>
                                                    <th data-field="Type" data-autohide-disabled="false" class="datatable-cell datatable-cell-sort"><span style="width: 80px;">Qtd</span></th>
                                                    <th data-field="Type" data-autohide-disabled="false" class="datatable-cell datatable-cell-sort"><span style="width: 80px;">CFOP Ent.</span></th>
                                                    <th data-field="Type" data-autohide-disabled="false" class="datatable-cell datatable-cell-sort"><span style="width: 80px;">Subtotal</span></th>
                                                    <th data-field="Actions" data-autohide-disabled="false" class="datatable-cell datatable-cell-sort"><span style="width: 80px;">Ações</span></th>
                                                </tr>
                                            </thead>
                                            <tbody class="">
                                                <?php $__currentLoopData = $itens; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <tr class="" id="tr_<?php echo e($i['codigo']); ?>">
                                                    <input type="hidden" class="inp-novo-<?php echo e($i['codigo']); ?> inp-check" value="<?php echo e($i['produtoNovo']); ?>">

                                                    <input type="hidden" class="produto_id_<?php echo e($i['codigo']); ?>" name="produto_id[]" value="<?php echo e($i['id']); ?>">
                                                    <input type="hidden" name="quantidade[]" value="<?php echo e($i['qCom']); ?>">
                                                    <input type="hidden" name="valor_unitario[]" value="<?php echo e($i['vUnCom']); ?>">
                                                    <input type="hidden" name="unidade_compra[]" value="<?php echo e($i['uCom']); ?>">
                                                    
                                                    <td><?php echo e($i['codigo']); ?></td>
                                                    <td><span id="n_<?php echo e($i['codigo']); ?>" class="<?php echo e($i['produtoNovo'] ? 'text-danger' : ''); ?> nome"><?php echo e($i['xProd']); ?></span></td>
                                                    <td><span class="ncm" style="width: 80px;"><?php echo e($i['NCM']); ?></span></td>
                                                    <td><span class="cfop" style="width: 80px;"><?php echo e($i['CFOP']); ?></span></td>
                                                    <td><span class="codBarras" style="width: 90px;"><?php echo e($i['codBarras']); ?></span></td>
                                                    <td><span class="unidade" style="width: 80px;"><?php echo e($i['uCom']); ?></span></td>
                                                    <td class="mt-3"><?php echo e(__moeda((float)$i['vUnCom'])); ?></td>
                                                    <td class="mt-5"><span id="qtd_aux_<?php echo e($i['codigo']); ?>" class="quantidade mt-3"><?php echo e($i['qCom']); ?></span></td>
                                                    <td>
                                                        <span class="" id="cfop_entrada_<?php echo e($i['codigo']); ?>" style="">
                                                            <input id="cfop_entrada_input" class="cfop form-control" style="width: 60px;" type="text" value="<?php echo e($i['CFOP_entrada']); ?>" name="">
                                                        </span>
                                                    </td>
                                                    <td class=""><?php echo e(__moeda((float) $i['qCom'] * (float) $i['vUnCom'])); ?></td>
                                                    <th>
                                                        <span>
                                                            <a class="<?php if(!$i['produtoNovo']): ?> d-none <?php endif; ?> btn-cad-<?php echo e($i['codigo']); ?>" id="th_acao1_<?php echo e($i['codigo']); ?>" onclick="cadProd('<?php echo e($i['codigo']); ?>','<?php echo e($i['xProd']); ?>','<?php echo e($i['codBarras']); ?>','<?php echo e($i['NCM']); ?>','<?php echo e($i['CFOP']); ?>','<?php echo e($i['uCom']); ?>','<?php echo e($i['vUnCom']); ?>','<?php echo e($i['qCom']); ?>', '<?php echo e($i['CFOP']); ?>','<?php echo e($i['CEST']); ?>')" href="javascript:;" data-bs-toggle="modal" data-bs-target="#modal-produto">
                                                                <span class="btn btn-success btn-sm">
                                                                    <i class="bx bx-plus"></i>
                                                                </span>
                                                            </a>
                                                        </span>
                                                    </th>
                                                </tr>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </tbody>
                                        </table>
                                        <br><br>
                                        <?php if($dadosNf['contSemRegistro'] > 0): ?>
                                        <div class="row sem-registro">
                                            <div class="col-xl-12">
                                                <p class="text-danger">*Esta nota possui produto(s) sem cadastro, inclua antes de continuar</p>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-12">
                            <div class="card card-custom gutter-b example example-compact">
                                <div class="card-body">
                                    <input type="hidden" id="fatura" value="<?php echo e(json_encode($fatura)); ?>">
                                    <div class="table-responsive">
                                        <h4>Fatura</h4>
                                        <table class="table mb-0 table-striped table-dynamic">
                                            <thead>
                                                <tr>
                                                    <th>Vencimento</th>
                                                    <th>Valor</th>
                                                    <th>Ações</th>
                                                </tr>
                                            </thead>
                                            <tbody id="body" class="datatable-body">
                                                <?php $__currentLoopData = $fatura; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <tr class="dynamic-form">
                                                    <td class="col-2">
                                                        <input type="date" class="form-control" name="vencimento[]" value="<?php echo e($f['vencimento']); ?>">
                                                    </td>
                                                    <td class="col-2">
                                                        <input type="text" class="form-control moeda" name="valor_parcela[]" value="<?php echo e(__moeda($f['valor_parcela'])); ?>">
                                                    </td>
                                                    <td class="">
                                                        <button class="btn btn-danger btn-sm btn-remove-tr">
                                                            <i class="bx bx-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="row">
                                        <div class="col-12">
                                            <br>
                                            <button type="button" class="btn btn-success btn-add-tr">
                                                Adicionar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="total" id="total" value="<?php echo e($dadosNf['vProd']); ?>" name="">
                        <div class="col-xl-12">
                            <div class="row">
                                <div class="col-xl-6">
                                    <h4>Total: <strong id="valorDaNF" class="blue-text">R$ <?php echo e(__moeda((float)$dadosNf['vProd'])); ?></strong></h4>
                                </div>
                                <div class="col-xl-3">
                                </div>
                                <div class="col-xl-3">
                                    <button type="submit" disabled id="btn-salvar" style="width: 100%" class="btn btn-success spinner-white spinner-right">
                                        <i class=""></i>
                                        <span class="">Salvar</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php echo Form::close(); ?>

                    <br>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $__env->startSection('js'); ?>
<script type="text/javascript" src="/js/compraFiscal.js"></script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('modals._produto', ['not_submit' => true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<?php $__env->stopSection(); ?>





<?php echo $__env->make('default.layout',['title' => 'Compra Fiscal'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home2/wesl4494/public_html/padariacasadopao.com/resources/views/compra_fiscal/import.blade.php ENDPATH**/ ?>