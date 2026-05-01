<?php
    $messages = [];
?>

<?php if($errors->any()): ?>
    <div class="flash-stack mb-3">
        <div class="alert alert-danger border-0 bg-danger alert-dismissible fade show py-2 shadow-sm" role="alert">
            <div class="d-flex align-items-start gap-2">
                <i class="bx bx-x-circle mt-1"></i>
                <div>
                    <strong>Revise os dados informados.</strong>
                    <ul class="mb-0 ps-3">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    </div>
<?php endif; ?>
<?php /**PATH /home2/wesl4494/public_html/padariacasadopao.com/resources/views/components/flash-message.blade.php ENDPATH**/ ?>