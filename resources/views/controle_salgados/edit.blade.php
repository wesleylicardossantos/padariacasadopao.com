@extends('default.layout',['title' => 'Controle de Salgados'])
@section('content')
<div class="page-content">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h4 class="mb-1">Editar controle de salgados</h4>
            <p class="text-muted mb-0">Atualize a produção do dia e mantenha a folha operacional sincronizada com o banco.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('controle.salgados.pdf', $item->id) }}" target="_blank" class="btn btn-outline-secondary">PDF</a>
            <a href="{{ route('controle.salgados.index') }}" class="btn btn-light">Voltar</a>
        </div>
    </div>


    <form method="POST" action="{{ route('controle.salgados.update', $item->id) }}">
        @csrf
        @method('PUT')
        @include('controle_salgados._form')
    </form>
</div>
@endsection
