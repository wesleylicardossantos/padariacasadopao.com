<?php $__env->startSection('css'); ?>
<style type="text/css">
	.btn-file {
		position: relative;
		overflow: hidden;
	}

	.btn-file input[type=file] {
		position: absolute;
		top: 0;
		right: 0;
		min-width: 100%;
		min-height: 100%;
		font-size: 100px;
		text-align: right;
		filter: alpha(opacity=0);
		opacity: 0;
		outline: none;
		background: white;
		cursor: inherit;
		display: block;
	}
</style>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
<div class="page-content">

	<div class="card border-top border-0 border-4 border-primary">
		<div class="card-body p-5">
			<a class="btn btn-primary" href="<?php echo e(route('produtos.download-modelo')); ?>">
				<i class="bx bx-download"></i>
				Baixar modelo
			</a>

			<?php echo Form::open()
			->post()
			->route('produtos.import-store')
			->multipart(); ?>

			<?php echo csrf_field(); ?>
			<div class="pl-lg-4"><br>
				<div class="card">
					<div class="card-body">
						<div class="form-group validated col-sm-10 col-lg-10">
							<label class="col-form-label">.xls/.xlsx</label>
							<div class="">
								<span class="btn btn-dark btn-file">
									Procurar arquivo<input accept=".xls, .xlsx" name="file" type="file">
								</span>
								<label class="text-info" id="filename"></label>
							</div>
						</div>
					</div>
					<div class="card-footer">
						<button class="btn btn-success">Salvar importação</button>
					</div>
				</div>
			</div>
			<?php echo Form::close(); ?>

		</div>
	</div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('default.layout',['title' => 'Importar Produtos'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home2/wesl4494/public_html/padariacasadopao.com/resources/views/produtos/import.blade.php ENDPATH**/ ?>