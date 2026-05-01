<tr class="line-product">
    <input readonly type="hidden" name="key" class="form-control" value="<?php echo e($product->key); ?>">
    <input readonly type="hidden" name="produto_id[]" class="form-control" value="<?php echo e($product->id); ?>">

    <td>
        <input readonly type="text" name="produto_nome[]" class="form-control produto-nome" value="<?php echo e($product->nome); ?>">
    </td>
    <td class="datatable-cell">
        <div class="form-group mb-2">
            <div class="input-group">
                <div class="input-group-prepend">
                    <button id="btn-subtrai" class="btn btn-danger" type="button">-</button>
                </div>
                <input type="tel" readonly class="form-control qtd" name="quantidade[]" value="<?php echo e($qtd); ?>">
                <div class="input-group-append">
                    <button class="btn btn-success" id="btn-incrementa" type="button">+</button>
                </div>
            </div>
        </div>
    </td>

    <td>
        <input readonly type="tel" name="valor_unitario[]" class="form-control value-unit" value="<?php echo e(__moeda($value_unit)); ?>">
    </td>
    <td>
        <input readonly type="tel" name="subtotal_item[]" class="form-control subtotal-item" value="<?php echo e(__moeda($sub_total)); ?>">
    </td>
</tr>
<?php /**PATH /home2/wesl4494/public_html/padariacasadopao.com/resources/views/frontBox/partials/row_frontBox.blade.php ENDPATH**/ ?>