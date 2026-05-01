<?php $__env->startSection('content'); ?>

<?php $__env->startSection('css'); ?>
<style type="text/css">
     input[type="file"] {
        display: none;
    }

    .file-padrao label{
        padding: 10px 10px;
        width: 100%;
        background-color: #1C1F23;
        color: #FFF;
        text-transform: uppercase;
        text-align: center;
        display: block;
        margin-top: 15px;
        cursor: pointer;
        border-radius: 5px;
    }

</style>
<?php $__env->stopSection(); ?>

<div class="page-content">
    <div class="card border-top border-0 border-4 border-primary">
        <div class="card-body p-5">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    <a href="<?php echo e(route('compraFiscal.index')); ?>" type="button" class="btn btn-light btn-sm">
                        <i class="bx bx-arrow-back"></i> Voltar
                    </a>
                </div>
            </div>
            <div class="card-title d-flex align-items-center">
                <h5 class="mb-0 text-primary">Compra fiscal</h5>
            </div>
            <hr>
            <?php echo Form::open()
            ->post()
			->id('form-import')
            ->route('compraFiscal.import')
            ->multipart(); ?>

            <div class="">
                <div class="card-body">
                    <div class="col-md-3 file-padrao">
                        <?php echo Form::file('file', 'Selecione o arquivo XML')->attrs(['accept' => '.xml']); ?>

                    </div>
                    <hr>
                </div>
            </div>
            <?php echo Form::close(); ?>

        </div>
    </div>
</div>


<?php $__env->startSection('js'); ?>
<script type="text/javascript">
    $('#inp-file').change(function() {
        $('#form-import').submit();
    });
</script>

<?php $__env->stopSection(); ?>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('default.layout',['title' => 'Compra Fiscal'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home2/wesl4494/public_html/padariacasadopao.com/resources/views/compra_fiscal/index.blade.php ENDPATH**/ ?>