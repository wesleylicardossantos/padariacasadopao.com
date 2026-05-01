@extends('rh.portal_funcionario.layout_externo',['title' => 'Documentos de rescisão'])
@section('content')
<div class="page-content">
    <div class="card"><div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
            <div>
                <h4 class="mb-1">Documentos de rescisão</h4>
                <div class="text-muted">Consulta externa dos documentos de desligamento de {{ $funcionario->nome }}</div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('rh.portal_externo.dashboard') }}" class="btn btn-outline-primary">Portal</a>
                <a href="{{ route('rh.portal_externo.logout') }}" class="btn btn-light">Sair</a>
            </div>
        </div>
        <div class="table-responsive"><table class="table table-striped">
            <thead><tr><th>Data</th><th>Motivo</th><th class="text-end">Total líquido</th><th class="text-end">Documentos</th></tr></thead>
            <tbody>
                @forelse($rescisoes as $rescisao)
                    <tr>
                        <td>{{ optional($rescisao->data_rescisao)->format('d/m/Y') }}</td>
                        <td>{{ $rescisao->motivo }}</td>
                        <td class="text-end">{{ __moeda($rescisao->total_liquido) }}</td>
                        <td class="text-end d-flex justify-content-end gap-2 flex-wrap">
                            <a target="_blank" href="{{ route('rh.portal_externo.documentos_rescisao.trct.pdf', $rescisao->id) }}" class="btn btn-sm btn-outline-danger">TRCT</a>
                            <a target="_blank" href="{{ route('rh.portal_externo.documentos_rescisao.tqrct.pdf', $rescisao->id) }}" class="btn btn-sm btn-outline-secondary">TQRCT</a>
                            <a target="_blank" href="{{ route('rh.portal_externo.documentos_rescisao.homologacao.pdf', $rescisao->id) }}" class="btn btn-sm btn-outline-primary">Homologação</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center">Nenhum documento de rescisão disponível.</td></tr>
                @endforelse
            </tbody>
        </table></div>
    </div></div>
</div>
@endsection
