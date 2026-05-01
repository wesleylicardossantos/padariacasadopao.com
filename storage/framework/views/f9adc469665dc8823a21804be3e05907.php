<?php $__env->startSection('content'); ?>
<div class="page-content">
	<div class="card border-top border-0 border-4 border-primary">
		<div class="card-body p-5">
			<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
				<div class="ms-auto">
					<a href="<?php echo e(route('categoria-conta.index')); ?>" type="button" class="btn btn-light btn-sm">
						<i class="bx bx-arrow-back"></i> Voltar
					</a>
				</div>
			</div>
			<div class="card-title d-flex align-items-center">
				<h5 class="mb-0 text-primary">Nova categoria de conta</h5>
			</div>
			<hr>

			<?php echo Form::open()
			->post()
			->autocomplete('off')
			->route('categoria-conta.store')
			->multipart(); ?>

			<div class="pl-lg-4">
				<?php echo $__env->make('categorias_conta._forms', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
			</div>
			<?php echo Form::close(); ?>

		</div>
	</div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('default.layout',['title' => 'Nova categoria de Conta'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home2/wesl4494/public_html/padariacasadopao.com/resources/views/categorias_conta/create.blade.php ENDPATH**/ ?>