<div class="row g-3">
   

    <div class="col-md-2">
        <?php echo Form::tel('valor_pago', 'Valor')
        ->attrs(['class' => 'moeda'])
        ->required(); ?>

    </div>

    <div class="col-md-2">
        <?php echo Form::date('data_pagamento', 'Data do pagamento')
        ->required(); ?>

    </div>

    <div class="col-md-2">
        <?php echo Form::select('tipo_pagamento', 'Tipo de Pagamento', ['' => 'Selecione'] + App\Models\ContaPagar::tiposPagamento())
        ->attrs(['class' => 'form-select'])
        ->required(); ?>

    </div>


    <div class="col-12">
        <button type="submit" class="btn btn-primary px-5">Pagar</button>
    </div>
</div><?php /**PATH /home2/wesl4494/public_html/padariacasadopao.com/resources/views/conta_pagar/_forms_pay.blade.php ENDPATH**/ ?>