@extends('default.layout',['title' => 'RH - Férias'])
@section('content')
<div class="page-content">
    <div class="card">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <div>
                    <h6 class="mb-0 text-uppercase">RH - Férias</h6>
                    <small class="text-muted">Controle de períodos aquisitivos, programação e status.</small>
                </div>
                @php($isRhAdmin = ((int) (optional(auth()->user())->adm ?? 0) === 1))
                <a href="/rh/ferias/create" class="btn btn-success"><i class="bx bx-plus"></i> Nova programação</a>
            </div>

            @if(!empty($semTabela))
                <div class="alert alert-danger">Estrutura de férias ainda não instalada. Execute o SQL do patch RH V3.</div>
            @endif

            @if(!empty($schemaLegado))
                <div class="alert alert-warning">Base antiga detectada: listagem adaptada para a estrutura atual do banco.</div>
            @endif

            <form method="GET" action="/rh/ferias">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Funcionário</label>
                        <input type="text" class="form-control" name="funcionario" value="{{ $funcionario ?? '' }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <option value="">Todos</option>
                            <option value="programada" @if(($status ?? '') == 'programada') selected @endif>Programada</option>
                            <option value="PROGRAMADA" @if(($status ?? '') == 'PROGRAMADA') selected @endif>PROGRAMADA</option>
                            <option value="gozo" @if(($status ?? '') == 'gozo') selected @endif>Em gozo</option>
                            <option value="concluida" @if(($status ?? '') == 'concluida') selected @endif>Concluída</option>
                            <option value="pendente" @if(($status ?? '') == 'pendente') selected @endif>Pendente</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary w-100">Filtrar</button>
                    </div>
                </div>
            </form>

            <hr>

            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Funcionário</th>
                            <th>Período aquisitivo</th>
                            <th>Gozo</th>
                            <th>Dias</th>
                            <th>Status</th>
                            <th>Obs.</th>
                            @if($isRhAdmin)
                                <th class="text-center" style="width: 120px;">Ações</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                    @if(!empty($semTabela))
                        <tr><td colspan="{{ $isRhAdmin ? 7 : 6 }}" class="text-center">Estrutura RH V3 pendente.</td></tr>
                    @else
                        @forelse($data as $item)
                        <tr>
                            <td>{{ optional($item->funcionario)->nome }}</td>
                            <td>
                                @if(!empty($item->periodo_aquisitivo_inicio) && !empty($item->periodo_aquisitivo_fim))
                                    {{ \Carbon\Carbon::parse($item->periodo_aquisitivo_inicio)->format('d/m/Y') }} até {{ \Carbon\Carbon::parse($item->periodo_aquisitivo_fim)->format('d/m/Y') }}
                                @else
                                    —
                                @endif
                            </td>
                            <td>{{ \Carbon\Carbon::parse($item->data_inicio)->format('d/m/Y') }} até {{ \Carbon\Carbon::parse($item->data_fim)->format('d/m/Y') }}</td>
                            <td>{{ $item->dias ?? (\Carbon\Carbon::parse($item->data_inicio)->diffInDays(\Carbon\Carbon::parse($item->data_fim)) + 1) }}</td>
                            <td><span class="badge bg-secondary">{{ ucfirst(strtolower($item->status)) }}</span></td>
                            <td>{{ $item->observacao }}</td>
                            @if($isRhAdmin)
                                <td class="text-center">
                                    <div class="d-inline-flex align-items-center gap-2">
                                        <a href="{{ route('rh.ferias.edit', $item->id) }}" class="btn btn-sm btn-outline-primary" title="Editar">
                                            <i class="bx bx-edit-alt"></i>
                                        </a>
                                        <form method="POST" action="{{ route('rh.ferias.destroy', $item->id) }}" class="d-inline" onsubmit="return confirm('Deseja excluir esta programação de férias?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Excluir">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            @endif
                        </tr>
                        @empty
                        <tr><td colspan="{{ $isRhAdmin ? 7 : 6 }}" class="text-center">Nenhuma programação encontrada.</td></tr>
                        @endforelse
                    @endif
                    </tbody>
                </table>
            </div>

            @if(empty($semTabela))
            {!! $data->appends(request()->all())->links() !!}
            @endif
        </div>
    </div>
</div>
@endsection
