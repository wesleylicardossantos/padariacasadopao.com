@extends('default.layout', ['title' => 'PDV - Teste Web'])
@section('content')
<style>
    .pdv-card{border:1px solid #e8edf5;border-radius:16px;box-shadow:0 10px 24px rgba(15,23,42,.05)}
    .pdv-kpi{background:#0f172a;color:#fff;border-radius:14px;padding:14px 16px}
    .pdv-kpi small{display:block;opacity:.75;text-transform:uppercase;letter-spacing:.04em}
    .pdv-kpi strong{font-size:1.1rem}
    .pdv-output{background:#0b1020;color:#dbeafe;border-radius:14px;min-height:260px;padding:16px;white-space:pre-wrap;overflow:auto;font-size:.9rem}
    .pdv-label{font-size:.78rem;font-weight:700;text-transform:uppercase;color:#64748b}
    .pdv-muted{color:#64748b}
</style>

<div class="page-content">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h4 class="mb-0">PDV Offline · Tela web de teste</h4>
            <small class="pdv-muted">Use esta tela para validar login, bootstrap, status e sincronização do backend do PDV.</small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ url('/api/pdv/login') }}" target="_blank" class="btn btn-light border">Debug Login GET</a>
            <button class="btn btn-dark" type="button" onclick="pdvCopiarToken()">Copiar token</button>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-lg-3 col-md-6"><div class="pdv-kpi"><small>API Base</small><strong>{{ $apiBase }}</strong></div></div>
        <div class="col-lg-3 col-md-6"><div class="pdv-kpi"><small>Rota de login</small><strong>POST /login</strong></div></div>
        <div class="col-lg-3 col-md-6"><div class="pdv-kpi"><small>Bootstrap</small><strong>GET /bootstrap</strong></div></div>
        <div class="col-lg-3 col-md-6"><div class="pdv-kpi"><small>Sincronização</small><strong>POST /vendas/sincronizar</strong></div></div>
    </div>

    <div class="row g-3">
        <div class="col-lg-5">
            <div class="card pdv-card mb-3">
                <div class="card-body p-4">
                    <div class="mb-3">
                        <div class="pdv-label mb-1">1. Login PDV</div>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label">Login</label>
                                <input type="text" id="pdvLogin" class="form-control" placeholder="Usuário do PDV">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Senha</label>
                                <input type="password" id="pdvSenha" class="form-control" placeholder="Senha do PDV">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Empresa (opcional)</label>
                                <input type="text" id="pdvEmpresa" class="form-control" placeholder="ID, hash, nome fantasia ou razão social">
                            </div>
                        </div>
                        <div class="d-flex gap-2 mt-3">
                            <button class="btn btn-primary" type="button" onclick="pdvLogin()">Autenticar</button>
                            <button class="btn btn-outline-secondary" type="button" onclick="pdvLimparSessao()">Limpar sessão</button>
                        </div>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <div class="pdv-label mb-1">2. Token atual</div>
                        <textarea id="pdvToken" class="form-control" rows="4" placeholder="O token será preenchido após o login"></textarea>
                    </div>

                    <div class="mb-0">
                        <div class="pdv-label mb-1">3. Testes rápidos</div>
                        <div class="d-flex flex-wrap gap-2">
                            <button class="btn btn-dark" type="button" onclick="pdvBootstrap()">Consultar bootstrap</button>
                            <button class="btn btn-outline-dark" type="button" onclick="pdvStatus()">Consultar status</button>
                            <button class="btn btn-success" type="button" onclick="pdvEnviarVenda()">Enviar venda de teste</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card pdv-card">
                <div class="card-body p-4">
                    <div class="pdv-label mb-2">4. Payload de sincronização</div>
                    <p class="pdv-muted mb-2">Edite o JSON abaixo antes de enviar. O campo <code>produto_id</code> deve existir no seu banco.</p>
                    <textarea id="pdvPayload" class="form-control" rows="18">{{ $payloadExemploJson }}</textarea>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card pdv-card h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <div class="pdv-label">Saída da API</div>
                            <small class="pdv-muted">Respostas das rotas do PDV aparecem aqui.</small>
                        </div>
                        <button class="btn btn-sm btn-light border" type="button" onclick="pdvLimparOutput()">Limpar saída</button>
                    </div>
                    <pre id="pdvOutput" class="pdv-output">Aguardando ação...</pre>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const PDV_API_BASE = @json($apiBase);

    function pdvSetOutput(title, payload) {
        const text = typeof payload === 'string' ? payload : JSON.stringify(payload, null, 2);
        document.getElementById('pdvOutput').textContent = `[${new Date().toLocaleString()}] ${title}\n\n${text}`;
    }

    function pdvGetToken() {
        return document.getElementById('pdvToken').value.trim();
    }

    function pdvPersistirToken(token) {
        document.getElementById('pdvToken').value = token || '';
        localStorage.setItem('pdv_test_token', token || '');
    }

    function pdvCarregarSessao() {
        const token = localStorage.getItem('pdv_test_token') || '';
        document.getElementById('pdvToken').value = token;
    }

    function pdvLimparSessao() {
        pdvPersistirToken('');
        pdvSetOutput('Sessão limpa', { ok: true, message: 'Token removido do navegador.' });
    }

    function pdvLimparOutput() {
        pdvSetOutput('Saída limpa', { ok: true });
    }

    async function pdvRequest(path, method = 'GET', body = null, withToken = false) {
        const headers = { 'Accept': 'application/json' };
        if (body !== null) headers['Content-Type'] = 'application/json';
        if (withToken) {
            const token = pdvGetToken();
            if (!token) {
                throw new Error('Faça o login do PDV antes de chamar esta rota.');
            }
            headers['token'] = token;
            headers['authorization-token'] = token;
        }

        const response = await fetch(PDV_API_BASE + path, {
            method,
            headers,
            body: body !== null ? JSON.stringify(body) : null,
        });

        const text = await response.text();
        let data;
        try {
            data = JSON.parse(text);
        } catch (e) {
            data = { raw: text };
        }

        if (!response.ok) {
            throw { status: response.status, data };
        }

        return data;
    }

    async function pdvLogin() {
        const payload = {
            login: document.getElementById('pdvLogin').value.trim(),
            senha: document.getElementById('pdvSenha').value,
            empresa: document.getElementById('pdvEmpresa').value.trim() || null,
        };

        try {
            const data = await pdvRequest('/login', 'POST', payload, false);
            pdvPersistirToken(data.token || data.terminal_token || '');
            pdvSetOutput('Login PDV realizado', data);
        } catch (error) {
            pdvSetOutput('Erro no login PDV', error);
        }
    }

    async function pdvBootstrap() {
        try {
            const data = await pdvRequest('/bootstrap', 'GET', null, true);
            pdvSetOutput('Bootstrap carregado', data);
        } catch (error) {
            pdvSetOutput('Erro no bootstrap', error);
        }
    }

    async function pdvStatus() {
        try {
            const data = await pdvRequest('/sync/status', 'GET', null, true);
            pdvSetOutput('Status de sincronização', data);
        } catch (error) {
            pdvSetOutput('Erro ao consultar status', error);
        }
    }

    async function pdvEnviarVenda() {
        try {
            const payload = JSON.parse(document.getElementById('pdvPayload').value);
            const data = await pdvRequest('/vendas/sincronizar', 'POST', payload, true);
            pdvSetOutput('Venda de teste enviada', data);
        } catch (error) {
            pdvSetOutput('Erro ao sincronizar venda', error.message ? { message: error.message } : error);
        }
    }

    function pdvCopiarToken() {
        const token = pdvGetToken();
        if (!token) {
            pdvSetOutput('Token vazio', { ok: false, message: 'Nenhum token salvo ainda.' });
            return;
        }
        navigator.clipboard.writeText(token).then(() => {
            pdvSetOutput('Token copiado', { ok: true });
        }).catch(() => {
            pdvSetOutput('Token disponível', { ok: true, token });
        });
    }

    pdvCarregarSessao();
</script>
@endsection
