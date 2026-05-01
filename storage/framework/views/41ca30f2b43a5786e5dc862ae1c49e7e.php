<?php $__env->startSection('content'); ?>
    <div class="page-content">
        <div class="card ">
            <?php echo Form::open()->post()->route('compraManual.store'); ?>

            <?php echo $__env->make('compra_manual._forms', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            <?php echo Form::close(); ?>

        </div>
    </div>

<?php $__env->startSection('js'); ?>
    <script>
        $(function() {
            $('[data-bs-toggle="popover"]').popover();
        });

        function selectDiv2(ref) {
            $('.btn-outline-primary').removeClass('active')
            if (ref == 'frete') {
                $('.div-frete').removeClass('d-none')
                $('.div-itens').addClass('d-none')
                $('.div-pagamento').addClass('d-none')
                $('.btn-frete').addClass('active')
            } else if (ref == 'itens') {
                $('.div-frete').addClass('d-none')
                $('.div-itens').removeClass('d-none')
                $('.div-pagamento').addClass('d-none')
                $('.btn-itens').addClass('active')
            } else {
                $('.div-frete').addClass('d-none')
                $('.div-itens').addClass('d-none')
                $('.div-pagamento').removeClass('d-none')
                $('.btn-pagamento').addClass('active')
            }
        }
    </script>

    <script type="text/javascript" src="/js/compra.js"></script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('modals._produto', ['not_submit' => true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php echo $__env->make('modals._fornecedor', ['not_submit' => true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('default.layout', ['title' => 'Nova Compra'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home2/wesl4494/public_html/padariacasadopao.com/resources/views/compra_manual/create.blade.php ENDPATH**/ ?>