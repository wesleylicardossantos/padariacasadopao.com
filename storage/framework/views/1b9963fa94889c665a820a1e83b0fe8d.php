<?php $__env->startSection('content'); ?>
<div class="page-content">
    <div class="card"><div class="card-body p-4">
        <div class="d-flex justify-content-between mb-3 flex-wrap gap-2">
            <div>
                <h5 class="mb-0">Gerar documento inteligente RH</h5>
                <small class="text-muted">Use template jurídico BR, IA integrada e salvamento automático no dossiê.</small>
            </div>
            <a class="btn btn-secondary" href="<?php echo e(route('rh.documentos.index')); ?>">Voltar</a>
        </div>

        <?php echo Form::open()->post()->route('rh.documentos.store')->multipart(); ?>

        <div class="row g-3">
            <div class="col-md-6"><?php echo Form::select('funcionario_id', 'Funcionário', ['' => 'Selecione'] + $funcionarios->pluck('nome','id')->all())->attrs(['class' => 'select2'])->required(); ?></div>
            <div class="col-md-3"><?php echo Form::text('tipo', 'Tipo do documento', old('tipo', 'contrato_trabalho'))->required(); ?></div>
            <div class="col-md-3"><?php echo Form::date('validade', 'Validade'); ?></div>
            <div class="col-md-8"><?php echo Form::text('nome', 'Nome do documento', old('nome', 'Contrato Individual de Trabalho'))->required(); ?></div>
            <div class="col-md-4"><?php echo Form::select('categoria', 'Categoria', ['contrato' => 'Contrato', 'rescisao' => 'Rescisão', 'disciplinar' => 'Disciplinar', 'juridico' => 'Jurídico', 'empresa' => 'Empresa', 'outro' => 'Outro'], old('categoria', 'contrato')); ?></div>

            <div class="col-md-8">
                <label class="form-label">Template jurídico</label>
                <select name="template_id" class="form-control select2">
                    <option value="">Selecione</option>
                    <?php $__currentLoopData = $templates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $template): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($template->id); ?>"><?php echo e($template->categoria); ?> - <?php echo e($template->nome); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <div class="form-check form-switch mb-2">
                    <input class="form-check-input" type="checkbox" value="1" name="usar_ia" id="usar_ia" checked>
                    <label class="form-check-label" for="usar_ia">Usar IA integrada</label>
                </div>
            </div>

            <div class="col-md-6"><?php echo Form::text('tipo_rescisao', 'Tipo de rescisão / natureza', old('tipo_rescisao')); ?></div>
            <div class="col-md-6"><?php echo Form::date('data_rescisao', 'Data de rescisão (se houver)'); ?></div>
            <div class="col-md-12"><?php echo Form::textarea('motivo_documento', 'Motivo / fato gerador')->attrs(['rows' => 3]); ?></div>
            <div class="col-md-12"><label class="form-label">Instruções para IA</label><textarea name="instrucoes_ia" rows="4" class="form-control"><?php echo e(old('instrucoes_ia', 'Formalize juridicamente o texto, mantenha linguagem profissional e não invente dados não fornecidos.')); ?></textarea></div>

            <div class="col-12"><hr><h6 class="mb-2">Campos profissionais do TRCT</h6><small class="text-muted">Preencha os dados abaixo para gerar o termo de rescisão no layout oficial profissional.</small></div>
            <div class="col-md-4"><?php echo Form::text('funcionario_pis', 'PIS / PASEP', old('funcionario_pis')); ?></div>
            <div class="col-md-4"><?php echo Form::text('funcionario_data_nascimento', 'Data de nascimento', old('funcionario_data_nascimento'))->attrs(['placeholder' => 'dd/mm/aaaa']); ?></div>
            <div class="col-md-4"><?php echo Form::text('funcionario_mae', 'Nome da mãe', old('funcionario_mae')); ?></div>
            <div class="col-md-4"><?php echo Form::text('aviso_previo_data', 'Data do aviso prévio', old('aviso_previo_data'))->attrs(['placeholder' => 'dd/mm/aaaa']); ?></div>
            <div class="col-md-4"><?php echo Form::text('codigo_afastamento', 'Código de afastamento', old('codigo_afastamento')); ?></div>
            <div class="col-md-4"><?php echo Form::text('pensao_alimenticia', 'Pensão alimentícia (%)', old('pensao_alimenticia')); ?></div>
            <div class="col-md-4"><?php echo Form::text('categoria_trabalhador', 'Categoria do trabalhador', old('categoria_trabalhador')); ?></div>
            <div class="col-md-4"><?php echo Form::text('local_recebimento', 'Local de recebimento', old('local_recebimento')); ?></div>
            <div class="col-md-4"><?php echo Form::text('data_recebimento', 'Data do recebimento', old('data_recebimento'))->attrs(['placeholder' => 'dd/mm/aaaa']); ?></div>
            <div class="col-md-12"><label class="form-label">Texto de homologação</label><textarea name="homologacao_texto" rows="3" class="form-control"><?php echo e(old('homologacao_texto', 'Foi prestado gratuitamente assistência ao trabalhador nos termos do art. 477, § 1º da Consolidação das Leis do Trabalho – CLT, sendo comprovado neste ato o efetivo pagamento das verbas rescisórias acima especificadas.')); ?></textarea></div>
            <div class="col-md-6"><?php echo Form::text('orgao_homologador', 'Órgão homologador', old('orgao_homologador')); ?></div>
            <div class="col-md-6"><?php echo Form::text('carimbo_assistente', 'Assistente / carimbo', old('carimbo_assistente')); ?></div>

            <div class="col-12"><h6 class="mb-2 mt-2">Verbas rescisórias principais</h6><small class="text-muted">Os valores abaixo alimentam a tabela do termo profissional.</small></div>
            <div class="col-md-3"><?php echo Form::text('verba_29_referencia', '29 - Referência', old('verba_29_referencia', 'Indenizado')); ?></div>
            <div class="col-md-3"><?php echo Form::text('verba_29_aviso_previo', '29 - Aviso prévio', old('verba_29_aviso_previo')); ?></div>
            <div class="col-md-3"><?php echo Form::text('verba_30_referencia', '30 - Referência', old('verba_30_referencia')); ?></div>
            <div class="col-md-3"><?php echo Form::text('verba_30_saldo_salario', '30 - Saldo de salário', old('verba_30_saldo_salario')); ?></div>
            <div class="col-md-3"><?php echo Form::text('verba_31_referencia', '31 - Referência', old('verba_31_referencia')); ?></div>
            <div class="col-md-3"><?php echo Form::text('verba_31_decimo_salario', '31 - 13º salário', old('verba_31_decimo_salario')); ?></div>
            <div class="col-md-3"><?php echo Form::text('verba_32_referencia', '32 - Referência', old('verba_32_referencia')); ?></div>
            <div class="col-md-3"><?php echo Form::text('verba_32_decimo_indenizado', '32 - 13º indenizado', old('verba_32_decimo_indenizado')); ?></div>
            <div class="col-md-3"><?php echo Form::text('verba_34_referencia', '34 - Referência', old('verba_34_referencia')); ?></div>
            <div class="col-md-3"><?php echo Form::text('verba_34_ferias_proporcionais', '34 - Férias proporcionais', old('verba_34_ferias_proporcionais')); ?></div>
            <div class="col-md-3"><?php echo Form::text('verba_42_ferias_indenizada', '42 - Férias indenizada', old('verba_42_ferias_indenizada')); ?></div>
            <div class="col-md-3"><?php echo Form::text('verba_43_terco_ferias', '43 - 1/3 férias', old('verba_43_terco_ferias')); ?></div>
            <div class="col-md-3"><?php echo Form::text('verba_44_referencia', '44 - Referência FGTS', old('verba_44_referencia')); ?></div>
            <div class="col-md-3"><?php echo Form::text('verba_44_fgts', '44 - FGTS', old('verba_44_fgts')); ?></div>
            <div class="col-md-3"><?php echo Form::text('verba_45_multa_fgts', '45 - 40% FGTS', old('verba_45_multa_fgts')); ?></div>
            <div class="col-md-3"><?php echo Form::text('verba_46_total_bruto', '46 - Total bruto', old('verba_46_total_bruto')); ?></div>
            <div class="col-md-3"><?php echo Form::text('verba_54_total_deducoes', '54 - Total deduções', old('verba_54_total_deducoes')); ?></div>
            <div class="col-md-3"><?php echo Form::text('verba_55_liquido_receber', '55 - Líquido a receber', old('verba_55_liquido_receber')); ?></div>

            <div class="col-12"><hr><h6 class="mb-2">Campos específicos do contrato</h6><small class="text-muted">Esses dados alimentam o template jurídico e evitam colchetes/variáveis sobrando no PDF.</small></div>
            <div class="col-md-4">
                <label class="form-label">Tipo de contrato</label>
                <select name="tipo_contrato" class="form-control" onchange="this.form.querySelector('[name=tipo_contrato_label]').value=this.options[this.selectedIndex].text.toLowerCase();">
                    <option value="indeterminado" <?php echo e(old('tipo_contrato', 'indeterminado') === 'indeterminado' ? 'selected' : ''); ?>>por prazo indeterminado</option>
                    <option value="determinado" <?php echo e(old('tipo_contrato') === 'determinado' ? 'selected' : ''); ?>>por prazo determinado</option>
                    <option value="intermitente" <?php echo e(old('tipo_contrato') === 'intermitente' ? 'selected' : ''); ?>>intermitente</option>
                </select>
                <input type="hidden" name="tipo_contrato_label" value="<?php echo e(old('tipo_contrato_label', 'por prazo indeterminado')); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Regime de trabalho</label>
                <select name="regime" class="form-control" onchange="this.form.querySelector('[name=regime_trabalho]').value=this.options[this.selectedIndex].text;">
                    <option value="presencial" <?php echo e(old('regime', 'presencial') === 'presencial' ? 'selected' : ''); ?>>Presencial</option>
                    <option value="teletrabalho" <?php echo e(old('regime') === 'teletrabalho' ? 'selected' : ''); ?>>Teletrabalho</option>
                </select>
                <input type="hidden" name="regime_trabalho" value="<?php echo e(old('regime_trabalho', 'Presencial')); ?>">
            </div>
            <div class="col-md-4"><?php echo Form::text('periodicidade_pagamento', 'Periodicidade do pagamento', old('periodicidade_pagamento', 'Mensal')); ?></div>
            <div class="col-md-6"><?php echo Form::text('forma_pagamento_documento', 'Forma de pagamento', old('forma_pagamento_documento', 'depositado em conta de titularidade do(a) empregado(a)')); ?></div>
            <div class="col-md-2"><?php echo Form::text('banco', 'Banco', old('banco')); ?></div>
            <div class="col-md-2"><?php echo Form::text('agencia', 'Agência', old('agencia')); ?></div>
            <div class="col-md-2"><?php echo Form::text('conta_corrente', 'Conta', old('conta_corrente')); ?></div>
            <div class="col-md-6"><?php echo Form::text('foro_cidade', 'Foro / cidade', old('foro_cidade', 'São Luís')); ?></div>
            <div class="col-md-6"><?php echo Form::text('local_trabalho', 'Local de trabalho', old('local_trabalho', 'Sede da empresa')); ?></div>
            <div class="col-md-4"><?php echo Form::text('funcionario_nacionalidade', 'Nacionalidade', old('funcionario_nacionalidade', 'brasileiro(a)')); ?></div>
            <div class="col-md-4"><?php echo Form::text('funcionario_estado_civil', 'Estado civil', old('funcionario_estado_civil')); ?></div>
            <div class="col-md-4"><?php echo Form::text('funcionario_profissao', 'Profissão', old('funcionario_profissao')); ?></div>
            <div class="col-md-6"><?php echo Form::text('funcionario_ctps', 'CTPS', old('funcionario_ctps')); ?></div>
            <div class="col-md-6"><?php echo Form::text('funcionario_ctps_serie', 'Série CTPS', old('funcionario_ctps_serie')); ?></div>
            <div class="col-md-6"><?php echo Form::text('empresa_tipo_pessoa', 'Tipo de pessoa da empresa', old('empresa_tipo_pessoa', 'pessoa jurídica de direito privado')); ?></div>
            <div class="col-md-6"><?php echo Form::text('empresa_representante_legal', 'Representante legal', old('empresa_representante_legal')); ?></div>
            <div class="col-md-6"><?php echo Form::text('empresa_representante_cpf', 'CPF do representante legal', old('empresa_representante_cpf')); ?></div>
            <div class="col-md-6">
                <label class="form-label">Contribuição sindical</label>
                <select name="autoriza_contribuicao_sindical" class="form-control">
                    <option value="nao" <?php echo e(old('autoriza_contribuicao_sindical', 'nao') === 'nao' ? 'selected' : ''); ?>>Não autoriza</option>
                    <option value="sim" <?php echo e(old('autoriza_contribuicao_sindical') === 'sim' ? 'selected' : ''); ?>>Autoriza</option>
                </select>
            </div>
            <div class="col-md-12"><label class="form-label">Jornada / descrição</label><textarea name="jornada_descricao" rows="3" class="form-control"><?php echo e(old('jornada_descricao', '44 (quarenta e quatro) horas semanais, com intervalos e descansos legais.')); ?></textarea></div>
            <div class="col-md-12"><label class="form-label">Prazo do contrato</label><textarea name="prazo_contrato_descricao" rows="3" class="form-control"><?php echo e(old('prazo_contrato_descricao', 'O presente contrato é válido por prazo indeterminado.')); ?></textarea></div>
            <div class="col-md-12"><label class="form-label">Atividades do empregado</label><textarea name="funcionario_atividades" rows="3" class="form-control"><?php echo e(old('funcionario_atividades', 'Atividades inerentes à função e outras compatíveis com a condição pessoal do(a) empregado(a).')); ?></textarea></div>
            <div class="col-md-12"><label class="form-label">Benefícios</label><textarea name="beneficios_descricao" rows="2" class="form-control"><?php echo e(old('beneficios_descricao', 'Conforme política interna da empresa e instrumento coletivo aplicável.')); ?></textarea></div>
            <div class="col-md-12"><label class="form-label">Multa/observação de confidencialidade</label><textarea name="confidencialidade_multa" rows="2" class="form-control"><?php echo e(old('confidencialidade_multa')); ?></textarea></div>
            <div class="col-md-12"><label class="form-label">Observações adicionais</label><textarea name="observacao" rows="4" class="form-control"><?php echo e(old('observacao')); ?></textarea></div>

            <div class="col-12"><hr><h6 class="mb-2">Upload manual opcional</h6><small class="text-muted">Se anexar um arquivo, o sistema registra manualmente no dossiê sem geração por IA.</small></div>
            <div class="col-md-12"><?php echo Form::file('arquivo', 'Arquivo')->attrs(['class' => 'form-control']); ?></div>
        </div>
        <div class="mt-3 d-flex gap-2 flex-wrap">
            <button class="btn btn-primary">Gerar e salvar no dossiê</button>
            <a class="btn btn-outline-secondary" href="<?php echo e(route('rh.documentos.templates.index')); ?>">Gerenciar templates</a>
        </div>
        <?php echo Form::close(); ?>

    </div></div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('default.layout',['title' => 'RH - Gerar Documento Inteligente'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home2/wesl4494/public_html/padariacasadopao.com/resources/views/rh/documentos/create.blade.php ENDPATH**/ ?>