@extends('default.layout',['title' => 'RH - Pré-visualização do Documento'])
@section('content')
<div class="page-content">
    <div class="card"><div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h5 class="mb-0">{{ $documento->nome }}</h5>
                <small class="text-muted">{{ $documento->tipo }} · {{ $documento->origem ?: 'manual' }}</small>
            </div>
            <div class="d-flex gap-2">
                @if(!empty($documento->arquivo))
                <a class="btn btn-primary" target="_blank" href="{{ route('rh.documentos.download', $documento->id) }}">Abrir PDF</a>
                @endif
                <a class="btn btn-secondary" href="{{ route('rh.documentos.index') }}">Voltar</a>
            </div>
        </div>
        <div class="border rounded p-4 bg-white">{!! $documento->conteudo_html ?: nl2br(e($documento->conteudo_texto ?: 'Sem conteúdo renderizado.')) !!}</div>
    </div></div>
</div>
@endsection
