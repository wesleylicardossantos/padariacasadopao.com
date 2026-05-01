@extends('default.layout',['title' => 'RH - Editar Férias'])
@section('content')
<div class="page-content">
    <div class="card"><div class="card-body p-4">
        <div class="d-flex justify-content-between mb-3"><h5 class="mb-0">Editar férias</h5><a class="btn btn-secondary" href="{{ route('rh.ferias.index') }}">Voltar</a></div>
        {!! Form::open()->post()->route('rh.ferias.update', [$item->id])->fill($item) !!}
        @include('rh.ferias.form')
        {!! Form::close() !!}
    </div></div>
</div>
@endsection
