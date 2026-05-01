@extends('default.layout',['title' => 'TQRCT'])
@section('content')
<div class="page-content"><div class="card"><div class="card-body p-4">
    <h4 class="text-center mb-4">TERMO DE QUITAÇÃO DE RESCISÃO DO CONTRATO DE TRABALHO</h4>
    @include('rh.documentos._rescisao_base', ['tituloSecundario' => 'TQRCT'])
    <div class="mt-4"><strong>Declaração:</strong> As verbas rescisórias acima foram quitadas conforme os lançamentos apresentados.</div>
</div></div></div>
@endsection
