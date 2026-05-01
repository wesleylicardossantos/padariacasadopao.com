@extends('default.layout',['title' => 'RH - Documentos Inteligentes'])
@section('content')
<div class="page-content">
    @if(!$hasTable)
    <div class="alert alert-warning">A tabela <strong>rh_documentos</strong> ainda não existe. Execute o SQL do módulo RH.</div>
    @endif

    <div class="row g-3 mb-3">
        <div class="col-md-4"><div class="card"><div class="card-body"><small class="text-muted d-block">Templates jurídicos ativos</small><h4 class="mb-0">{{ $templatesAtivos }}</h4></div></div></div>
        <div class="col-md-4"><div class="card"><div class="card-body"><small class="text-muted d-block">Documentos gerados com IA</small><h4 class="mb-0">{{ $documentosIa }}</h4></div></div></div>
        <div class="col-md-4"><div class="card"><div class="card-body"><small class="text-muted d-block">Integração com dossiê</small><h4 class="mb-0">Automática</h4></div></div></div>
    </div>

    <div class="card mb-3"><div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <h5 class="mb-0">Documentos Inteligentes RH</h5>
                <small class="text-muted">Geração com IA, templates jurídicos BR e salvamento automático no dossiê.</small>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a class="btn btn-outline-primary" href="{{ route('rh.documentos.templates.index') }}">Templates Jurídicos</a>
                <a class="btn btn-success" href="{{ route('rh.documentos.create') }}">Gerar documento</a>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-7">
                <div class="border rounded p-3 h-100">
                    <h6 class="mb-2">Templates prontos</h6>
                    <div class="row g-2">
                        @forelse($templates->take(8) as $template)
                            <div class="col-md-6">
                                <div class="border rounded p-2 h-100">
                                    <small class="text-muted text-uppercase">{{ $template->categoria }}</small>
                                    <div class="fw-bold">{{ $template->nome }}</div>
                                    <div class="small text-muted">{{ $template->descricao }}</div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12"><div class="text-muted">Nenhum template jurídico disponível ainda.</div></div>
                        @endforelse
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="border rounded p-3 h-100 bg-light">
                    <h6 class="mb-2">Fluxo de uso</h6>
                    <ol class="mb-0 ps-3">
                        <li>Selecione o funcionário e o template jurídico.</li>
                        <li>Ative a IA para revisar ou complementar o documento.</li>
                        <li>Gere o PDF A4 automaticamente.</li>
                        <li>O sistema salva o documento no dossiê do funcionário.</li>
                    </ol>
                </div>
            </div>
        </div>
    </div></div>

    <div class="card"><div class="card-body p-4">
        {!! Form::open()->fill(request()->all())->get() !!}
        <div class="row g-3 align-items-end mb-3">
            <div class="col-md-6">{!! Form::select('funcionario_id', 'Funcionário', ['' => 'Todos'] + $funcionarios->pluck('nome','id')->all())->attrs(['class' => 'select2']) !!}</div>
            <div class="col-md-6"><button class="btn btn-primary">Filtrar</button> <a class="btn btn-danger" href="{{ route('rh.documentos.index') }}">Limpar</a></div>
        </div>
        {!! Form::close() !!}

        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead><tr><th>Funcionário</th><th>Tipo</th><th>Nome</th><th>Origem</th><th>Status</th><th>Validade</th><th>Arquivo</th><th>Ações</th></tr></thead>
                <tbody>
                @if($hasTable)
                    @forelse($data as $item)
                    <tr>
                        <td>{{ $item->funcionario_nome }}</td>
                        <td>{{ $item->tipo }}</td>
                        <td>{{ $item->nome }}</td>
                        <td>{{ $item->origem ?: '-' }}</td>
                        <td>{{ $item->status ?: '-' }}</td>
                        <td>{{ !empty($item->validade) ? \Carbon\Carbon::parse($item->validade)->format('d/m/Y') : '-' }}</td>
                        <td>
                            @if(!empty($item->arquivo))
                            <a href="{{ route('rh.documentos.download', $item->id) }}" target="_blank" class="btn btn-sm btn-primary">PDF</a>
                            @else
                            <span class="text-muted">Sem arquivo</span>
                            @endif
                        </td>
                        <td class="d-flex gap-1 flex-wrap">
                            @if(!empty($item->conteudo_html))
                            <a class="btn btn-outline-secondary btn-sm" href="{{ route('rh.documentos.preview', $item->id) }}"><i class="bx bx-show"></i></a>
                            @endif
                            <form method="POST" action="{{ route('rh.documentos.destroy', $item->id) }}" onsubmit="return confirm('Remover documento?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm"><i class="bx bx-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted">Sem documentos.</td></tr>
                    @endforelse
                @else
                    <tr><td colspan="8" class="text-center text-muted">Módulo ainda não instalado no banco.</td></tr>
                @endif
                </tbody>
            </table>
        </div>
        @if($hasTable && method_exists($data, 'links'))
        {{ $data->appends(request()->all())->links() }}
        @endif
    </div></div>
</div>
@endsection
