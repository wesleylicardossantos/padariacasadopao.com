@extends('default.layout',['title' => 'RH - WhatsApp Inteligente'])
@section('content')
<div class="page-content">
    <div class="card">
        <div class="card-body p-4">
            <h5 class="mb-3">WhatsApp Inteligente RH</h5>
            <form method="POST" action="/rh/whatsapp-inteligente">
                @csrf
                <label class="form-label">Pergunta</label>
                <input type="text" class="form-control" name="pergunta" value="{{ $pergunta ?? '' }}" placeholder="Ex: posso contratar?">
                <button class="btn btn-primary mt-3">Perguntar</button>
            </form>

            @if(!empty($resposta))
                <div class="alert alert-info mt-4">
                    <strong>Resposta:</strong><br>
                    {{ $resposta }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
