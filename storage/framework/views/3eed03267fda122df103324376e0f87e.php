<div class="row g-3">
    <div class="col-md-2">
        <?php echo Form::text('referencia', 'Referência')->required(); ?>

    </div>

    <div class="col-md-4">
        <label for="inp-fornecedor_id" class="required">Fornecedor</label>
        <div class="input-group">
            <select required class="form-control select2 fornecedor_id <?php if($errors->has('fornecedor_id')): ?> is-invalid <?php endif; ?>" name="fornecedor_id" id="inp-fornecedor_id">
                <?php if(isset($item)): ?>
                <option value="<?php echo e($item->fornecedor_id); ?>"><?php echo e($item->fornecedor->razao_social); ?></option>
                <?php endif; ?>
            </select>
            <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#modal-fornecedor">
                <i class="bx bx-plus"></i></button>

            <?php if($errors->has('fornecedor_id')): ?>
            <div class="invalid-feedback">
                <?php echo e($errors->first('fornecedor_id')); ?>

            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-md-2">
        <?php echo Form::select('categoria_id', 'Categoria', $categorias->pluck('nome', 'id')->all())
        ->attrs(['class' => 'form-select'])->required(); ?>

    </div>

    <div class="col-md-2">
        <?php echo Form::tel('valor_integral', 'Valor')
        ->attrs(['class' => 'moeda'])->required()
        ->value(isset($item) ? __moeda($item->valor_integral) : ''); ?>

    </div>

    <div class="col-md-2">
        <?php echo Form::date('data_vencimento', 'Vencimento')->required(); ?>

    </div>

    <div class="col-md-2">
        <?php echo Form::select('tipo_pagamento', 'Tipo de pagamento', App\Models\ContaPagar::tiposPagamento())
        ->attrs(['class' => 'form-select']); ?>

    </div>

    <div class="col-md-2">
        <?php echo Form::select('status', 'Conta paga', ['0' => 'Não', '1' => 'Sim'])
        ->attrs(['class' => 'form-select']); ?>

    </div>

    <?php if(isset($item)): ?>
    <?php echo __view_locais_select_edit("Local", $item->filial_id); ?>

    <?php else: ?>
    <?php echo __view_locais_select(); ?>

    <?php endif; ?>

    <hr>

    <?php if(!isset($item)): ?>
    <p class="text-danger">
        *Campo abaixo deve ser preenchido se ouver recorrência para este registro
    </p>

    <div class="col-md-2">
        <?php echo Form::tel('recorrencia', 'Data')
        ->attrs(['data-mask' => '00/00'])
        ->placeholder('mm/aa'); ?>

    </div>
    <?php endif; ?>

    <div class="row tbl-recorrencia d-none mt-2">
    </div>

    <div class="col-12">
        <button type="submit" class="btn btn-primary px-5 float-end">Salvar</button>
    </div>
</div>
<?php /**PATH /home2/wesl4494/public_html/padariacasadopao.com/resources/views/conta_pagar/_forms.blade.php ENDPATH**/ ?>