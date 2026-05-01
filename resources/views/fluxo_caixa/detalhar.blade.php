@extends('default.layout', ['title' => 'Detalhamento do Fluxo de Caixa'])
@section('content')
<div class="page-content">
    <div class="card">
        <div class="card-body p-0">
            @include('fluxo_caixa._detalhar_content')
        </div>
    </div>
</div>
@endsection
