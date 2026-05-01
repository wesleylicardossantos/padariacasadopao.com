<input type="hidden" id="caixa_livre" value="<?php echo e($usuario->caixa_livre); ?>" name="">
<input type="hidden" id="abertura" value="<?php echo e($abertura); ?>" name="">
<input type="hidden" id="prevenda_id" value="<?php echo e(isset($item) ? $item->id : null); ?>" name="prevenda_id">

<?php if(isset($itens)): ?>
<input type="hidden" id="itens_pedido" value="<?php echo e(json_encode($itens)); ?>">
<input type="hidden" id="valor_total" <?php if(isset($valor_total)): ?> value="<?php echo e($valor_total); ?>" <?php else: ?> value='0' <?php endif; ?>>
<input type="hidden" id="delivery_id" <?php if(isset($delivery_id)): ?> value="<?php echo e($delivery_id); ?>" <?php else: ?> value='0' <?php endif; ?>>
<input type="hidden" id="bairro" <?php if(isset($bairro)): ?> value="<?php echo e($bairro); ?>" <?php else: ?> value='0' <?php endif; ?>>
<input type="hidden" id="codigo_comanda_hidden" <?php if(isset($cod_comanda)): ?> value="<?php echo e($cod_comanda); ?>" <?php else: ?> value='0' <?php endif; ?> name="">
<?php endif; ?>

<input type="hidden" id="codigo_comanda" value="0" name="codigo_comanda">

<?php if(isset($pedido)): ?>
<input type="hidden" value="<?php echo e($pedido->id); ?>" name="pedido_id">
<?php endif; ?>

<?php if(isset($filial)): ?>
<input type="hidden" id="filial" class="filial_id" name="filial_id" value="<?php echo e($filial == null ? null : $filial); ?>">
<?php endif; ?>

<div class="card card-custom gutter-b example">
    <div class="col-lg-12 mt-2">
        <div class="row row-cols-auto m-3">
            <h5 class=""><strong id="timer" class="is-desktop"></strong>
                <?php if($usuario->caixa_livre): ?>
                <span class="text-info">Caixa Livre</span>
                <button data-toggle="modal" data-target="#modal-funcionarios" class="btn btn-sm btn-light-info">
                    <i class="bx bx-user"></i>
                </button>
                <?php endif; ?>
            </h5>
            <div class="col is-desktop">
                <button type="button" class="btn btn-dark btn-sm" style="margin-left: -10px" data-bs-toggle="modal" data-bs-target="#modal-selecionar_vendedor"><i class="bx bx-user-check"></i> Informar
                Vendedor</button>
            </div>
            <div class="col is-desktop">
                <button type="button" class="btn btn-info btn-sm" style="margin-left: -10px" data-bs-toggle="modal" data-bs-target="#modal-lista_pre_venda"><i class="bx bx-folder-open"></i> Lista de
                Pré-vendas</button>
            </div>
            <div class="col is-desktop">
                <a href="<?php echo e(route('frenteCaixa.list')); ?>" type="button" class="btn btn-primary  btn-sm" style="margin-left: -10px"><i class="bx bx-list-check"></i> Lista de Vendas</a>
            </div>
            <div class="col is-desktop">
                <button type="button" class="btn btn-warning btn-sm" style="margin-left: -10px" data-bs-toggle="modal" data-bs-target="#modal-fluxo_diario"><i class="bx bx-money"></i> Fluxo
                Diário</button>
            </div>
            <div class="col is-desktop">
                <a class="btn btn-success btn-sm" style="margin-left: -10px" href="<?php echo e(route('frenteCaixa.troca')); ?>"><i class="bx bx-sync"></i> Lista de Trocas</a>
            </div>

            <h4 class="h4-comanda text-primary"></h4>

            <div class="row ms-auto">
                <div class="col">
                    <button class="btn btn-outline-primary dropdown-toggle btn-sm" data-bs-toggle="dropdown" aria-expanded="false">Ações</button>
                    <ul class="dropdown-menu">
                        <li><a class="btn btn-outline-secondary dropdown-item" href="<?php echo e(route('frenteCaixa.devolucao')); ?>">Devolução</a>
                        </li>
                        <li><a class="btn btn-outline-secondary dropdown-item" data-bs-toggle="modal" data-bs-target="#modal-sangria_caixa">Sangria</a>
                        </li>
                        <li><a class="btn btn-outline-secondary dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#modal-suprimento_caixa">Suprimento de Caixa</a>
                        </li>
                        <li><a class="btn btn-outline-secondary dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#modal-comanda_pdv">Apontar Comanda</a>
                        </li>
                        <li><a class="btn btn-outline-secondary dropdown-item" href="<?php echo e(route('frenteCaixa.fechar')); ?>">Fechar
                        Caixa</a>
                    </li>
                    <li><a class="btn btn-outline-secondary dropdown-item" href="<?php echo e(route('frenteCaixa.configuracao')); ?>">Configuração</a>
                    </li>
                    <li><a class="btn btn-outline-secondary dropdown-item" href="<?php echo e(route('frenteCaixa.list')); ?>">Sair</a>
                    </li>
                </ul>
            </div>

            <div class="col is-mobile">
                <button class="btn btn-outline-success dropdown-toggle btn-sm" data-bs-toggle="dropdown" aria-expanded="false">PDV</button>
                <ul class="dropdown-menu">

                    <li>
                        <button type="button" class="btn btn-outline-secondary dropdown-item" data-bs-toggle="modal" data-bs-target="#modal-selecionar_vendedor"><i class="bx bx-user-check"></i> Informar Vendedor
                        </button>
                    </li>
                    <li>
                        <button type="button" class="btn btn-outline-secondary dropdown-item" data-bs-toggle="modal" data-bs-target="#modal-lista_pre_venda"><i class="bx bx-folder-open"></i> Lista de
                        Pré-vendas</button>
                    </li>
                    <li>
                        <a href="<?php echo e(route('frenteCaixa.list')); ?>" type="button" class="btn btn-outline-secondary dropdown-item" ><i class="bx bx-list-check"></i> Lista de Vendas</a>
                    </li>
                    <li>
                        <button type="button" class="btn btn-outline-secondary dropdown-item" data-bs-toggle="modal" data-bs-target="#modal-fluxo_diario"><i class="bx bx-money"></i> Fluxo
                        Diário</button>
                    </li>
                    <li>
                        <a class="btn btn-outline-secondary dropdown-item" href="<?php echo e(route('frenteCaixa.troca')); ?>"><i class="bx bx-sync"></i> Lista de Trocas</a>
                    </li>

                </ul>
            </div>
            <div class="col">
                <div class="col" style="margin-left: -10px">
                    <a class="btn btn-outline-danger btn-sm" href="<?php echo e(route('vendas.index')); ?>">Sair</a>
                </div>
            </div>
        </div>
    </div>
    <div class="row dark-theme m-1">
        <div class="col-lg-8 col-12">

            <div class="input-group-prepend">
                <span class="input-group-text" id="focus-codigo">
                    <li class="bx bx-barcode"></li>
                    <input class="mousetrap" type="" autofocus id="codBarras" name="">
                    <span id="mousetrapTitle"><span class="texto-leitor">CLIQUE AQUI PARA ATIVAR O LEITOR</span> <i class="las la-sort-down" style="margin-top: 4px;"></i></span>
                </span>
            </div>
            <div class="row mt-2">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="inp-produto_id" class="">Produto</label>
                        <div class="input-group">
                            <select class="form-control produto_id" name="produto_id" id="inp-produto_id"></select>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <?php echo Form::tel('quantidade', 'Quantidade')->attrs(['class' => 'qtd']); ?>

                </div>
                <div class="col-md-2">
                    <?php echo Form::tel('valor_unitario', 'Valor Unitário')->attrs(['class' => 'moeda value_unit']); ?>

                </div>
                <div class="col-md-1 is-desktop" style="margin-left: 20px">
                    <br>
                    <button class="btn btn-primary btn-add-item" type="button">Adicionar</button>
                </div>
                <div class="col-md-1 is-mobile" style="margin-top: 10px">
                    <button class="btn btn-primary btn-add-item w-100" type="button">Adicionar</button>
                </div>
                <div class="table-responsive" style="height: 480px">
                    <table class="table mb-0 table-striped mt-2 table-itens table-pdv">
                        <thead>
                            <tr>
                                <th>Produto</th>
                                <th>QTD</th>
                                <th>Valor</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(isset($itensDopedido)): ?>
                            <?php $__currentLoopData = $itensDopedido; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $it): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                            <?php echo $it; ?>

                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php endif; ?>
                            <?php if(isset($item)): ?>
                            <?php $__currentLoopData = $item->itens; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>

                                <input readonly type="hidden" name="key" class="form-control" value="<?php echo e($product->key); ?>">
                                <input readonly type="hidden" name="produto_id[]" class="form-control" value="<?php echo e($product->produto->id); ?>">

                                <td>
                                    <input readonly type="text" name="produto_nome[]" class="form-control" value="<?php echo e($product->produto->nome); ?>">
                                </td>
                                <td>
                                    <input readonly type="tel" name="quantidade[]" class="form-control qtd-item" value="<?php echo e(__estoque($product->quantidade)); ?>">
                                </td>
                                <td>
                                    <input readonly type="tel" name="valor_unitario[]" class="form-control" value="<?php echo e(__moeda($product->valor)); ?>">
                                </td>
                                <td>
                                    <input readonly type="tel" name="subtotal_item[]" class="form-control subtotal-item" value="<?php echo e(__moeda($product->valor * $product->quantidade)); ?>">
                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    <hr>
                </div>
            </div>
            <div class="card" style="background-color: rgb(248, 242, 242) ; margin-top: -10px">
                <div class="row" style="margin-left: 5px">
                    <div class="col-md-3">
                        <p class="mt-2">Desconto: <strong class="class_desconto" id="valor_desconto">R$ 0,00 </strong> <button type="button" onclick="setaDesconto()" class="btn btn-warning btn-sm mt-1 btn-desconto"><i class="bx bx-edit"></i></button></p>
                    </div>
                    <div class="col-md-3">
                        <p class="mt-2">Acréscimo: <strong class="class_acrescimo" id="valor_acrescimo">R$ 0,00 </strong> <button type="button" onclick="setaAcrescimo()" class="btn btn-warning btn-sm mt-1 btn-acrescimo"><i class="bx bx-edit"></i></button></p>
                    </div>
                    <div class="col-md-6 col-12 mt-1 mb-1">
                        <label>Lista de Preços:</label>

                        <select name="" id="" class="form-select mt-2 w-75">
                            <?php $__currentLoopData = $lista; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value=""><?php echo e($item->nome); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <?php if(isset($abertura)): ?>
                    <?php if(empresaComFilial() && $abertura): ?>
                    <div class="row align-items-center">
                        <div class="col-lg-12">
                            <div style="display: flex;align-items: center;height: 100%;">
                                <h6 class="mb-0">
                                    Local: <strong class="text-info"><?php echo e($filial != null ? $filial->descricao : 'Matriz'); ?></strong>
                                </h6>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-12">
            <div class="card" style="background-color: rgb(243, 231, 231); height: 650px">
                <div class="row row-cols-auto m-2 btns-pdv">
                    <div class="col-lg-4 col-12">
                        <button type="button" data-bs-toggle="modal" data-bs-target="#modal-selecionar_cliente" class="btn btn-info btn-sm btn-selecionar_cliente w-100"><i class="bx bx-user"></i>Cliente</button>
                    </div>
                    <div class="col-lg-4 col-12">
                        <button type="button" class="btn btn-primary btn-sm modal-pag_mult w-100" data-bs-toggle="modal" data-bs-target="#modal-pag_multi_pdv"><i class="bx bx-list-ol"></i>Pag. Multiplo</button>
                    </div>
                    <div class="col-lg-4 col-12">
                        <button type="button" class="btn btn-warning btn-sm w-100" data-bs-toggle="modal" data-bs-target="#modal-observacoes_pdv"><i class="bx bx-pencil"></i>Observações</button>
                    </div>
                </div>
                <hr>
                <div class="card m-2" style="background-color: rgb(217, 223, 209)">
                    <h6 class="m-3">TOTAL</h6>
                    <div class="row">
                        <p class="col-2 m-3">R$:</p>
                        <h1 class="col-6 m-1">
                            <?php if(isset($item)): ?>
                            <strong class="total-venda" style="margin-left:-40px"><?php echo e(__moeda($item->valor_total)); ?></strong>
                            <?php else: ?>
                            <strong class="total-venda" style="margin-left:-40px">0,00</strong>
                            <?php endif; ?>
                        </h1>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="m-2">
                        <div class="col-11">
                            <?php echo Form::select(
                            'tipo_pagamento',
                            'Tipo de Pagamento',
                            ['' => 'Selecione'] + App\Models\Venda::tiposPagamento(),
                            )->attrs([
                            'class' => 'select2',
                            ]); ?>

                        </div>
                        <div class="col-md-6 div-vencimento d-none mt-2">
                            <?php echo Form::date('data_vencimento', 'Data Vencimento'); ?>

                        </div>
                        <div class="col-11 mt-3">
                            <input type="text" id="valor_recebido" name="valor_recebido" placeholder="Valor Recebido" class="form-control moeda">
                        </div>
                        <div class="card col-11 mt-3" style="background-color: rgb(143, 145, 141)">
                            <div class="row div-toco">
                                <h6 class="col-lg-3 m-2" style="font-size: 20px">Troco:</h6>
                                <h6 class="col-lg-3 m-2" style="font-size: 25px">
                                    <?php if(isset($item)): ?>
                                    <strong class="" id="valor-troco"></strong>
                                    <?php else: ?>
                                    <strong class="" id="valor-troco">0,00</strong>
                                    <?php endif; ?>
                                </h6>
                            </div>
                        </div>

                        <?php echo Form::hidden('subtotal', 'SubTotal')->attrs(['class' => 'moeda']); ?>


                        
                        <div class="col-md-12">
                            <button style="width: 96%; margin-top: 135px;" type="button" id="salvar_venda" disabled class="btn btn-success px-5" data-bs-toggle="modal" data-bs-target="#modal-finalizar_venda">
                                Finalizar Venda
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<?php echo $__env->make('modals.frontBox._selecionar_cliente', ['not_submit' => true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php echo $__env->make('modals.frontBox._observacoes_pdv', ['not_submit' => true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php echo $__env->make('modals.frontBox._selecionar_vendedor', ['not_submit' => true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php echo $__env->make('modals.frontBox._pag_multi_pdv', ['not_submit' => true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php echo $__env->make('modals.frontBox._finalizar_venda', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php echo $__env->make('modals.frontBox._dados_cartao', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php /**PATH /home2/wesl4494/public_html/padariacasadopao.com/resources/views/frontBox/_forms.blade.php ENDPATH**/ ?>