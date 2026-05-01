@extends('default.layout',['title' => 'RH - Desligamentos'])
@section('content')
<div class="page-content">
    <div class="card">
        <div class="card-body p-4">
            @php
                $sessionUser = session('user_logged');
                $isRhAdmin = ((int) (optional(auth()->user())->adm ?? 0) === 1)
                    || ((int) data_get($sessionUser, 'adm', 0) === 1)
                    || (!empty(data_get($sessionUser, 'login')) && function_exists('isSuper') && isSuper(data_get($sessionUser, 'login')));
            @endphp

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <div>
                    <h6 class="mb-0 text-uppercase">RH - Desligamentos</h6>
                    <small class="text-muted">Registro formal de saídas, cálculo de rescisão e documentos.</small>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    @if(!empty($rescisaoInstalada))
                        <a href="{{ route('rh.desligamentos.dashboard_executivo') }}" class="btn btn-outline-primary">Dashboard executivo</a>
                        <a href="{{ route('rh.desligamentos.exportar_fgts') }}" class="btn btn-outline-success">Exportar FGTS/SEFIP</a>
                    @endif
                    <a href="{{ route('rh.desligamentos.create') }}" class="btn btn-danger">Novo desligamento</a>
                </div>
            </div>

            @if(!empty($semTabela))
                <div class="alert alert-danger">Tabela de desligamentos ainda não instalada. Execute o SQL do patch RH.</div>
            @endif

            <form method="GET" action="{{ route('rh.desligamentos.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4"><label class="form-label">Funcionário</label><input type="text" class="form-control" name="funcionario" value="{{ $funcionario ?? '' }}"></div>
                    <div class="col-md-2"><button class="btn btn-primary w-100">Filtrar</button></div>
                </div>
            </form>

            <hr>

            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Funcionário</th>
                            <th>Data</th>
                            <th>Tipo</th>
                            <th>Motivo</th>
                            <th>Observação</th>
                            @if($isRhAdmin)
                                <th class="text-center" style="width: 200px;">Ação</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @if(!empty($semTabela))
                            <tr><td colspan="{{ $isRhAdmin ? 6 : 5 }}" class="text-center">Estrutura RH pendente.</td></tr>
                        @else
                            @forelse($data as $item)
                            <tr>
                                <td>{{ optional($item->funcionario)->nome }}</td>
                                <td>{{ \Carbon\Carbon::parse($item->data_desligamento)->format('d/m/Y') }}</td>
                                <td>{{ $item->tipo }}</td>
                                <td>{{ $item->motivo }}</td>
                                <td>{{ $item->observacao }}</td>
                                @if($isRhAdmin)
                                    <td class="text-center">
                                        @if(!empty($item->rescisao_id) && !empty($rescisaoInstalada))
                                            <a href="{{ route('rh.desligamentos.show', $item->rescisao_id) }}" class="btn btn-sm btn-outline-primary" title="Detalhes"><i class="bx bx-show"></i></a>
                                        @endif
                                        <form method="POST" action="{{ route('rh.desligamentos.destroy', $item->id) }}" class="d-inline" onsubmit="return confirm('Deseja excluir este desligamento?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Excluir">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                @endif
                            </tr>
                            @empty
                            <tr><td colspan="{{ $isRhAdmin ? 6 : 5 }}" class="text-center">Nenhum desligamento encontrado.</td></tr>
                            @endforelse
                        @endif
                    </tbody>
                </table>
            </div>

            @if(empty($semTabela) && method_exists($data, 'links'))
            {!! $data->appends(request()->all())->links() !!}
            @endif
        </div>
    </div>
</div>
@endsection
