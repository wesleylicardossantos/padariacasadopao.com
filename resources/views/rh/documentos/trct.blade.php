@extends('default.layout',['title' => 'TRCT'])
@section('content')
<div class="page-content"><div class="card"><div class="card-body p-4">
    <h4 class="text-center mb-4">TERMO DE RESCISÃO DO CONTRATO DE TRABALHO</h4>
    @include('rh.documentos._rescisao_base', ['tituloSecundario' => 'TRCT'])
</div></div></div>
@endsection
