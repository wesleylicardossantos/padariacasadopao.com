<?php $__env->startSection('content'); ?>
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
            </div>
            <div class="col">
                <h6 class="mb-0 text-uppercase">MOVIMENTAÇÃO DE CAIXA</h6>
                <?php echo Form::open()->fill(request()->all())->get(); ?>

                <div class="row">
                    <div class="col-md-3">
                        <?php echo Form::date('start_date', 'Data inicial'); ?>

                    </div>
                    <div class="col-md-3">
                        <?php echo Form::date('end_date', 'Data final'); ?>

                    </div>
                    <div class="col-md-6 text-left ">
                        <br>
                        <button class="btn btn-primary" type="submit"><i class="bx bx-search"></i> Pesquisar</button>
                        <a id="clear-filter" class="btn btn-danger" href="<?php echo e(route('fluxoCaixa.index')); ?>"><i class="bx bx-eraser"></i> Limpar</a>
                        <div class="btn-group">
                            <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bx bx-plus"></i> Adicionar
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#modal-suprimento_caixa"><i class="bx bx-plus-circle text-success"></i> Crédito</a></li>
                                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#modal-sangria_caixa"><i class="bx bx-minus-circle text-danger"></i> Débito</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <?php echo Form::close(); ?>

                <hr />
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table mb-0 table-striped">
                                <thead class="">
                                    <tr>
                                        <th>Data</th>
                                        <th>Vendas</th>
                                        <th>Frente de caixa</th>
                                        <th>Soma de vendas</th>
                                        <th>Contas recebidas</th>
                                        <th>Créditos</th>
                                        <th>Débitos</th>
                                        <th>Ordem de serviço</th>
                                        <th>Contas pagas</th>
                                        <th>Resultado</th>
                                        <th class="text-center">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__empty_1 = true; $__currentLoopData = $fluxo; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><?php echo e($item['data']); ?></td>
                                        <td><?php echo e(__moeda($item['venda'])); ?></td>
                                        <td><?php echo e(__moeda($item['venda_caixa'])); ?></td>
                                        <td><?php echo e(__moeda($item['venda'] + $item['venda_caixa'])); ?></td>
                                        <td><?php echo e(__moeda($item['conta_receber'])); ?></td>
                                        <td><?php echo e(__moeda($item['suprimento'] ?? 0)); ?></td>
                                        <td><?php echo e(__moeda($item['sangria'] ?? 0)); ?></td>
                                        <td><?php echo e(__moeda($item['os'])); ?></td>
                                        <td><?php echo e(__moeda($item['conta_pagar'])); ?></td>
                                        <td><?php echo e(__moeda(($item['venda'] + $item['venda_caixa'] + $item['conta_receber'] + ($item['suprimento'] ?? 0) + $item['os']) - (($item['sangria'] ?? 0) + $item['conta_pagar']))); ?></td>
                                        <td class="text-center" style="min-width: 130px;">
                                            <div class="dropdown">
                                                <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    Ações
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <button type="button" class="dropdown-item btn-detalhar-dia" title="Visualizar" data-url="<?php echo e(route('fluxoCaixa.detalharDia', $item['data_raw'] ?? '')); ?>" data-bs-toggle="modal" data-bs-target="#modalDetalharDia">
                                                            <i class="bx bx-show"></i> Visualizar
                                                        </button>
                                                    </li>
                                                    <?php if(session('user_logged.super') ?? false): ?>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <a class="dropdown-item text-danger" title="Excluir itens do dia" href="<?php echo e(route('fluxoCaixa.excluirForm', $item['data_raw'] ?? '')); ?>">
                                                            <i class="bx bx-trash"></i> Excluir
                                                        </a>
                                                    </li>
                                                    <?php endif; ?>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="11" class="text-center">Nada encontrado</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('modals.frontBox._suprimento_caixa', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php echo $__env->make('modals.frontBox._sangria_caixa', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<div class="modal fade" id="modalDetalharDia" tabindex="-1" aria-labelledby="modalDetalharDiaLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDetalharDiaLabel">Detalhamento da movimentação</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modalDetalharDiaBody">
                <div class="text-center p-4 text-muted">Selecione um dia para visualizar os detalhes.</div>
            </div>
        </div>
    </div>
</div>

<?php $__env->startSection('js'); ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalEl = document.getElementById('modalDetalharDia');
    const modalBody = document.getElementById('modalDetalharDiaBody');
    const loadingHtml = '<div class=\"text-center p-5 text-muted\"><div class=\"spinner-border text-primary mb-3\" role=\"status\"></div><div>Carregando detalhamento...</div></div>';
    const emptyHtml = '<div class=\"alert alert-danger m-3 mb-0\">Não foi possível carregar o detalhamento deste dia.</div>';

    document.querySelectorAll('.btn-detalhar-dia').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const url = this.getAttribute('data-url');
            if (!modalBody || !url) return;
            modalBody.innerHTML = loadingHtml;

            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html'
                }
            })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Erro ao carregar detalhamento');
                }
                return response.text();
            })
            .then(function (html) {
                modalBody.innerHTML = html;
            })
            .catch(function () {
                modalBody.innerHTML = emptyHtml;
            });
        });
    });

    if (modalEl) {
        modalEl.addEventListener('hidden.bs.modal', function () {
            if (modalBody) {
                modalBody.innerHTML = '<div class=\"text-center p-4 text-muted\">Selecione um dia para visualizar os detalhes.</div>';
            }
        });
    }
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('default.layout', ['title' => 'Fluxo de Caixa'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home2/wesl4494/public_html/padariacasadopao.com/resources/views/fluxo_caixa/index.blade.php ENDPATH**/ ?>