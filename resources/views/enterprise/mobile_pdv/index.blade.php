@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h1 class="mb-3">PDV Mobile</h1>
    <p class="text-muted">Aplicativo PWA para operação móvel com bootstrap offline, catálogo local e sincronização posterior.</p>
    <div class="card">
        <div class="card-body">
            <p class="mb-2">Acesso direto ao app:</p>
            <a class="btn btn-primary" href="{{ asset('mobile-pdv/index.html') }}" target="_blank">Abrir app mobile</a>
            <p class="small text-muted mt-3 mb-0">O app usa os endpoints de bootstrap e sincronização do PDV offline para operação mobile.</p>
        </div>
    </div>
</div>
@endsection
