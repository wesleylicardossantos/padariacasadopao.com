<?php if($contaReceber > 0): ?>
<a class="dropdown-item alert-item" href="<?php echo e(route('conta-receber.index')); ?>">
	<div class="d-flex align-items-center">
		<div class="notify bg-light-success text-success"><i class="bx bx-money"></i>
		</div>
		<div class="flex-grow-1">
			<h6 class="msg-name">Conta a receber</h6>
			<small>em atraso</small>
			<p class="msg-info">R$ <?php echo e(__moeda($contaReceber)); ?></p>
		</div>
	</div>
</a>
<?php endif; ?>

<?php if($contaPagar > 0): ?>
<a class="dropdown-item alert-item" href="<?php echo e(route('conta-pagar.index')); ?>">
	<div class="d-flex align-items-center">
		<div class="notify bg-light-danger text-danger"><i class="bx bx-money"></i>
		</div>
		<div class="flex-grow-1">
			<h6 class="msg-name">Conta a pagar</h6>
			<small>em atraso</small>
			<p class="msg-info">R$ <?php echo e(__moeda($contaPagar)); ?></p>
		</div>
	</div>
</a>
<?php endif; ?>

<?php if(sizeof($produtosComAlertaEstoque) > 0): ?>
<?php $__currentLoopData = $produtosComAlertaEstoque; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<a class="dropdown-item alert-item" href="<?php echo e(route('produtos.index')); ?>">
	<div class="d-flex align-items-center">
		<div class="notify bg-light-danger text-danger"><i class="bx bx-money"></i>
		</div>
		<div class="flex-grow-1">
			<h6 class="msg-name">Produto com alerta em estoque</h6>
			<small></small>
			<p class="msg-info"><?php echo e($p['nome']); ?> - estoque: <?php echo e($p['estoque']); ?></p>
		</div>
	</div>
</a>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endif; ?><?php /**PATH /home2/wesl4494/public_html/padariacasadopao.com/resources/views/notificacoes/index.blade.php ENDPATH**/ ?>