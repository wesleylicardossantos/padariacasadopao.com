@extends('rh.portal_funcionario.layout_externo',['title' => 'Dossiê do funcionário'])
@section('content')
<div class="page-content">
    <div class="card">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                <div>
                    <h4 class="mb-1">Dossiê do funcionário</h4>
                    <div class="text-muted">Linha do tempo documental e eventos do colaborador.</div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('rh.portal_externo.dashboard') }}" class="btn btn-light"><i class="bx bx-arrow-back"></i> Voltar</a>
                    <a href="{{ route('rh.portal_externo.logout') }}" class="btn btn-outline-secondary"><i class="bx bx-log-out"></i> Sair</a>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-3"><div class="card border shadow-none h-100 mb-0"><div class="card-body"><div class="text-muted">Status do dossiê</div><h5 class="mb-0">{{ $dossie->status ?? 'ativo' }}</h5></div></div></div>
                <div class="col-md-3"><div class="card border shadow-none h-100 mb-0"><div class="card-body"><div class="text-muted">Eventos</div><h3 class="mb-0">{{ $eventos->count() }}</h3></div></div></div>
                <div class="col-md-3"><div class="card border shadow-none h-100 mb-0"><div class="card-body"><div class="text-muted">Documentos</div><h3 class="mb-0">{{ $documentos->count() }}</h3></div></div></div>
                <div class="col-md-3"><div class="card border shadow-none h-100 mb-0"><div class="card-body"><div class="text-muted">Rescisões</div><h3 class="mb-0">{{ $rescisoes->count() }}</h3></div></div></div>
            </div>

            @if($rescisoes->count() > 0)
                <div class="alert alert-warning border d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <strong>Documentos rescisórios disponíveis</strong><br>
                        <span class="text-muted">Seu perfil permite consultar os arquivos de rescisão publicados no portal.</span>
                    </div>
                    <a href="{{ route('rh.portal_externo.documentos_rescisao') }}" class="btn btn-dark"><i class="bx bx-folder-open"></i> Abrir documentos de rescisão</a>
                </div>
            @endif

            <div class="row g-4">
                <div class="col-lg-7">
                    <div class="card border mb-0">
                        <div class="card-header bg-light"><strong>Linha do tempo</strong></div>
                        <div class="card-body">
                            @forelse($eventos as $evento)
                                <div class="border-start ps-3 mb-3">
                                    <div class="fw-bold">{{ $evento->titulo }}</div>
                                    <div class="small text-muted">{{ optional($evento->data_evento)->format('d/m/Y') ?? '--' }} · {{ $evento->categoria }}</div>
                                    <div>{{ $evento->descricao ?: 'Sem descrição adicional.' }}</div>
                                </div>
                            @empty
                                <div class="text-muted">Nenhum evento visível no dossiê até o momento.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="card border mb-0">
                        <div class="card-header bg-light"><strong>Documentos recentes</strong></div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped mb-0">
                                    <thead><tr><th>Nome</th><th>Tipo</th><th>Status</th></tr></thead>
                                    <tbody>
                                    @forelse($documentos as $documento)
                                        <tr>
                                            <td>{{ $documento->nome ?? ('Documento #' . $documento->id) }}</td>
                                            <td>{{ $documento->tipo ?? '--' }}</td>
                                            <td>{{ $documento->status ?? 'disponivel' }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="3" class="text-center py-4">Nenhum documento recente encontrado.</td></tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
