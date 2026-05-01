<?php $__env->startSection('content'); ?>
<div class="page-content">
    <div class="card"><div class="card-body p-4">
        <div class="d-flex justify-content-between mb-3 flex-wrap gap-2">
            <div>
                <h5 class="mb-0"><?php echo e($template->exists ? 'Editar template jurídico' : 'Novo template jurídico'); ?></h5>
                <small class="text-muted">Variáveis disponíveis: {{empresa_nome}}, {{funcionario_nome}}, {{funcionario_cpf}}, {{funcionario_cargo}}, {{funcionario_salario}}, {{funcionario_data_admissao}}, {{motivo_documento}}, {{observacoes_adicionais}}</small>
            </div>
            <a class="btn btn-secondary" href="<?php echo e(route('rh.documentos.templates.index')); ?>">Voltar</a>
        </div>

        <form method="POST" action="<?php echo e($action); ?>">
            <?php echo csrf_field(); ?>
            <?php if($method !== 'POST'): ?> <?php echo method_field($method); ?> <?php endif; ?>
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Nome</label><input type="text" name="nome" class="form-control" value="<?php echo e(old('nome', $template->nome)); ?>" required></div>
                <div class="col-md-3"><label class="form-label">Categoria</label><input type="text" name="categoria" class="form-control" value="<?php echo e(old('categoria', $template->categoria)); ?>" required></div>
                <div class="col-md-3"><label class="form-label">Tipo</label><input type="text" name="tipo_documento" class="form-control" value="<?php echo e(old('tipo_documento', $template->tipo_documento)); ?>" required></div>
                <div class="col-md-8"><label class="form-label">Descrição</label><input type="text" name="descricao" class="form-control" value="<?php echo e(old('descricao', $template->descricao)); ?>"></div>
                <div class="col-md-2"><label class="form-label">Versão</label><input type="text" name="versao" class="form-control" value="<?php echo e(old('versao', $template->versao ?: '1.0')); ?>"></div>
                <div class="col-md-1 d-flex align-items-end"><div class="form-check"><input class="form-check-input" type="checkbox" name="usa_ia" value="1" <?php echo e(old('usa_ia', $template->usa_ia) ? 'checked' : ''); ?>><label class="form-check-label">IA</label></div></div>
                <div class="col-md-1 d-flex align-items-end"><div class="form-check"><input class="form-check-input" type="checkbox" name="ativo" value="1" <?php echo e(old('ativo', $template->ativo ?? true) ? 'checked' : ''); ?>><label class="form-check-label">Ativo</label></div></div>
                <div class="col-md-12"><label class="form-label">Conteúdo HTML</label><textarea name="conteudo_html" rows="18" class="form-control" required><?php echo e(old('conteudo_html', $template->conteudo_html)); ?></textarea></div>
            </div>
            <div class="mt-3"><button class="btn btn-primary">Salvar template</button></div>
        </form>
    </div></div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('default.layout',['title' => 'RH - Template Jurídico'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home2/wesl4494/public_html/padariacasadopao.com/resources/views/rh/documentos/templates/form.blade.php ENDPATH**/ ?>