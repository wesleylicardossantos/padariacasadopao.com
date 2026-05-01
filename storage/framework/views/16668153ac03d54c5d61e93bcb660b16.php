<div class="row g-3">
    <div class="col-md-4">
        <?php echo Form::text('nome', 'Nome')->required(); ?>

    </div>
    <div class="col-md-4">
        <?php echo Form::select('tipo', 'Tipo', ['receber' => 'Vendas', 'pagar' => 'Compras'])
        ->attrs(['class' => 'select2']); ?>

    </div>
    <div class="col-12">
        <button type="submit" class="btn btn-primary px-5">Salvar</button>
    </div>
</div>
<?php /**PATH /home2/wesl4494/public_html/padariacasadopao.com/resources/views/categorias_conta/_forms.blade.php ENDPATH**/ ?>