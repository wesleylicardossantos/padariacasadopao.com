@extends('default.layout',['title' => 'RH - IA Externa'])
@section('content')
<style>
.rh-card{border:1px solid #e8edf5;border-radius:16px;box-shadow:0 8px 24px rgba(15,23,42,.04)}
.box{border-left:4px solid #94a3b8;border-radius:10px;padding:.9rem 1rem;background:#fff}
.smallmuted{color:#64748b;font-size:.92rem}
</style>

<div class="page-content">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h5 class="mb-0">IA Externa (WhatsApp / Email)</h5>
            <small class="text-muted">Envia alertas da IA para fora do sistema com segurança.</small>
        </div>
        <div class="d-flex gap-2">
            <a href="/rh/maximo" class="btn btn-dark">Nível Máximo</a>
            <a href="/rh/ia-autonoma" class="btn btn-secondary">IA Autônoma</a>
        </div>
    </div>

    <form method="GET" action="/rh/ia-externa">
        <div class="card rh-card mb-3">
            <div class="card-body p-4">
                <div class="row g-3 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label">Mês</label>
                        <input type="number" class="form-control" name="mes" min="1" max="12" value="{{ $mes ?? date('m') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Ano</label>
                        <input type="number" class="form-control" name="ano" value="{{ $ano ?? date('Y') }}">
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary w-100">Atualizar</button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <div class="row g-3 mb-3">
        <div class="col-lg-3"><div class="card rh-card"><div class="card-body"><div class="smallmuted">Competência</div><div><strong>{{ $payload['competencia'] ?? ((str_pad(($mes ?? date('m')), 2, '0', STR_PAD_LEFT)) . '/' . ($ano ?? date('Y'))) }}</strong></div></div></div></div>
        <div class="col-lg-3"><div class="card rh-card"><div class="card-body"><div class="smallmuted">Score</div><div><strong>{{ $score ?? 0 }}/100</strong></div></div></div></div>
        <div class="col-lg-6"><div class="card rh-card"><div class="card-body"><div class="smallmuted">Modo dono</div><div><strong>{{ $payload['modo_dono'] ?? 'Sem recomendação no momento.' }}</strong></div></div></div></div>
    </div>

    <div class="card rh-card mb-3">
        <div class="card-header bg-transparent"><h6 class="mb-0">Alertas que serão enviados</h6></div>
        <div class="card-body">
            <div class="d-grid gap-2">
                @forelse(($alertas ?? []) as $alerta)
                    <div class="box">{{ $alerta }}</div>
                @empty
                    <div class="box">Sem alertas críticos no período.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="card rh-card">
        <div class="card-header bg-transparent"><h6 class="mb-0">Envio externo</h6></div>
        <div class="card-body">
            @if(session('resultado_email'))
                <div class="alert alert-info">{{ session('resultado_email')['mensagem'] ?? '' }}</div>
            @endif
            @if(session('resultado_whatsapp'))
                <div class="alert alert-info">{{ session('resultado_whatsapp')['mensagem'] ?? '' }}</div>
            @endif

            <form method="POST" action="/rh/ia-externa/enviar">
                @csrf
                <input type="hidden" name="mes" value="{{ $mes ?? date('m') }}">
                <input type="hidden" name="ano" value="{{ $ano ?? date('Y') }}">

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Email destino</label>
                        <input type="email" class="form-control" name="email_destino" value="{{ $emailDestino ?? '' }}">
                        <div class="smallmuted mt-1">Use RH_IA_EMAIL_TO no .env para padrão automático.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Webhook WhatsApp</label>
                        <input type="text" class="form-control" name="whatsapp_webhook" value="{{ $whatsWebhook ?? '' }}">
                        <div class="smallmuted mt-1">Use RH_IA_WHATSAPP_WEBHOOK no .env para padrão automático.</div>
                    </div>
                </div>

                <div class="mt-3 d-flex gap-2 flex-wrap">
                    <button class="btn btn-primary" name="enviar_email" value="1">Enviar por Email</button>
                    <button class="btn btn-success" name="enviar_whatsapp" value="1">Enviar por WhatsApp</button>
                    <button class="btn btn-dark" type="submit" name="enviar_ambos" value="1">Enviar Ambos</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
