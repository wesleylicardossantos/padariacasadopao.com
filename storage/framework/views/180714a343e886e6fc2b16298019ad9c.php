<tr>
	<td>
		<input readonly type="tel" name="produto_id[]" class="form-control" value="<?php echo e($product->id); ?>">
	</td>
	<td>
		<input readonly type="text" name="produto_nome[]" class="form-control" value="<?php echo e($product->nome); ?>">
	</td>
	<td>
		<input readonly type="tel" name="valor_unitario[]" class="form-control" value="<?php echo e(__moeda($value_unit)); ?>">
	</td>
	<td>
		<input readonly type="tel" name="quantidade[]" class="form-control qtd-item" value="<?php echo e($qtd); ?>">
	</td>
	<td>
		<input readonly type="tel" name="subtotal_item[]" class="form-control subtotal-item" value="<?php echo e(__moeda($sub_total)); ?>">
	</td>
	<td>
		<button class="btn btn-sm btn-danger btn-delete-row">
			<i class="bx bx-trash"></i>
		</button>
	</td>
</tr><?php /**PATH /home2/wesl4494/public_html/padariacasadopao.com/resources/views/compra_manual/partials/row_product_purchase.blade.php ENDPATH**/ ?>