<?php $__env->startSection('content'); ?>
<div class="page-content">
	<div class="card border-top border-0 border-4 border-primary">
		<div class="card-body p-5">
			<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
				<div class="ms-auto">
					<a href="<?php echo e(route('conta-pagar.index')); ?>" type="button" class="btn btn-light btn-sm">
						<i class="bx bx-arrow-back"></i> Voltar
					</a>
				</div>
			</div>
			<div class="card-title d-flex align-items-center">
				<h5 class="mb-0 text-primary">Pagar conta</h5>
			</div>
			<hr>
			
			<?php echo Form::open()
			->put()
			->route('conta-pagar.payPut', [$item->id]); ?>

			<div class="pl-lg-4">
				<div class="row">
					<div class="col-md-6">
						<h6>Data de cadastro: <strong class=""><?php echo e(__data_pt($item->created_at)); ?></strong></h6>
						<h6>Valor: <strong class="">R$ <?php echo e(__moeda($item->valor_integral)); ?></strong></h6>

					</div>
					<div class="col-md-6">
						<h6>Data de vencimento: <strong class=""><?php echo e(__data_pt($item->data_vencimento, false)); ?></strong></h6>
						<h6>Referência: <strong class=""><?php echo e($item->referencia); ?></strong></h6>

					</div>
				</div>
				<?php echo $__env->make('conta_pagar._forms_pay', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
			</div>
			<?php echo Form::close(); ?>

		</div>
	</div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('default.layout',['title' => 'Pagar Conta'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home2/wesl4494/public_html/padariacasadopao.com/resources/views/conta_pagar/pay.blade.php ENDPATH**/ ?>