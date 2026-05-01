@extends('default.layout',['title' => 'RH - Nova Ocorrência'])
@section('content')
<div class="page-content">
    <div class="card"><div class="card-body p-4">
        <div class="d-flex justify-content-between mb-3"><h5 class="mb-0">Nova ocorrência</h5><a class="btn btn-secondary" href="{{ route('rh.ocorrencias.index') }}">Voltar</a></div>
        {!! Form::open()->post()->route('rh.ocorrencias.store') !!}
        @include('rh.ocorrencias.form')
        {!! Form::close() !!}
    </div></div>
</div>
@endsection
