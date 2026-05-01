@extends('default.layout', ['title' => 'Identidade visual da empresa'])
@section('content')
<div class="page-content">
    <div class="card">
        <div class="card-body p-4">
            <div class="col">
                <h6 class="mb-0 text-uppercase">Identidade visual da empresa</h6>
                <p class="text-muted mt-2 mb-0">Envie a logo oficial e a imagem de fundo do login. As alterações são salvas no banco, no storage e atualizadas automaticamente em todo o sistema.</p>
                {!! Form::open()->post()->multipart()->route('config.store') !!}
                <hr>
                @include('config_empresa._forms')
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>
@endsection
