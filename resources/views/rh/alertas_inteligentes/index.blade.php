@extends('default.layout',['title' => 'RH - Alertas Inteligentes'])
@section('content')
<style>
.box{border-left:4px solid #94a3b8;border-radius:10px;padding:.9rem 1rem;background:#fff}
.box.alerta{border-left-color:#f59e0b;background:#fff7ed}
.box.critico{border-left-color:#dc2626;background:#fef2f2}
.box.ok{border-left-color:#16a34a;background:#f0fdf4}
.badge-read{display:inline-block;padding:.25rem .55rem;border-radius:999px;background:#eef2ff;font-size:.78rem;font-weight:700}
</style>

<div class="page-content">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h5 class="mb-0">Alertas Inteligentes</h5>
            <small class="text-muted">Detecção automática de riscos atuais e futuros.</small>
        </div>
        <div class="d-flex gap-2">
            <a href="/rh/preditivo-ia" class="btn btn-dark">Preditivo IA</a>
            <a href="/rh/ia-aprendizado" class="btn btn-secondary">IA com Aprendizado</a>
        </div>
    </div>

    <form method="GET" action="/rh/alertas-inteligentes">
        <div class="card mb-3">
            <div class="card-body p-4">
                <div class="row g-3 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label">Mês</label>
                        <input type="number" class="form-control" name="mes" min="1" max="12" value="{{ $mes }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Ano</label>
                        <input type="number" class="form-control" name="ano" value="{{ $ano }}">
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary w-100">Atualizar</button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <div class="alert alert-info">
        Alertas gerados agora: <strong>{{ count($alertasGerados) }}</strong> |
        Persistidos nesta execução: <strong>{{ $persistidos }}</strong>
    </div>

    <div class="card">
        <div class="card-header bg-transparent"><h6 class="mb-0">Alertas registrados</h6></div>
        <div class="card-body">
            <div class="d-grid gap-3">
                @forelse($alertas as $a)
                    <div class="box {{ $a->nivel }}">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div>
                                <strong>{{ $a->titulo }}</strong><br>
                                <span>{{ $a->mensagem }}</span><br>
                                <small>Tipo: {{ $a->tipo }} | Criado em: {{ $a->created_at }}</small>
                            </div>
                            <div class="text-end">
                                <span class="badge-read">{{ $a->lido ? 'LIDO' : 'NOVO' }}</span><br>
                                @if(!$a->lido)
                                    <a href="/rh/alertas-inteligentes/ler/{{ $a->id }}" class="btn btn-sm btn-outline-primary mt-2">Marcar como lido</a>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="box ok">Sem alertas registrados.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
