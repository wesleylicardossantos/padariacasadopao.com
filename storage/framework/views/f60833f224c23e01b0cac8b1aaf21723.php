<?php $__env->startSection('content'); ?>
<div class="page-content">
	<div class="card border-top border-0 border-4 border-primary">
		<div class="card-body p-5">
			<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
				<div class="ms-auto">
					<a href="<?php echo e(route('conta-pagar.index')); ?>" type="button" class="btn btn-light btn-sm">
						<i class="bx bx-arrow-back"></i> Voltar
					</a>
				</div>
			</div>
			<div class="card-title d-flex align-items-center">
				<h5 class="mb-0 text-primary">Nova conta a pagar</h5>
			</div>
			<hr>
			
			<?php echo Form::open()
			->post()
			->route('conta-pagar.store')
			->multipart(); ?>

			<div class="pl-lg-4">
				<?php echo $__env->make('conta_pagar._forms', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
			</div>
			<?php echo Form::close(); ?>

		</div>
	</div>
</div>
<?php echo $__env->make('modals._fornecedor', ['not_submit' => true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<?php $__env->startSection('js'); ?>
<script type="text/javascript">
    $('.modal .select2').each(function() {
        console.log($(this))
        let id = $(this).prop('id')
        if(id == 'inp-uf'){
            $(this).select2({
                dropdownParent: $(this).parent(),
                theme: 'bootstrap4',
            });
        }
        if(id == 'inp-cidade_id'){
            $(this).select2({
                minimumInputLength: 2,
                language: "pt-BR",
                placeholder: "Digite para buscar a cidade",
                width: "100%",
                theme: 'bootstrap4',
                dropdownParent: $(this).parent(),
                ajax: {
                    cache: true,
                    url: path_url + 'api/buscaCidades',
                    dataType: "json",
                    data: function(params) {
                        console.clear()
                        var query = {
                            pesquisa: params.term,
                        };
                        return query;
                    },
                    processResults: function(response) {
                        console.log("response", response)
                        var results = [];

                        $.each(response, function(i, v) {
                            var o = {};
                            o.id = v.id;

                            o.text = v.nome + "(" + v.uf + ")";
                            o.value = v.id;
                            results.push(o);
                        });
                        return {
                            results: results
                        };
                    }
                }
            });
        }
    })

    $('#inp-recorrencia').blur(() => {
        let data = $('#inp-recorrencia').val()
        if(data.length == 5){
            let vencimento = $('#inp-data_vencimento').val()
            let valor = $('#inp-valor_integral').val()
            if(valor && vencimento){
                let item = {
                    data: data,
                    vencimento: vencimento,
                    valor: valor
                }
                $.get(path_url + 'api/conta-pagar/recorrencia', item)
                .done((html) => {
                    console.log("success", html)
                    $('.tbl-recorrencia').html(html)
                    $('.tbl-recorrencia').removeClass('d-none')

                }).fail((err) => {
                    console.log(err)

                })
            }else{
                swal("Algo saiu errado", "Informe o valor e vencimento data conta base!", "warning")
            }
        }else{
            swal("Algo saiu errado", "Informe uma data válida mm/aa exemplo 12/25", "warning")
        }
    })
</script>

<script type="text/javascript" src="/js/fornecedor.js"></script>

<?php $__env->stopSection(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('default.layout',['title' => 'Nova Conta a Pagar'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home2/wesl4494/public_html/padariacasadopao.com/resources/views/conta_pagar/create.blade.php ENDPATH**/ ?>