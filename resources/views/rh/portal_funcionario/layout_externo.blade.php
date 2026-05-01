<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Portal do funcionário' }}</title>
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/icons.css') }}" rel="stylesheet">
    <style>body{background:#f4f6f9}.page-content{padding:24px 0}</style>
</head>
<body>
<div class="container py-4">
    <x-flash-message />
    @yield('content')
</div>
</body>
</html>
