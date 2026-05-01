<!doctype html>
    <html lang="pt-BR" class="<?php echo e($theme != null ? $theme->tema : ''); ?> <?php echo e($theme != null ? $theme->cabecalho : ''); ?> <?php echo e($theme != null ? 'color-sidebar ' . $theme->plano_fundo : ''); ?>">

    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!--favicon-->

        <!--plugins-->
        <link href="/assets/css/simplebar.css" rel="stylesheet" />
        <link href="/assets/css/perfect-scrollbar.css" rel="stylesheet" />
        <link href="/assets/css/tagsinput.css" rel="stylesheet" />
        <link href="/assets/vectormap/jquery-jvectormap-2.0.2.css" rel="stylesheet" />
        <link href="/assets/css/highcharts.css" rel="stylesheet" />
        <!-- loader-->
        <link href="/assets/css/metisMenu.min.css" rel="stylesheet" />
        <link href="assets/css/pace.min.css" rel="stylesheet" />
        <script src="assets/js/pace.min.js"></script>
        <!-- Bootstrap CSS -->
        <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
        <link href="/assets/css/bootstrap-extended.css" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
        <link href="/assets/css/app.css" rel="stylesheet">
        <link href="/assets/css/icons.css" rel="stylesheet">
        <!-- Theme Style CSS -->
        <link rel="stylesheet" href="/assets/css/dark-theme.css" />
        <link rel="stylesheet" href="/assets/css/semi-dark.css" />
        <link rel="stylesheet" href="/assets/css/header-colors.css" />

        <link href="/assets/css/select2.min.css" rel="stylesheet" />
        <link href="/assets/css/select2-bootstrap4.css" rel="stylesheet" />
        <link href="/assets/css/style.css" rel="stylesheet" />
        <link rel="stylesheet" type="text/css" href="/assets/css/toastr.min.css">

        <title>Frente de Caixa</title>

        <style type="text/css">
            .mousetrap{
                width: 0;
                border:none;
                outline:none;
                background: #f3f6f9;
                position: relative;
            }

            #mousetrapTitle{
                position: absolute;
                left: 80px;
                color: green;
                background-color: #fff;
                box-shadow: 0 0 5px #fff;
                align-items: center;
                gap: 3px;
            }

            .pr-2{
                background:#F3F6F9;
                border: 1px solid #ccc;
                border-radius: 2px;
                height: 35px;
            }


            .mousetrap:focus{
                border:none;outline:none;border-right: 3px solid #000;
                animation: fadebarcode .5s infinite ease-in-out;
            }
        </style>
        <?php echo $__env->yieldContent('css'); ?>
    </head>

    <body>
        <input type="hidden" value="<?php echo e(session('user_logged')['empresa']); ?>" id="empresa_id">
        <input type="hidden" value="<?php echo e(session('user_logged')['id']); ?>" id="usuario_id">
        <?php if(isset($config)): ?>
        <input type="hidden" id="pass" value="<?php echo e($config->senha_remover); ?>">
        <?php endif; ?>
        <input type="hidden" value="<?php echo e($config->percentual_max_desconto); ?>" id="percentual_max_desconto">
       
        <!--wrapper-->
        <div class="wrapper">
            
            <?php echo Form::open()->post()->route('frenteCaixa.store')->id('form-pdv'); ?>

            <?php echo $__env->make('frontBox._forms', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            <?php echo Form::close(); ?>

        </div>

        <div class="page">
            <div class="row">

                <?php echo $__env->yieldContent('modal'); ?>

            </div>
        </div>

        <div class="modal-loading"></div>

        <?php echo $__env->make('modals.frontBox._fluxo_diario', ['not_submit' => true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php echo $__env->make('modals.frontBox._lista_pre_venda', ['not_submit' => true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php echo $__env->make('modals.frontBox._suprimento_caixa', ['not_submit' => true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php echo $__env->make('modals.frontBox._comanda_pdv', ['not_submit' => true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php echo $__env->make('modals.frontBox._sangria_caixa', ['not_submit' => true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php echo $__env->make('modals._abrir_caixa', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>


        <script type="text/javascript">
            var casas_decimais = 2;
            casas_decimais = <?php echo e($casasDecimais); ?>

            const path_url = <?php echo json_encode(url('/') . '/', 15, 512) ?>;
        </script>

        <script>

        </script>
        <script src="/assets/js/bootstrap.bundle.min.js"></script>
        <!--plugins-->
        <script src="/assets/js/jquery.min.js"></script>
        
        
        
        
        <!-- <script src="/assets/js/index2.js"></script> -->
        <script src='https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js'></script>
        <script type="text/javascript" src="/js/jquery.mask.min.js"></script>
        <script src="/assets/js/select2.min.js"></script>
        
        <script src="/js/main.js"></script>
        <script src="/js/frontBox.js"></script>
        <script src="/js/theme.js"></script>
        <script src="/assets/js/toastr.min.js"></script>

        <script type="text/javascript">
            toastr.options = {
                "progressBar": true, 
                "onclick": null,
                "showDuration": "300",
                "hideDuration": "1000",
                "timeOut": "10000",
                "extendedTimeOut": "1000",
                "showEasing": "swing",
                "hideEasing": "linear",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut"
            }

            <?php if(session()->has('flash_sucesso')): ?>
            toastr.success('<?php echo e(session()->get('flash_sucesso')); ?>');
            <?php endif; ?>

            <?php if(session()->has('flash_erro')): ?>
            toastr.error('<?php echo e(session()->get('flash_erro')); ?>');
            <?php endif; ?>

            <?php if(session()->has('flash_warning')): ?>
            toastr.warning('<?php echo e(session()->get('flash_warning')); ?>');
            <?php endif; ?>

        </script>

        <?php echo $__env->yieldContent('js'); ?>
    </body>
    </html>
<?php /**PATH /home2/wesl4494/public_html/padariacasadopao.com/resources/views/frontBox/index.blade.php ENDPATH**/ ?>