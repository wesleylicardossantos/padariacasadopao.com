<?php
$menu = new App\Helpers\Menu();
$menu = $menu->preparaMenu();

$normalizePath = function ($url) {
    if (empty($url) || $url === 'javascript:;') {
        return '';
    }

    $path = parse_url($url, PHP_URL_PATH) ?: '';
    return trim($path, '/');
};

$isSubActive = function ($item) use ($normalizePath) {
    if (isset($item['rota_ativa']) || empty($item['rota'])) {
        return false;
    }

    $path = $normalizePath($item['rota']);
    if ($path === '') {
        return false;
    }

    if (!empty($item['exact_match'])) {
        return request()->path() === $path;
    }

    return request()->is($path) || request()->is($path . '/*');
};
?>

<?php $__currentLoopData = $menu; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<?php if(!isset($m['ativo']) || $m['ativo']): ?>
<?php
    $subAtivo = false;
    foreach ($m['subs'] as $subMenu) {
        if ($isSubActive($subMenu)) {
            $subAtivo = true;
            break;
        }
    }

    $menuAtivo = ($m['titulo'] == ($rotaAtiva ?? null)) || $subAtivo;
?>
<li class="<?php echo e($menuAtivo ? 'mm-active' : ''); ?>">
	<a href="javascript:;" class="has-arrow <?php echo e($menuAtivo ? 'mm-active' : ''); ?>" aria-expanded="<?php echo e($menuAtivo ? 'true' : 'false'); ?>">
		<div class="parent-icon"><i class='<?php echo e($m['icone']); ?>'></i>
		</div>
		<div class="menu-title"><?php echo e($m['titulo']); ?></div>
	</a>
	<ul class="<?php echo e($menuAtivo ? 'mm-show' : ''); ?>">	
		<?php $__currentLoopData = $m['subs']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
		<?php if(!isset($i['rota_ativa']) && $i['rota'] != ''): ?>
		<?php ($itemAtivo = $isSubActive($i)); ?>
		<li class="<?php echo e($itemAtivo ? 'mm-active' : ''); ?>"><a class="<?php echo e($itemAtivo ? 'mm-active' : ''); ?>" <?php if(isset($i['target'])): ?> target="_blank" <?php endif; ?> href="<?php echo e($i['rota']); ?>" title="<?php echo e($i['nome']); ?>">
			<i class="bx bx-circle" style="font-size: 10px;"></i><span><?php echo e($i['nome']); ?></span></a>
		</li>
		<?php endif; ?>
		<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
	</ul>
</li>
<?php endif; ?>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php /**PATH /home2/wesl4494/public_html/padariacasadopao.com/resources/views/default/menu.blade.php ENDPATH**/ ?>