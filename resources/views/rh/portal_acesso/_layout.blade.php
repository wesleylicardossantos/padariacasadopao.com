<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Portal do Funcionário')</title>
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/icons.css') }}" rel="stylesheet">
    <style>
        body{background:#f4f6f9;min-height:100vh;}
        .portal-wrap{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;}
        .portal-card{width:100%;max-width:480px;border:0;border-radius:18px;box-shadow:0 10px 30px rgba(0,0,0,.08)}
    </style>
</head>
<body>
<div class="portal-wrap">
    <div class="card portal-card">
        <div class="card-body p-4 p-md-5">
            <div class="text-center mb-4">
                <h3 class="mb-1">Portal do Funcionário</h3>
                <div class="text-muted">Acesse com CPF ou e-mail</div>
            </div>
            <x-flash-message />
            @yield('body')
        </div>
    </div>
</div>
</body>
</html>
