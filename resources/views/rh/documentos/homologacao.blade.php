@extends('default.layout',['title' => 'Termo de Homologação'])
@section('content')
<div class="page-content"><div class="card"><div class="card-body p-4">
    <h4 class="text-center mb-4">TERMO DE HOMOLOGAÇÃO DA RESCISÃO</h4>
    @include('rh.documentos._rescisao_base', ['tituloSecundario' => 'HOMOLOGAÇÃO'])
    <div class="row mt-5">
        <div class="col-md-6 text-center">_________________________________<br>Empregador</div>
        <div class="col-md-6 text-center">_________________________________<br>Trabalhador</div>
    </div>
</div></div></div>
@endsection
