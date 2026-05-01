@extends('default.layout',['title' => 'RH - Templates Jurídicos'])
@section('content')
<div class="page-content">
    <div class="card"><div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <h5 class="mb-0">Templates Jurídicos RH</h5>
                <small class="text-muted">Base padrão BR para contratos, rescisões e documentos internos.</small>
            </div>
            <div class="d-flex gap-2">
                <a class="btn btn-outline-secondary" href="{{ route('rh.documentos.index') }}">Voltar</a>
                <a class="btn btn-primary" href="{{ route('rh.documentos.templates.create') }}">Novo template</a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead><tr><th>Nome</th><th>Categoria</th><th>Tipo</th><th>IA</th><th>Versão</th><th>Status</th><th>Ações</th></tr></thead>
                <tbody>
                    @forelse($data as $item)
                        <tr>
                            <td>
                                <div class="fw-bold">{{ $item->nome }}</div>
                                <div class="small text-muted">{{ $item->descricao }}</div>
                            </td>
                            <td>{{ $item->categoria }}</td>
                            <td>{{ $item->tipo_documento }}</td>
                            <td>{{ $item->usa_ia ? 'Sim' : 'Não' }}</td>
                            <td>{{ $item->versao }}</td>
                            <td>{{ $item->ativo ? 'Ativo' : 'Inativo' }}</td>
                            <td class="d-flex gap-1 flex-wrap">
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('rh.documentos.templates.edit', $item->id) }}"><i class="bx bx-edit"></i></a>
                                <form method="POST" action="{{ route('rh.documentos.templates.destroy', $item->id) }}" onsubmit="return confirm('Remover template?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger"><i class="bx bx-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted">Nenhum template encontrado.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $data->links() }}
    </div></div>
</div>
@endsection
