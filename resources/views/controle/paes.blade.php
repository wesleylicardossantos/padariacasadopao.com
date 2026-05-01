@extends('default.layout',['title' => 'Controle de Pães'])
@section('content')
<style>
    .controle-card{border:1px solid #e9edf5;border-radius:18px;box-shadow:0 10px 30px rgba(15,23,42,.05)}
    .controle-pill{display:inline-flex;align-items:center;gap:.45rem;padding:.45rem .8rem;border-radius:999px;background:#f3f0ff;color:#6f42c1;font-weight:600}
    .controle-kpi{border:1px solid #eef2f7;border-radius:16px;background:linear-gradient(135deg,#ffffff 0%,#fbfcff 100%)}
    .controle-kpi .label{font-size:.78rem;text-transform:uppercase;letter-spacing:.04em;color:#6c757d;font-weight:700}
    .controle-kpi .value{font-size:1.6rem;font-weight:800;color:#1f2937}
    .controle-list li{padding:.8rem 0;border-bottom:1px solid #eef2f7}
    .controle-list li:last-child{border-bottom:none}
</style>

<div class="page-content">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <div class="controle-pill mb-2"><i class='bx bx-baguette'></i> Produção e acompanhamento</div>
            <h4 class="mb-1">Controle de Pães</h4>
            <p class="text-muted mb-0">Tela inicial do novo módulo de controle. Você pode expandir este painel com produção diária, perdas, fornadas e estoque.</p>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="card controle-kpi h-100">
                <div class="card-body">
                    <div class="label">Produção prevista</div>
                    <div class="value">120 un.</div>
                    <small class="text-muted">Base inicial para acompanhamento.</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card controle-kpi h-100">
                <div class="card-body">
                    <div class="label">Produção concluída</div>
                    <div class="value">0 un.</div>
                    <small class="text-muted">Atualize conforme as fornadas forem finalizadas.</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card controle-kpi h-100">
                <div class="card-body">
                    <div class="label">Perdas do dia</div>
                    <div class="value">0 un.</div>
                    <small class="text-muted">Use este espaço para controle operacional.</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card controle-card">
        <div class="card-body p-4">
            <div class="row g-4 align-items-start">
                <div class="col-lg-7">
                    <h5 class="mb-3">Próximos passos sugeridos</h5>
                    <ul class="list-unstyled controle-list mb-0">
                        <li><strong>1.</strong> Integrar cadastro de produtos com tipo “Pães”.</li>
                        <li><strong>2.</strong> Registrar meta diária de produção por item.</li>
                        <li><strong>3.</strong> Adicionar apontamento de fornadas e perdas.</li>
                        <li><strong>4.</strong> Exibir histórico diário e comparação com vendas.</li>
                    </ul>
                </div>
                <div class="col-lg-5">
                    <div class="alert alert-light border mb-0">
                        <h6 class="mb-2">Módulo pronto para expansão</h6>
                        <p class="text-muted mb-0">O menu e a rota já estão implementados. Agora você pode conectar esta tela ao fluxo real de produção da padaria sem quebrar o restante do sistema.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
