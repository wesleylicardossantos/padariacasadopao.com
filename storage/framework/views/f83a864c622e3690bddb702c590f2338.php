<div class="row g-3">
    <div class="col-md-2">
        <?php echo Form::tel('icms', '%ICMS')->required()
        ->attrs(['class' => 'form-control perc']); ?>

    </div>
    <div class="col-md-2">
        <?php echo Form::tel('pis', '%PIS')->required()
        ->attrs(['class' => 'form-control perc']); ?>

    </div>
    <div class="col-md-2">
        <?php echo Form::tel('cofins', '%COFINS')->required()
        ->attrs(['class' => 'form-control perc']); ?>

    </div>
    <div class="col-md-2">
        <?php echo Form::tel('ipi', '%IPI')->required()
        ->attrs(['class' => 'form-control perc']); ?>

    </div>

    <div class="col-md-3">
        <?php echo Form::tel('ncm_padrao', 'NCM padrão')->required()
        ->attrs(['class' => 'form-control ncm']); ?>

    </div>

    <div class="col-md-3">
        <?php echo Form::select('regime', 'Regime', $regimes)->required()
        ->attrs(['class' => 'form-select']); ?>

    </div>

    <div class="col-md-3 perc_cred" <?php if($item != null && $item->regime != 0): ?> style="display: block" <?php else: ?> style="display: none" <?php endif; ?>>
        <?php echo Form::text('perc_ap_cred', '% Aproveitamento crédito')
        ->attrs(['class' => 'form-control perc']); ?>

    </div>

    

    <div class="col-12">
        <button type="submit" class="btn btn-primary px-5">Salvar</button>
    </div>
</div>


<?php /**PATH /home2/wesl4494/public_html/padariacasadopao.com/resources/views/tributos/_forms.blade.php ENDPATH**/ ?>