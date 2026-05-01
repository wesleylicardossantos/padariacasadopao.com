@extends('default.layout',['title' => $documentTitle])
@section('content')
<div class="page-content">
    <div class="card">
        <div class="card-body p-3 p-md-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <div>
                    <h5 class="mb-1 text-uppercase">{{ $documentTitle }}</h5>
                    <div class="text-muted small">{{ $previewLabel ?? 'Pré-visualização técnica ajustada para DOMPDF / Snappy PDF' }}</div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a target="_blank" href="{{ request()->url() . '?pdf=1&driver=dompdf' }}" class="btn btn-danger">Abrir PDF DOMPDF</a>
                    <a target="_blank" href="{{ request()->url() . '?pdf=1&driver=snappy' }}" class="btn btn-outline-dark">Abrir PDF Snappy</a>
                </div>
            </div>
            <div class="ratio" style="--bs-aspect-ratio: 141.4%;">
                <iframe
                    title="{{ $documentTitle }}"
                    src="{{ request()->url() . '?pdf=1&driver=dompdf' }}"
                    style="width:100%;height:100%;border:1px solid #d1d5db;border-radius:.5rem;background:#fff;"
                ></iframe>
            </div>
        </div>
    </div>
</div>
@endsection
