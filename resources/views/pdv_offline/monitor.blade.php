@extends('default.layout', ['title' => 'PDV Offline - Monitor de Sincronização'])

@section('css')
<style>
    .sync-card {
        border: 1px solid #edf0f5;
        border-radius: 16px;
        box-shadow: 0 6px 18px rgba(15, 23, 42, 0.06);
    }
    .sync-badge {
        font-size: 11px;
        padding: 6px 10px;
        border-radius: 999px;
        text-transform: uppercase;
        letter-spacing: .04em;
    }
    .sync-status-pendente { background: #fff4de; color: #946200; }
    .sync-status-sincronizando { background: #e8f4ff; color: #135ca8; }
    .sync-status-sincronizado { background: #e7f7ee; color: #18794e; }
    .sync-status-duplicado { background: #efe8ff; color: #6941c6; }
    .sync-status-erro { background: #fdecec; color: #b42318; }
    .metric-number { font-size: 28px; font-weight: 700; }
    .metric-label { color: #6c757d; font-size: 13px; }
    .monitor-table td, .monitor-table th { vertical-align: middle; }
    .payload-box {
        background: #0f172a;
        color: #e2e8f0;
        border-radius: 12px;
        padding: 12px;
        font-size: 12px;
        max-height: 220px;
        overflow: auto;
        white-space: pre-wrap;
        word-break: break-word;
    }
    .error-list li:last-child { border-bottom: none !important; }
</style>
@endsection

@section('content')
@php
    $metricas = $payload['metricas'] ?? [];
    $lista = $payload['lista']['data'] ?? [];
    $ultimosErros = $payload['ultimos_erros'] ?? [];
@endphp
<div class="page-content">
    <div class="container-fluid">
        <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Monitor do PDV Offline</h4>
                <div class="text-muted">Empresa #{{ $empresaId }} • atualização em <span id="consultadoEm">{{ $payload['consultado_em'] ?? '--' }}</span></div>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-secondary" id="btnRefresh"><i class="bx bx-refresh"></i> Atualizar</button>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-6 col-xl-2">
                <div class="card sync-card h-100"><div class="card-body"><div class="metric-number" id="mPendentes">{{ $metricas['pendentes'] ?? 0 }}</div><div class="metric-label">Pendentes</div></div></div>
            </div>
            <div class="col-md-6 col-xl-2">
                <div class="card sync-card h-100"><div class="card-body"><div class="metric-number" id="mSincronizando">{{ $metricas['sincronizando'] ?? 0 }}</div><div class="metric-label">Sincronizando</div></div></div>
            </div>
            <div class="col-md-6 col-xl-2">
                <div class="card sync-card h-100"><div class="card-body"><div class="metric-number" id="mSincronizadas">{{ $metricas['sincronizadas'] ?? 0 }}</div><div class="metric-label">Sincronizadas</div></div></div>
            </div>
            <div class="col-md-6 col-xl-2">
                <div class="card sync-card h-100"><div class="card-body"><div class="metric-number" id="mDuplicadas">{{ $metricas['duplicadas'] ?? 0 }}</div><div class="metric-label">Duplicadas</div></div></div>
            </div>
            <div class="col-md-6 col-xl-2">
                <div class="card sync-card h-100"><div class="card-body"><div class="metric-number" id="mErros">{{ $metricas['com_erro'] ?? 0 }}</div><div class="metric-label">Com erro</div></div></div>
            </div>
            <div class="col-md-6 col-xl-2">
                <div class="card sync-card h-100"><div class="card-body"><div class="fw-bold small text-muted mb-2">Última sincronização</div><div id="mUltimaSync">{{ $metricas['ultima_sincronizacao_em'] ?? '--' }}</div></div></div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-12 col-xl-9">
                <div class="card sync-card">
                    <div class="card-body">
                        <form id="filtrosForm" class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status" id="statusFilter">
                                    <option value="">Todos</option>
                                    <option value="pendente">Pendente</option>
                                    <option value="sincronizando">Sincronizando</option>
                                    <option value="sincronizado">Sincronizado</option>
                                    <option value="duplicado">Duplicado</option>
                                    <option value="erro">Erro</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">UUID local</label>
                                <input type="text" class="form-control" name="uuid_local" id="uuidFilter" placeholder="Buscar UUID local">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Itens</label>
                                <select class="form-select" name="per_page" id="perPageFilter">
                                    <option value="20">20</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                            </div>
                        </form>

                        <div class="table-responsive">
                            <table class="table table-hover monitor-table align-middle">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>UUID local</th>
                                    <th>Status</th>
                                    <th>Venda caixa</th>
                                    <th>Tentativas</th>
                                    <th>Última tentativa</th>
                                    <th>Sincronizado em</th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody id="monitorTableBody">
                                @forelse($lista as $item)
                                    <tr>
                                        <td>{{ $item['id'] }}</td>
                                        <td class="text-break">{{ $item['uuid_local'] }}</td>
                                        <td><span class="sync-badge sync-status-{{ $item['status'] }}">{{ $item['status'] }}</span></td>
                                        <td>{{ $item['venda_caixa_id'] ?? '--' }}</td>
                                        <td>{{ $item['tentativas'] }}</td>
                                        <td>{{ $item['ultima_tentativa_em'] ?? '--' }}</td>
                                        <td>{{ $item['sincronizado_em'] ?? '--' }}</td>
                                        <td class="text-end">
                                            <button class="btn btn-sm btn-outline-dark btnDetalhes" type="button" data-item='@json($item)'>Detalhes</button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="8" class="text-center text-muted py-4">Nenhum registro encontrado.</td></tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-3 small text-muted">
                            <div id="paginationResume">
                                Exibindo {{ $payload['lista']['from'] ?? 0 }}-{{ $payload['lista']['to'] ?? 0 }} de {{ $payload['lista']['total'] ?? 0 }} registros
                            </div>
                            <div id="pageInfo">
                                Página {{ $payload['lista']['current_page'] ?? 1 }} de {{ $payload['lista']['last_page'] ?? 1 }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-xl-3">
                <div class="card sync-card mb-3">
                    <div class="card-body">
                        <h6 class="mb-3">Últimos erros</h6>
                        <ul class="list-unstyled mb-0 error-list" id="errorList">
                            @forelse($ultimosErros as $erro)
                                <li class="border-bottom pb-2 mb-2">
                                    <div class="fw-bold text-break">{{ $erro['uuid_local'] }}</div>
                                    <div class="small text-muted mb-1">Tentativas: {{ $erro['tentativas'] }} • {{ $erro['ultima_tentativa_em'] ?? '--' }}</div>
                                    <div class="small text-danger">{{ $erro['erro'] }}</div>
                                </li>
                            @empty
                                <li class="text-muted">Sem erros recentes.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
                <div class="card sync-card">
                    <div class="card-body">
                        <h6 class="mb-3">Detalhes do registro</h6>
                        <div id="detailEmpty" class="text-muted">Clique em “Detalhes” para visualizar request, response e erro.</div>
                        <div id="detailContent" class="d-none">
                            <div class="mb-2"><strong>UUID:</strong> <span id="detailUuid"></span></div>
                            <div class="mb-2"><strong>Status:</strong> <span id="detailStatus"></span></div>
                            <div class="mb-2"><strong>Erro:</strong> <span id="detailErro"></span></div>
                            <div class="mb-2">
                                <div class="fw-bold mb-1">Request payload</div>
                                <div class="payload-box" id="detailRequest"></div>
                            </div>
                            <div>
                                <div class="fw-bold mb-1">Response payload</div>
                                <div class="payload-box" id="detailResponse"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
(function () {
    const empresaId = @json($empresaId);
    const endpoint = @json(route('pdv.offline.monitor.data'));
    const body = document.getElementById('monitorTableBody');
    const errorList = document.getElementById('errorList');
    const consultedAt = document.getElementById('consultadoEm');
    const filtersForm = document.getElementById('filtrosForm');
    const statusFilter = document.getElementById('statusFilter');
    const uuidFilter = document.getElementById('uuidFilter');
    const perPageFilter = document.getElementById('perPageFilter');

    statusFilter.value = @json($payload['filtros']['status'] ?? '');
    uuidFilter.value = @json($payload['filtros']['uuid_local'] ?? '');
    perPageFilter.value = String(@json($payload['filtros']['per_page'] ?? 20));

    function statusBadge(status) {
        return `<span class="sync-badge sync-status-${status}">${status}</span>`;
    }

    function safeJson(data) {
        if (data === null || data === undefined || data === '') return '--';
        try {
            return JSON.stringify(data, null, 2);
        } catch (e) {
            return String(data);
        }
    }

    function fillDetails(item) {
        document.getElementById('detailEmpty').classList.add('d-none');
        document.getElementById('detailContent').classList.remove('d-none');
        document.getElementById('detailUuid').textContent = item.uuid_local || '--';
        document.getElementById('detailStatus').textContent = item.status || '--';
        document.getElementById('detailErro').textContent = item.erro || '--';
        document.getElementById('detailRequest').textContent = safeJson(item.request_payload);
        document.getElementById('detailResponse').textContent = safeJson(item.response_payload);
    }

    function bindDetailButtons() {
        document.querySelectorAll('.btnDetalhes').forEach(btn => {
            btn.addEventListener('click', function () {
                fillDetails(JSON.parse(this.dataset.item));
            });
        });
    }

    function buildRows(items) {
        if (!items.length) {
            body.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-4">Nenhum registro encontrado.</td></tr>';
            return;
        }

        body.innerHTML = items.map(item => `
            <tr>
                <td>${item.id}</td>
                <td class="text-break">${item.uuid_local ?? '--'}</td>
                <td>${statusBadge(item.status)}</td>
                <td>${item.venda_caixa_id ?? '--'}</td>
                <td>${item.tentativas}</td>
                <td>${item.ultima_tentativa_em ?? '--'}</td>
                <td>${item.sincronizado_em ?? '--'}</td>
                <td class="text-end">
                    <button class="btn btn-sm btn-outline-dark btnDetalhes" type="button" data-item='${JSON.stringify(item).replace(/'/g, '&#39;')}'>Detalhes</button>
                </td>
            </tr>
        `).join('');

        bindDetailButtons();
    }

    function buildErrorList(items) {
        if (!items.length) {
            errorList.innerHTML = '<li class="text-muted">Sem erros recentes.</li>';
            return;
        }

        errorList.innerHTML = items.map(item => `
            <li class="border-bottom pb-2 mb-2">
                <div class="fw-bold text-break">${item.uuid_local}</div>
                <div class="small text-muted mb-1">Tentativas: ${item.tentativas} • ${item.ultima_tentativa_em ?? '--'}</div>
                <div class="small text-danger">${item.erro ?? '--'}</div>
            </li>
        `).join('');
    }

    function updateMetrics(metricas) {
        document.getElementById('mPendentes').textContent = metricas.pendentes ?? 0;
        document.getElementById('mSincronizando').textContent = metricas.sincronizando ?? 0;
        document.getElementById('mSincronizadas').textContent = metricas.sincronizadas ?? 0;
        document.getElementById('mDuplicadas').textContent = metricas.duplicadas ?? 0;
        document.getElementById('mErros').textContent = metricas.com_erro ?? 0;
        document.getElementById('mUltimaSync').textContent = metricas.ultima_sincronizacao_em ?? '--';
    }

    async function refreshData() {
        const params = new URLSearchParams({
            empresa_id: empresaId,
            status: statusFilter.value,
            uuid_local: uuidFilter.value,
            per_page: perPageFilter.value
        });

        const response = await fetch(`${endpoint}?${params.toString()}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });

        if (!response.ok) {
            throw new Error('Não foi possível atualizar o monitor do PDV offline.');
        }

        const data = await response.json();
        consultedAt.textContent = data.consultado_em || '--';
        updateMetrics(data.metricas || {});
        buildRows((data.lista && data.lista.data) ? data.lista.data : []);
        buildErrorList(data.ultimos_erros || []);
        document.getElementById('paginationResume').textContent = `Exibindo ${data.lista?.from ?? 0}-${data.lista?.to ?? 0} de ${data.lista?.total ?? 0} registros`;
        document.getElementById('pageInfo').textContent = `Página ${data.lista?.current_page ?? 1} de ${data.lista?.last_page ?? 1}`;
    }

    filtersForm.addEventListener('submit', async function (e) {
        e.preventDefault();
        await refreshData();
    });

    document.getElementById('btnRefresh').addEventListener('click', async function () {
        await refreshData();
    });

    bindDetailButtons();
    setInterval(() => {
        refreshData().catch(() => {});
    }, 30000);
})();
</script>
@endsection
