@extends('default.layout',['title' => 'Controle de Salgados'])
@section('content')
<div class="page-content">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h4 class="mb-1">Controle de Salgados</h4>
            <p class="text-muted mb-0">Novo lançamento operacional de produção com estrutura pronta para SaaS multiempresa.</p>
        </div>
        <a href="{{ route('controle.salgados.index') }}" class="btn btn-light">Listagem</a>
    </div>


    <form method="POST" action="{{ route('controle.salgados.store') }}">
        @csrf
        @include('controle_salgados._form')
    </form>
</div>
@endsection
