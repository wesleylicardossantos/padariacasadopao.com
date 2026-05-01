<?php $__env->startSection('css'); ?>
<style type="text/css">
    .ck-editor__editable {
        width: 100%;
        min-height: 300px;
    }

</style>
<?php $__env->stopSection(); ?>
<div class="row g-3 m-auto">
    <div class="row mt-3">
        <div class="col-md-6 row">
            <button type="button" class="btn btn-identificacao btn-outline-primary link-active px-6" onclick="selectDiv('identificacao')">Identificação do produto</button>
        </div>
        <div class="col-md-6 row m-auto">
            <button type="button" class="btn btn-aliquotas btn-outline-primary" onclick="selectDiv('aliquotas')">Aliquotas</button>
        </div>
    </div>
    <input type="hidden" value="<?php echo e(csrf_token()); ?>" id="token">
    <div class="div-identificacao row mt-4">
        <p class="" style="color: crimson">* Campos obrigatórios</p>
        <div class="col-md-7">
            <?php echo Form::text('nome', 'Descrição')->required(); ?>

        </div>
        <div class="col-md-2">
            <?php echo Form::text('referencia', 'Referência')->attrs(['class' => 'ignore']); ?>

        </div>

        <?php if(isset($item)): ?>
        <?php echo __view_locais_edit($item->locais, 'Disponibilidade'); ?>

        <?php else: ?>
        <?php echo __view_locais('Disponibilidade'); ?>

        <?php endif; ?>

        <?php if(!empresaComFilial()): ?>
        <div class="col-md-3"></div>
        <?php endif; ?>

        <div class="col-md-2 mt-2">
            <?php echo Form::tel('valor_compra', 'Valor do custo')->attrs(['class' => 'moeda'])->value(isset($item) ? __moeda($item->valor_compra) : '')->required(); ?>

        </div>
        <?php
        $config = App\Models\ConfigNota::configStatic();
        ?>
        <div class="col-md-2 mt-2">
            <?php echo Form::tel('percentual_lucro', '% de lucro')->value(isset($item) ? __moeda($item->percentual_lucro) : $config->percentual_lucro_padrao )->attrs(['class' => 'perc'])->required(); ?>

        </div>
        <div class="col-md-2 mt-2">
            <?php echo Form::tel('valor_venda', 'Valor de venda')->attrs(['class' => 'moeda'])->value(isset($item) ? __moeda($item->valor_venda) : '')->required(); ?>

        </div>
        <div class="col-md-2 mt-2">
            <?php echo Form::tel('estoque_inicial', 'Estoque inicial')->attrs(['class' => 'ignore']); ?>

        </div>
        <div class="col-md-4 mt-2">
            <div class="form-group">
                <label for="inp-" class="">Código de barras</label>
                <div class="input-group">
                    <input type="tel" id="inp-codBarras" class="form-control ignore" name="codBarras" value="<?php echo e(isset($item) ? $item->codBarras : ''); ?>">
                    <button type="button" class="btn btn-primary" id="btn-codBarras">
                        <i class="bx bx-barcode-reader"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="col-md-2 mt-2">
            <?php echo Form::select('reajuste_automatico', 'Reajuste automático', [0 => 'Não', 1 => 'Sim'])->attrs([
            'class' => 'form-select ignore',
            ]); ?>

        </div>
        <div class="col-md-2 mt-2">
            <?php echo Form::select('gerenciar_estoque', 'Gerenciar estoque', [0 => 'Não', 1 => 'Sim'])->attrs([
            'class' => 'form-select',
            ]); ?>

        </div>
        <div class="col-md-2 mt-2">
            <?php echo Form::select('inativo', 'Inativo', [0 => 'Não', 1 => 'Sim'])->attrs(['class' => 'form-select']); ?>

        </div>

        <?php if(isset($item)): ?>
        <div class="col-md-2 mt-2 d-none">
            <?php echo Form::select('grade', 'Tipo grade', [0 => 'Não', 1 => 'Sim'])->attrs(['class' => 'form-select']); ?>

        </div>
        <?php else: ?>
        <div class="col-md-2 mt-2">
            <?php echo Form::select('grade', 'Tipo grade', [0 => 'Não', 1 => 'Sim'])->attrs(['class' => 'form-select']); ?>

        </div>
        <?php endif; ?>

        <div class="col-md-4 mt-2">
            <div class="form-group">
                <label for="inp-categoria_id" class="required">Categoria</label>
                <div class="input-group">
                    <select class="form-control select2" name="categoria_id" id="inp-categoria_id" required>
                        <option value="">Selecione a categoria</option>
                        <?php $__currentLoopData = $categorias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option <?php if(isset($item)): ?> <?php if($item->categoria_id == $c->id): ?> selected <?php endif; ?> <?php endif; ?> value="<?php echo e($c->id); ?>">
                            <?php echo e($c->nome); ?>

                        </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <?php if(!isset($not_submit)): ?>
                    <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#modal-categoria">
                        <i class="bx bx-plus"></i></button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-4 mt-2">
            <div class="form-group">
                <label for="inp-sub_categoria_id" class="">Sub categoria</label>
                <div class="input-group">
                    <select class="form-control select2 ignore" name="sub_categoria_id" id="inp-sub_categoria_id">
                        <option value="">Selecione</option>
                        <?php if(isset($item)): ?>
                        <option selected value="<?php echo e($item->sub_categoria_id); ?>">
                            <?php echo e($item->subCategoria); ?>

                        </option>
                        <?php endif; ?>
                    </select>
                    <?php if(!isset($not_submit)): ?>
                    <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#modal-sub_categoria">
                        <i class="bx bx-plus"></i></button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-3 mt-2">
            <div class="form-group">
                <label for="inp-marcas_id" class="">Marca</label>
                <div class="input-group">
                    <select class="form-control select2 ignore" name="marca_id" id="inp-marca_id">
                        <?php if(isset($marcas)): ?>
                        <?php $__currentLoopData = $marcas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($m->id); ?>">
                            <?php echo e($m->nome); ?>

                        </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php endif; ?>
                    </select>
                    <?php if(!isset($not_submit)): ?>
                    <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#modal-marca">
                        <i class="bx bx-plus"></i>
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-2 mt-2">
            <?php echo Form::tel('estoque_minimo', 'Estoque mínimo')->attrs(['class' => 'ignore']); ?>

        </div>
        <div class="col-md-3 mt-2">
            <?php echo Form::tel('limite_maximo_desconto', 'Limite máx. de desconto')->attrs(['class' => 'ignore']); ?>

        </div>

        <div class="col-md-2 mt-2">
            <?php echo Form::select('unidade_compra', 'Un. de compra', App\Models\Produto::unidadesMedida())->value(isset($item) ? $item->unidade_compra : 'UN')->attrs(['class' => 'form-select']); ?>

        </div>
        <div class="col-md-2 mt-2">
            <?php echo Form::select('unidade_venda', 'Un. de venda', App\Models\Produto::unidadesMedida())->value(isset($item) ? $item->unidade_compra : 'UN')->attrs(['class' => 'form-select']); ?>

        </div>
        <div class="col-md-2 mt-2">
            <?php echo Form::tel('conversao_unitaria', 'Conversão unitária')->attrs(['class' => 'ignore']); ?>

        </div>
        <div class="col-md-3 mt-2">
            <?php echo Form::date('alerta_vencimento', 'Alerta de venc. (dias)')->attrs(['class' => 'ignore']); ?>

        </div>
        <div class="col-md-2 mt-2">
            <?php if(isset($tributacao)): ?>
            <?php echo Form::tel('NCM', 'NCM')->attrs(['class' => 'ncm'])->value(isset($item) ? $item->NCM : $tributacao->ncm_padrao); ?>

            <?php else: ?>
            <?php echo Form::tel('NCM', 'NCM')->attrs(['class' => 'ncm']); ?>

            <?php endif; ?>
        </div>
        <div class="col-md-2 mt-2">
            <?php echo Form::tel('CEST', 'CEST')->attrs(['class' => 'ignore']); ?>

        </div>
        <div class="col-md-2 mt-2">
            <?php echo Form::tel('referencia_balanca', 'Ref balança')->attrs(['class' => 'ignore']); ?>

        </div>
        <div class="col-md-2 mt-2">
            <?php echo Form::tel('perc_comissao', '% Comissão')->attrs(['class' => 'perc'])->attrs(['class' => 'ignore']); ?>

        </div>
        <div class="col-md-3 mt-2">
            <?php echo Form::select('tipo_dimensao', 'Tipo de dimensão', [2 => '--', 1 => 'Area', 0 => 'Dimensão'])->attrs(['class' => 'form-select']); ?>

        </div>
        <div class="col-md-3 mt-2">
            <?php
            $appendAttr = '<label class="text-info label-popover" data-bs-container="body" data-bs-toggle="popover" data-bs-placement="bottom" data-bs-content="Se marcado com Sim, quando adicionado em um pedido será listado na tela de controle de pedidos." data-bs-original-title="" title="">
                <i class="bx bx-info-circle m-1"></i>
            </label>';
            ?>
            <?php echo Form::select('envia_controle_pedidos', 'Envia controle pedidos' . $appendAttr, [0 => 'Não', 1 => 'Sim'])->attrs(['class' => 'form-select ignore']); ?>

        </div>
        <div class="col-md-3 mt-2">
            <?php
            $appendAttr = '<label class="text-info label-popover" data-bs-container="body" data-bs-toggle="popover" data-bs-placement="bottom" data-bs-content="Se selecionado alguma opção, o item aparecerá para tela selecionada | Cadastro em menu pedidos - tela de pedidos" data-bs-original-title="" title="">
                <i class="bx bx-info-circle m-1"></i>
            </label>';
            ?>
            <?php echo Form::select('tela_pedido_id', 'Tela de pedido (opcional)' . $appendAttr, ['' => '--'] + $telasPedido->pluck('nome', 'id')->all())->attrs(['class' => 'form-select ignore']); ?>

        </div>
        <div class="col-12 mt-4">
            <?php if(!isset($not_submit)): ?>
            <div id="image-preview" class="_image-preview col-md-4">
                <label for="" id="image-label" class="_image-label">Selecione a imagem</label>
                <input type="file" name="image" id="image-upload" class="_image-upload" accept="image/*" />
                <?php if(isset($item)): ?>
                <?php if($item->imagem): ?>
                <img src="/uploads/products/<?php echo e($item->imagem); ?>" class="img-default">
                <?php else: ?>
                <img src="/imgs/no_product.png" class="img-default">
                <?php endif; ?>
                <?php else: ?>
                <img src="/imgs/no_product.png" class="img-default">
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        <div class="clearfix"></div>
        <div class="col-md-3 mt-4">
            <?php echo Form::select('delivery', 'Atribuir ao delivery', [0 => 'Não', 1 => 'Sim'])->attrs(['class' => 'form-select']); ?>

        </div>

        <div class="col-md-3 mt-4">
            <?php echo Form::select('ecommerce', 'Atribuir ao ecommerce', [0 => 'Não', 1 => 'Sim'])->attrs(['class' => 'form-select']); ?>

        </div>

        <div class="col-md-3 mt-4">
            <?php echo Form::select('locacao', 'Locação', [0 => 'Não', 1 => 'Sim'])->attrs(['class' => 'form-select']); ?>

        </div>
        <div class="col-md-3 d-locacao d-none mt-4">
            <?php echo Form::tel('valor_locacao', 'Valor da locação')->attrs(['class' => 'moeda ignore']); ?>

        </div>

        <div class="row d-ecommerce d-none mt-4">
            <p style="color: crimson" class="mt-1">* Se Atente para preencher todos os dados para utilizar o API dos
                correios.</p>
            <div class="col-md-4 mt-2">
                <?php if(isset($categoriasEcommerce)): ?>
                <?php echo Form::select('ecommerce_categoria_id', 'Categoria', $categoriasEcommerce->pluck('nome', 'id'))->attrs([
                'class' => 'select2 ignore',
                ]); ?>

                <?php endif; ?>
            </div>
            <div class="col-md-4 mt-2">
                <?php echo Form::select('ecommerce_sub_categoria_id', 'Sub categoria')->attrs(['class' => 'select2 ignore']); ?>

            </div>
            <div class="col-md-3 mt-2">
                <?php echo Form::text('valor_ecommerce', 'Valor')->attrs(['class' => 'moeda ignore'])->value(isset($item->ecommerce) ? __moeda($item->ecommerce->valor) : ''); ?>

            </div>
            <div class="col-md-3 mt-2">
                <?php echo Form::select('ecommerce_controlar_estoque', 'Controlar estoque', [0 => 'Não', 1 => 'Sim'])->attrs([
                'class' => 'select2',
                ]); ?>

            </div>
            <div class="col-md-3 mt-2">
                <?php echo Form::select('ecommerce_ativo', 'Ativo', [0 => 'Não', 1 => 'Sim'])->attrs(['class' => 'select2']); ?>

            </div>
            <div class="col-md-3 mt-2">
                <?php echo Form::select('ecommerce_destaque', 'Destaque', [0 => 'Não', 1 => 'Sim'])->attrs(['class' => 'select2']); ?>

            </div>
            <?php if(!isset($not_submit)): ?>
            <div class="col-md-12 mt-3 nicEdit-panelContain">
                <label for="">Descrição</label>
                <textarea name="descricao_ecommerce" id="descricao-ecommerce" cols="120" rows="10" value="<?php echo e(isset($item->ecommerce) ? $item->ecommerce->descricao : ''); ?>"></textarea>
            </div>
            <?php endif; ?>
        </div>

        <div class="row">
            <div class="col-md-3 mt-4">
                <?php
                $appendAttr = '<label class="text-info label-popover" data-bs-container="body" data-bs-toggle="popover" data-bs-placement="bottom" data-bs-content="Produzido no estabelecimento, composto de outros produtos já cadastrados, deverá ser criado uma composição/receita para redução de estoque." data-bs-original-title="" title="">
                    <i class="bx bx-info-circle m-1"></i>
                </label>';
                ?>
                <?php echo Form::select('composto', 'Composto' . $appendAttr, [0 => 'Não', 1 => 'Sim'])->attrs(['class' => 'form-select']); ?>

            </div>
            <?php if(!isset($not_submit)): ?>
            <div class="col-md-12 d-comp d-none mt-2">
                <div class="col-md-12 d-comp d-none">
                    <textarea name="info_tecnica_composto" id="info_tecnica_composto" cols="83" rows="6"></textarea>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <div class="col-md-3 mt-3">
            <?php echo Form::select('derivado_petroleo', 'Derivado de petróleo', [0 => 'Não', 1 => 'Sim'])->attrs([
            'class' => 'form-select',
            ]); ?>

        </div>
        <div class="row d-pet d-none">
            <div class="col-md-6 mt-2">
                <?php echo Form::select('codigo_anp', 'ANP', App\Models\Produto::lista_ANP())->attrs(['class' => 'select2']); ?>

            </div>
            <div class="col-md-3 mt-2">
                <?php echo Form::tel('perc_glp', '%GLP')->attrs(['class' => 'tel'])->attrs(['class' => 'ignore']); ?>

            </div>
            <div class="col-md-3 mt-2">
                <?php echo Form::tel('perc_gnn', '%GNn')->attrs(['class' => 'tel'])->attrs(['class' => 'ignore']); ?>

            </div>
            <div class="col-md-3 mt-2">
                <?php echo Form::tel('perc_gni', '%GNi')->attrs(['class' => 'tel'])->attrs(['class' => 'ignore']); ?>

            </div>
            <div class="col-md-3 mt-2">
                <?php echo Form::tel('valor_partida', 'Valor de partida')->attrs(['class' => 'tel'])->attrs(['class' => 'ignore']); ?>

            </div>
            <div class="col-md-3 mt-2">
                <?php echo Form::tel('unidade_tributavel', 'Und tributável')->attrs(['class' => 'tel'])->attrs(['class' => 'ignore']); ?>

            </div>
            <div class="col-md-3 mt-2">
                <?php echo Form::tel('quantidade_tributavel', 'Qtd tributável')->attrs(['class' => 'tel'])->attrs(['class' => 'ignore']); ?>

            </div>
        </div>
        <h5 class="mt-3">Dados de dimensões e peso do produto (opcional)</h5>
        <div class="col-md-2 mt-2">
            <?php echo Form::tel('largura', 'Largura (cm)')->attrs(['class' => 'tel'])->attrs(['class' => 'ignore']); ?>

        </div>
        <div class="col-md-2 mt-2">
            <?php echo Form::tel('altura', 'Altura (cm)')->attrs(['class' => 'tel'])->attrs(['class' => 'ignore']); ?>

        </div>
        <div class="col-md-2 mt-2">
            <?php echo Form::tel('comprimento', 'Comprimento (cm)')->attrs(['class' => 'tel'])->attrs(['class' => 'ignore']); ?>

        </div>
        <div class="col-md-2 mt-2">
            <?php echo Form::tel('peso_liquido', 'Peso líquido')->attrs(['class' => 'tel'])->attrs(['class' => 'ignore']); ?>

        </div>
        <div class="col-md-2 mt-2">
            <?php echo Form::tel('peso_bruto', 'Peso bruto')->attrs(['class' => 'tel'])->attrs(['class' => 'ignore']); ?>

        </div>
        <h5 class="mt-5 lote-vencimento">Lote e vencimento (opcional)</h5>
        <div class="col-md-3">
            <?php echo Form::select('lote-vencimento', 'Lote e vencimento', [0 => 'Não', 1 => 'Sim'])->attrs([
            'class' => 'form-select',
            ]); ?>

        </div>
        <div class="row d-lote d-none">
            <div class="col-md-3 mt-3">
                <?php echo Form::text('lote', 'Lote')->attrs(['class' => ''])->attrs(['class' => 'ignore']); ?>

            </div>
            <div class="col-md-3 mt-3">
                <?php echo Form::date('vencimento', 'Vencimento')->attrs(['class' => ''])->attrs(['class' => 'ignore']); ?>

            </div>
        </div>
        <h5 class="dados-veiculo mt-5">Dados Veículo (Opcional)</h5>
        <div class="col-md-3">
            <?php echo Form::select('dados-veiculo', 'Dados veículo', [0 => 'Não', 1 => 'Sim'])->attrs(['class' => 'form-select']); ?>

        </div>
        <div class="row d-dados d-none">
            <div class="col-md-3 mt-3">
                <?php echo Form::text('renavam', 'Renavam')->attrs(['class' => ''])->attrs(['class' => 'ignore']); ?>

            </div>
            <div class="col-md-3 mt-3">
                <?php echo Form::text('placa', 'Placa')->attrs(['class' => 'placa'])->attrs(['class' => 'ignore']); ?>

            </div>
            <div class="col-md-3 mt-3">
                <?php echo Form::text('chassi', 'Chassi')->attrs(['class' => ''])->attrs(['class' => 'ignore']); ?>

            </div>
            <div class="col-md-3 mt-3">
                <?php echo Form::text('combustivel', 'Combustível')->attrs(['class' => ''])->attrs(['class' => 'ignore']); ?>

            </div>
            <div class="col-md-3 mt-3">
                <?php echo Form::text('ano_modelo', 'Ano/Modelo')->attrs(['class' => ''])->attrs(['class' => 'ignore']); ?>

            </div>
            <div class="col-md-3 mt-3">
                <?php echo Form::text('cor_veiculo', 'Cor')->attrs(['class' => ''])->attrs(['class' => 'ignore']); ?>

            </div>
        </div>
    </div>
    <div class="row div-aliquotas d-none  mt-4">
        <p class="" style="color: crimson">* Campos obrigatórios</p>

        <div class="col-md-12">
            <?php echo Form::select('CST_CSOSN', 'CSOSN *', App\Models\Produto::listaCSTCSOSN())->attrs([
            'class' => 'select2'
            ])->value(isset($item) ? $item->CST_CSOSN : $config->CST_CSOSN_padrao); ?>

        </div>
        <div class="col-md-6 mt-3">
            <?php echo Form::select('CST_PIS', 'CST PIS *', App\Models\Produto::listaCST_PIS_COFINS())->attrs([
            'class' => 'select2',
            ])->value(isset($item) ? $item->CST_PIS : $config->CST_PIS_padrao); ?>

        </div>
        <div class="col-md-6 mt-3">
            <?php echo Form::select('CST_COFINS', 'CST COFINS *', App\Models\Produto::listaCST_PIS_COFINS())->attrs([
            'class' => 'select2',
            ])->value(isset($item) ? $item->CST_PIS : $config->CST_COFINS_padrao); ?>

        </div>
        <div class="col-md-6 mt-3">
            <?php echo Form::select('CST_IPI', 'CST IPI *', App\Models\Produto::listaCST_IPI())->attrs([
            'class' => 'select2'
            ])->value(isset($item) ? $item->CST_IPI : $config->CST_IPI_padrao); ?>

        </div>
        <div class="col-md-6 mt-3">
            <?php echo Form::select('CST_CSOSN_EXP', 'CSOSN Exportação *', App\Models\Produto::listaCSTCSOSN())->attrs([
            'class' => 'select2',
            ]); ?>

        </div>
        
        <div class="col-md-2 mt-3">
            <?php echo Form::tel('CFOP_saida_estadual', 'CFOP saída interno')->attrs(['class' => 'cfop'])->value(isset($item) ? $item->CFOP_saida_estadual : $naturezaPadrao->CFOP_saida_estadual); ?>

        </div>
        <div class="col-md-2 mt-3">
            <?php echo Form::tel('CFOP_saida_inter_estadual', 'CFOP saída externo')->attrs(['class' => 'cfop'])->value(isset($item) ? $item->CFOP_saida_inter_estadual : $naturezaPadrao->CFOP_saida_inter_estadual); ?>

        </div>
        

        <div class="col-md-2 mt-3">
            <?php echo Form::tel('perc_icms', '%ICMS *')->attrs(['class' => 'perc'])->value(isset($item) ? $item->perc_icms : $tributacao->icms); ?>

        </div>
        <div class="col-md-2 mt-3">
            <?php echo Form::tel('perc_pis', '%PIS *')->attrs(['class' => 'perc'])->value(isset($item) ? $item->perc_pis : $tributacao->pis); ?>

        </div>
        <div class="col-md-2 mt-3">
            <?php echo Form::tel('perc_cofins', '%COFINS *')->attrs(['class' => 'perc'])->value(isset($item) ? $item->perc_cofins : $tributacao->cofins); ?>

        </div>
        <div class="col-md-2 mt-3">
            <?php echo Form::tel('perc_ipi', '%IPI *')->attrs(['class' => 'perc'])->value(isset($item) ? $item->perc_ipi : $tributacao->ipi); ?>

        </div>

        <div class="col-md-2 mt-3">
            <?php echo Form::text('perc_iss', '% ISS *')->attrs(['class' => 'perc ignore']); ?>

        </div>
        <div class="col-md-2 mt-3">
            <?php echo Form::text('perc_reducao', '% Redução BC')->attrs(['class' => 'perc ignore']); ?>

        </div>
        <div class="col-md-2 mt-3">
            <?php echo Form::text('cBenef', 'Cod benefício')->attrs(['class' => ''])->attrs(['class' => 'ignore']); ?>

        </div>
        <div class="col-md-6 mt-3">
            <?php echo Form::select('origem', 'Origem', App\Models\Produto::origens())->attrs(['class' => 'select2']); ?>

        </div>
        <div class="col-md-3 mt-3">
            <?php echo Form::text('perc_icms_interestadual', '%ICMS interestadual')->attrs(['class' => 'ignore']); ?>

        </div>
        <div class="col-md-2 mt-3">
            <?php echo Form::text('perc_icms_interno', '%ICMS interno')->attrs(['class' => 'ignore']); ?>

        </div>
        <div class="col-md-2 mt-3">
            <?php echo Form::text('perc_fcp_interestadual', '%FCP interestad.')->attrs(['class' => 'ignore']); ?>

        </div>
        
        <div class="col-md-2 mt-3">
            <?php echo Form::tel('CFOP_entrada_estadual', 'CFOP entrada interno')->attrs(['class' => 'cfop ignore'])->value(isset($item) ? $item->CFOP_entrada_estadual : $naturezaPadrao->CFOP_entrada_estadual); ?>

        </div>
        <div class="col-md-2 mt-3">
            <?php echo Form::text('CFOP_entrada_inter_estadual', 'CFOP entrada externo')->attrs(['class' => 'cfop ignore'])->value(isset($item) ? $item->CFOP_entrada_inter_estadual : $naturezaPadrao->CFOP_entrada_inter_estadual); ?>

        </div>
        <div class="col-md-12 mt-3">
            <?php echo Form::select('CST_CSOSN_entrada', 'CSOSN entrada', App\Models\Produto::listaCSTCSOSN())->attrs([
            'class' => 'select2',
            ]); ?>

        </div>
        
        <div class="col-md-6 mt-3">
            <?php echo Form::select(
            'CST_PIS_entrada',
            'CST PIS Entrada *',
            App\Models\Produto::listaCST_PIS_COFINS_Entrada(),
            )->attrs(['class' => 'select2']); ?>

        </div>
        <div class="col-md-6 mt-3">
            <?php echo Form::select(
            'CST_COFINS_entrada',
            'CST COFINS Entrada *',
            App\Models\Produto::listaCST_PIS_COFINS_Entrada(),
            )->attrs(['class' => 'select2']); ?>

        </div>
        <div class="col-md-6 mt-3">
            <?php echo Form::select('CST_IPI_entrada', 'CST IPI entrada', App\Models\Produto::listaCST_IPI_Entrada())->attrs([
            'class' => 'select2',
            ]); ?>

        </div>
    </div>

    <input type="hidden" class="divisoes ignore" id="divisoes" value="<?php echo e(json_encode($divisoes)); ?>" name="">
    <input type="hidden" class="subDivisoes ignore" id="subDivisoes" value="<?php echo e(json_encode($subDivisoes)); ?>" name="">

    <div class="col-12 mt-5">
        <?php if(isset($not_submit)): ?>
        <button type="button" class="btn btn-primary px-5" id="btn-store-produto">Salvar</button>
        <?php else: ?>
        <button type="submit" class="btn btn-primary px-5">Salvar</button>
        <?php endif; ?>
    </div>
</div>

<?php $__env->startSection('js'); ?>
<script src="/js/grade.js"></script>
<script src="/js/product.js"></script>
<script type="text/javascript" src="/assets/js/jquery.uploadPreview.min.js"></script>

<script>
    ClassicEditor
        .create(document.querySelector('#info_tecnica_composto'))
        .then(editor => {
            editor.ui.view.editable.element.style.height = '300px';
        })
        .catch(error => {});

    ClassicEditor
        .create(document.querySelector('#descricao-ecommerce'))
        .then(editor => {
            editor.ui.view.editable.element.style.height = '300px';
        })
        .catch(error => {});

</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('modals._categoria', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php echo $__env->make('modals._sub_categoria', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php echo $__env->make('modals._marca', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php echo $__env->make('modals._grade', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php echo $__env->make('modals._grade2', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php /**PATH /home2/wesl4494/public_html/padariacasadopao.com/resources/views/produtos/_forms.blade.php ENDPATH**/ ?>