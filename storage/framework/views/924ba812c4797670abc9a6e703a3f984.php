<?php
    $fmt = $fmt ?? fn ($v) => number_format((float) ($v ?? 0), 2, ',', '.');
    $texto = $texto ?? fn ($v, $fallback = '') => isset($v) && $v !== '' && $v !== null ? $v : $fallback;
?>
<div class="voucher" style="width:100%; page-break-inside:avoid; break-inside:avoid;">
    <div class="title">RECIBO DE PAGAMENTO DE SALÁRIO</div>
    <div class="top-line"></div>

    <div class="section">
        <div class="section-title">Dados da empresa</div>
        <table>
            <tr>
                <td colspan="7">
                    <span class="label">Empresa</span>
                    <span class="value big"><?php echo e($texto($empresaNome)); ?></span>
                </td>
                <td colspan="2">
                    <span class="label">CNPJ</span>
                    <span class="value"><?php echo e($texto($empresaDoc)); ?></span>
                </td>
                <td colspan="1">
                    <span class="label">Competência</span>
                    <span class="value strong"><?php echo e($competencia); ?></span>
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Dados do colaborador</div>
        <table>
            <tr>
                <td colspan="8">
                    <span class="label">Nome</span>
                    <span class="value big"><?php echo e($texto($funcionario->nome)); ?></span>
                </td>
                <td colspan="2">
                    <span class="label">CPF</span>
                    <span class="value"><?php echo e($texto($funcionario->cpf)); ?></span>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <span class="label">Código</span>
                    <span class="value"><?php echo e($texto($funcionario->id)); ?></span>
                </td>
                <td colspan="3">
                    <span class="label">Função / Cargo</span>
                    <span class="value"><?php echo e($texto($cargo)); ?></span>
                </td>
                <td colspan="2">
                    <span class="label">Data Admissão</span>
                    <span class="value"><?php echo e($texto($admissao)); ?></span>
                </td>
                <td colspan="3">
                    <span class="label">Setor / Local</span>
                    <span class="value"><?php echo e($texto($setor)); ?></span>
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Demonstrativo de eventos</div>
        <table class="events">
            <thead>
                <tr>
                    <th style="width:10%;" class="center">Cód.</th>
                    <th style="width:38%;">Descrição</th>
                    <th style="width:14%;" class="center">Referência</th>
                    <th style="width:19%;" class="right">Vencimentos</th>
                    <th style="width:19%;" class="right">Descontos</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $eventosLinhas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td class="center mono"><?php echo e($texto($item['codigo'] ?? '', '000')); ?></td>
                    <td><?php echo e($item['descricao']); ?></td>
                    <td class="center"><?php echo e($item['referencia']); ?></td>
                    <td class="right"><?php echo e($item['vencimento'] !== null ? $fmt($item['vencimento']) : '0,00'); ?></td>
                    <td class="right"><?php echo e($item['desconto'] !== null ? $fmt($item['desconto']) : '0,00'); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Resumo financeiro</div>
        <table class="bottom-grid">
            <tr>
                <td colspan="2">
                    <span class="label">Salário Base</span>
                    <span class="value strong"><?php echo e($fmt($salarioBase)); ?></span>
                </td>
                <td colspan="2">
                    <span class="label">Total de Vencimentos</span>
                    <span class="value strong"><?php echo e($fmt($totalProventos)); ?></span>
                </td>
                <td colspan="2">
                    <span class="label">Total de Descontos</span>
                    <span class="value strong"><?php echo e($fmt($totalDescontos)); ?></span>
                </td>
                <td colspan="2">
                    <span class="label">Valor Líquido</span>
                    <span class="value big"><?php echo e($fmt($liquidoCalc)); ?></span>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <span class="label">Base INSS</span>
                    <span class="value"><?php echo e($fmt($baseInss)); ?></span>
                </td>
                <td colspan="2">
                    <span class="label">INSS</span>
                    <span class="value"><?php echo e($fmt($inss)); ?></span>
                </td>
                <td colspan="2">
                    <span class="label">Base FGTS</span>
                    <span class="value"><?php echo e($fmt($baseFgts)); ?></span>
                </td>
                <td colspan="2">
                    <span class="label">FGTS do Mês</span>
                    <span class="value"><?php echo e($fmt($fgts)); ?></span>
                </td>
            </tr>
            <tr>
                <td colspan="4">
                    <span class="label">Base Cálc. IRRF</span>
                    <span class="value"><?php echo e($fmt($baseIrrf)); ?></span>
                </td>
                <td colspan="4">
                    <span class="label">Faixa IRRF</span>
                    <span class="value"><?php echo e(((float) ($faixaIrrf ?? 0)) > 0 ? $fmt($faixaIrrf) : '0,00'); ?></span>
                </td>
            </tr>
        </table>
    </div>

    <div class="declaration-wrap">
        <div class="declaration-box">
            Declaro ter recebido a importância líquida discriminada neste recibo, referente à competência informada.
        </div>
        <div class="signature-box">
            <div class="signature-area"></div>
            <div class="sign-label">Assinatura do colaborador</div>
        </div>
        <div class="date-box">
            <div class="date-area"></div>
            <div class="sign-label">Data</div>
        </div>
    </div>

    <div class="footer-note">Documento interno gerado automaticamente pelo sistema</div>
</div>
<?php /**PATH /home2/wesl4494/public_html/padariacasadopao.com/resources/views/rh/holerite/_voucher_content.blade.php ENDPATH**/ ?>