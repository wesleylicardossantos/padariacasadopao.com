<?php $__env->startSection('content'); ?>
<div class="page-content">
    <div class="card border-top border-0 border-4 border-primary">
        <div class="card-body p-5">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    <a href="<?php echo e(route('produtos.index')); ?>" type="button" class="btn btn-light btn-sm">
                        <i class="bx bx-arrow-back"></i> Voltar
                    </a>
                </div>
            </div>
            <hr>
            <?php echo Form::open()
            ->post()
            // ->route('produtos.store')
            ->multipart(); ?>

            <div class="pl-lg-4">
                <div class="row g-3">
                    <div class="col-md-4 mt-2">
                        <?php echo Form::select('modelo', 'Modelo da balança', App\Models\Produto::modelosBalanca())->attrs(['class' => 'form-select']); ?>

                    </div>
                    <div class="mt-5">
                        <div class="table-responsive">
                            <table class="table mb-0 table-striped">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" id="todos"></th>
                                        <th>Produto</th>
                                        <th>Referência</th>
                                        <th>Valor de venda</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><input type="checkbox" class="check_prod" name="produto_id[]" value="<?php echo e($item->id); ?>"></td>
                                        <td><?php echo e($item->nome); ?></td>
                                        <td><?php echo e($item->referencia_balanca); ?></td>
                                        <td><?php echo e(__moeda($item->valor_venda)); ?></td>
                                    </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div>
                        <button class="btn btn-info">Gerar arquivo</button>
                    </div>
                </div>
            </div>
            <?php echo Form::close(); ?>

        </div>
    </div>
</div>

<?php $__env->startSection('js'); ?>
<script type="text/javascript">
    $('#todos').click(() => {
        if ($('#todos').is(":checked")) {
            $('.check_prod').prop('checked', true);
        } else {
            $('.check_prod').prop('checked', false);
        }
    })

</script>
<?php $__env->stopSection(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('default.layout',['title' => 'Produtos balança'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home2/wesl4494/public_html/padariacasadopao.com/resources/views/produtos/balanca.blade.php ENDPATH**/ ?>