<!DOCTYPE html>
<html lang="pt-BR">
<?php
    $branding = $empresaBranding ?? [];
    $brandingLogo = $branding['logo_url'] ?? asset('logos/default.png');
    $brandingBackground = $branding['background_url'] ?? asset('assets/images/img123.jpg');
    $brandingNome = $branding['nome'] ?? config('app.name');
?>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/css/bootstrap-extended.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/login2/auth.css" />
    <title><?php echo e($brandingNome); ?> Login</title>
</head>

<body>
    <div id="auth">
        <div class="row g-0 min-vh-100">
            <div class="col-lg-5 col-12 auth-left-wrapper">
                <?php if(session()->has('flash_sucesso')): ?>
                <div class="mt-2 mb-0 px-2">
                    <div class="alert alert-success border-0 bg-success alert-dismissible fade show mb-0 py-1">
                        <div class="d-flex align-items-center">
                            <div class="font-35 text-white"><i class="bx bxs-check-circle"></i></div>
                            <div class="ms-3">
                                <h6 class="mb-0 text-white">Sucesso</h6>
                                <div class="text-white"><?php echo e(session()->get('flash_sucesso')); ?></div>
                            </div>
                        </div>
                        <button type="button" class="btn-close text-white" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
                <?php endif; ?>

                <?php if(session()->has('flash_erro')): ?>
                <div class="mt-2 mb-0 px-2">
                    <div class="alert alert-danger border-0 bg-danger alert-dismissible fade show mb-0 py-1">
                        <div class="d-flex align-items-center">
                            <div class="font-35 text-white"><i class="bx bxs-message-square-x"></i></div>
                            <div class="ms-3">
                                <h6 class="mb-0 text-white">Erro</h6>
                                <div class="text-white"><?php echo e(session()->get('flash_erro')); ?></div>
                            </div>
                        </div>
                        <button type="button" class="btn-close text-white" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
                <?php endif; ?>

                <div id="auth-left">
                    <div class="auth-logo">
                        <a href="/">
                            <!-- CAMINHO DA LOGO: public<?php echo e($brandingLogo); ?> -->
                            <img src="<?php echo e($brandingLogo); ?>" alt="Logo do sistema" onerror="this.onerror=null;this.src='<?php echo e(asset('logos/default.png')); ?>';" />
                        </a>
                    </div>

                    <div class="auth-header-text">
                        <h1 class="auth-title">Login</h1>
                        <p class="auth-subtitle">Entre com seus dados de acesso</p>
                    </div>

                    <div class="auth-card mt-4">
                        <form method="post" action="<?php echo e(route('login.request')); ?>" id="form-login">
                            <?php echo csrf_field(); ?>
                            <div class="form-group position-relative mb-3">
                                <label class="form-label" for="login">Usuário</label>
                                <input autocomplete="off" type="text" class="form-control" id="login" placeholder="Digite seu usuário" autofocus <?php if(session('login') !=null): ?> value="<?php echo e(session('login')); ?>" <?php else: ?> <?php if(isset($loginCookie)): ?> value="<?php echo e($loginCookie); ?>" <?php endif; ?> <?php endif; ?> name="login" />
                            </div>

                            <div class="form-group position-relative mb-3">
                                <label class="form-label" for="senha">Senha</label>
                                <input type="password" class="form-control" id="senha" name="senha" placeholder="Digite sua senha" autocomplete="off" <?php if(isset($senhaCookie)): ?> value="<?php echo e($senhaCookie); ?>" <?php endif; ?>>
                            </div>

                            <div class="form-check form-check-lg d-flex mt-1 mb-3">
                                <input class="form-check-input me-2" type="checkbox" id="lembrar" name="lembrar" <?php if(isset($lembrarCookie)): ?> <?php if($lembrarCookie==true): ?> checked <?php endif; ?> <?php endif; ?> />
                                <label class="form-check-label text-gray-600" for="lembrar">
                                    Lembrar-me
                                </label>
                            </div>

                            <button type="submit" class="btn btn-primary btn-login w-100">
                                Entrar
                            </button>
                        </form>

                        <div class="text-center mt-3 form-login">
                            <p><a href="javascript:;" id="forget-password" class="text-sublinhado">Esqueci minha senha</a></p>
                        </div>
                    </div>

                    <div class="row div-recuperar-senha-sicok d-none mt-4">
                        <form method="post" id="forget-form" action="<?php echo e(route('recuperarSenha')); ?>">
                            <?php echo csrf_field(); ?>
                            <p>Receba uma nova senha em seu e-mail cadastrado.</p>
                            <div class="form-group">
                                <input class="form-control input-email-recuperar-senha-sicok" type="text" autocomplete="off" placeholder="E-mail cadastrado" name="email" />
                            </div>
                            <div class="mt-3">
                                <button type="submit" class="btn btn-warning btn-recuperar-senha-sicok w-100">Solicitar nova senha</button>
                            </div>
                        </form>
                        <div class="mt-2">
                            <button type="button" id="back-btn" class="btn btn-info">Tela de login</button>
                        </div>
                    </div>

                    <div class="mt-4">
                        <a href="/cadastro/plano" class="btn btn-success btn-cadastro w-100">Quero cadastrar minha empresa</a>
                    </div>

                    <?php if(env("APP_ENV") == "demo"): ?>
                    <div class="card mt-3">
                        <div class="card-body">
                            <h4 class="mt-2">Demonstração de Login</h4>
                            <button type="button" class="btn btn-success" onclick="doLogin('usuario', '123')">
                                Super Admin
                            </button>
                            <button type="button" class="btn btn-info" onclick="doLogin('mateus', '123456')">
                                Administrador
                            </button>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-lg-7 col-12 d-none d-lg-block">
                <!-- CAMINHO DO FUNDO: public/assets/images/img123.jpg -->
                <div id="auth-right" style="background: linear-gradient(135deg, rgba(21,31,65,0.78), rgba(63,84,145,0.42)), url('<?php echo e($brandingBackground); ?>') center center / cover no-repeat;">
                    <div class="auth-right-overlay">
                        <div class="brand-mark">
                            <img src="<?php echo e($brandingLogo); ?>" alt="Logo do sistema" onerror="this.onerror=null;this.src='<?php echo e(asset('logos/default.png')); ?>';" />
                        </div>
                        <div class="auth-right-content">
                            <h2>Gestão inteligente para sua empresa</h2>
                            <p>Controle financeiro, vendas, estoque, relatórios e indicadores em um só lugar.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="/assets/js/jquery.min.js" type="text/javascript"></script>
    <script>
        $("#forget-password").on('click', function() {
            $('#form-login, .form-login').addClass('d-none');
            $('.div-recuperar-senha-sicok').removeClass('d-none');
        });

        $('#back-btn').on('click', function() {
            $('.div-recuperar-senha-sicok').addClass('d-none');
            $('#form-login, .form-login').removeClass('d-none');
        });

        function doLogin(login, senha){
            $('#login').val(login);
            $('#senha').val(senha);
            $('#form-login').submit();
        }
    </script>
</body>
</html>
<?php /**PATH /home2/wesl4494/public_html/padariacasadopao.com/resources/views/login/login2.blade.php ENDPATH**/ ?>