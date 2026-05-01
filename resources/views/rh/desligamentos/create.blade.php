@extends('default.layout',['title' => 'RH - Novo Desligamento'])
@section('content')
<div class="page-content">
    <div class="card">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h6 class="mb-0 text-uppercase">Novo desligamento</h6>
                    <small class="text-muted">Fases 1, 2 e 3 integradas ao fluxo de rescisão.</small>
                </div>
                <a href="{{ route('rh.desligamentos.index') }}" class="btn btn-secondary">Voltar</a>
            </div>

            @if(empty($podeGerarRescisao))
                <div class="alert alert-warning">A engine de rescisão ainda não está instalada no banco. Execute a migration/SQL antes de salvar.</div>
            @endif

            <form method="POST" action="{{ route('rh.desligamentos.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Funcionário</label>
                        <select class="form-select" name="funcionario_id" required>
                            <option value="">Selecione</option>
                            @foreach($funcionarios as $item)
                                <option value="{{ $item->id }}">{{ $item->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Data desligamento</label>
                        <input type="date" class="form-control" name="data_desligamento" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tipo</label>
                        <input type="text" class="form-control" name="tipo" placeholder="Ex: sem justa causa" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Tipo aviso</label>
                        <select class="form-select" name="tipo_aviso">
                            <option value="indenizado">Indenizado</option>
                            <option value="trabalhado">Trabalhado</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Motivo</label>
                        <input type="text" class="form-control" name="motivo" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Dependentes IRRF</label>
                        <input type="number" min="0" max="20" class="form-control" name="dependentes_irrf" value="0">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Descontos extras</label>
                        <input type="number" step="0.01" min="0" class="form-control" name="descontos_extras" value="0.00">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Observação</label>
                        <textarea class="form-control" name="observacao" rows="2"></textarea>
                    </div>
                </div>

                <div class="row g-3 mt-2">
                    <div class="col-md-2"><div class="form-check"><input class="form-check-input" type="checkbox" checked name="gerar_trct" id="gerar_trct"><label class="form-check-label" for="gerar_trct">Gerar TRCT</label></div></div>
                    <div class="col-md-2"><div class="form-check"><input class="form-check-input" type="checkbox" checked name="gerar_tqrct" id="gerar_tqrct"><label class="form-check-label" for="gerar_tqrct">Gerar TQRCT</label></div></div>
                    <div class="col-md-3"><div class="form-check"><input class="form-check-input" type="checkbox" checked name="gerar_homologacao" id="gerar_homologacao"><label class="form-check-label" for="gerar_homologacao">Gerar homologação</label></div></div>
                    <div class="col-md-2"><div class="form-check"><input class="form-check-input" type="checkbox" checked name="bloquear_portal" id="bloquear_portal"><label class="form-check-label" for="bloquear_portal">Bloquear portal</label></div></div>
                    <div class="col-md-3"><div class="form-check"><input class="form-check-input" type="checkbox" checked name="arquivo_morto" id="arquivo_morto"><label class="form-check-label" for="arquivo_morto">Enviar ao arquivo morto</label></div></div>
                </div>
                <div class="mt-4 d-flex gap-2 flex-wrap">
                    <button class="btn btn-danger">Salvar desligamento e processar rescisão</button>
                    <a href="{{ route('rh.desligamentos.dashboard_executivo') }}" class="btn btn-outline-primary">Dashboard executivo</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
