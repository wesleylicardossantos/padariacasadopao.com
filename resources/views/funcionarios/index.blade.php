@extends('default.layout',['title' => 'Funcionários'])
@section('content')
@php
    $filters = $filters ?? [
        'nome' => request('nome', ''),
        'cpf' => request('cpf', ''),
        'status' => request('status', ''),
        'arquivo_morto' => request('arquivo_morto', '0'),
    ];
    $arquivoMorto = $arquivoMorto ?? request()->boolean('arquivo_morto');
    $resumoLista = $resumoLista ?? ['total' => 0, 'ativos' => 0, 'inativos' => 0];
@endphp
<style>
.func-card{border:1px solid #e8edf5;border-radius:16px;box-shadow:0 8px 24px rgba(15,23,42,.04)}
.func-table thead th{background:#f8fafc;border-bottom:2px solid #dbe4f0;white-space:nowrap;cursor:pointer}
.func-table tbody td{padding:.58rem .72rem;vertical-align:middle}
.func-table tbody tr:hover{background:#f8fbff}
.func-cpf{white-space:nowrap}
.func-actions{position:relative;display:inline-block}
.func-menu{display:none;position:absolute;right:0;top:calc(100% + 6px);min-width:210px;background:#fff;border:1px solid #e8edf5;border-radius:12px;box-shadow:0 14px 30px rgba(15,23,42,.12);z-index:20}
.func-actions.open .func-menu{display:block}
.func-menu a,.func-menu .func-menu-button{display:flex;align-items:center;gap:.55rem;padding:.72rem .85rem;text-decoration:none;color:#0f172a;border-bottom:1px solid #f1f5f9;width:100%;background:transparent;border-left:0;border-right:0;border-top:0;text-align:left}
.func-menu a:last-child,.func-menu .func-menu-button:last-child{border-bottom:none}.func-menu .func-menu-button:hover{background:#f8fafc}
.func-status{border-radius:999px;padding:.28rem .55rem;font-size:.76rem;font-weight:700}
.func-tabbar{display:flex;gap:.75rem;flex-wrap:wrap;margin-bottom:1rem}
.func-tab{display:flex;align-items:center;gap:.55rem;padding:.75rem 1rem;border:1px solid #dbe4f0;border-radius:14px;text-decoration:none;color:#334155;background:#fff;font-weight:600}
.func-tab.active{background:#eef4ff;border-color:#6d5dfc;color:#4338ca;box-shadow:0 8px 24px rgba(67,56,202,.08)}
.func-kpi{padding:.8rem 1rem;border-radius:14px;background:#f8fafc;border:1px solid #e2e8f0;min-width:160px}
.func-kpi strong{display:block;font-size:1.2rem;color:#0f172a}
</style>

<div class="page-content">
    <div class="func-tabbar">
        <a href="{{ route('funcionarios.index', ['status' => 1]) }}" class="func-tab {{ !$arquivoMorto ? 'active' : '' }}">
            <i class="bx bx-user-check"></i>
            <span>Funcionários ativos</span>
            <span class="badge bg-success">{{ $resumoLista['ativos'] ?? 0 }}</span>
        </a>
        <a href="{{ route('funcionarios.index', ['arquivo_morto' => 1, 'status' => 0]) }}" class="func-tab {{ $arquivoMorto ? 'active' : '' }}">
            <i class="bx bx-archive-in"></i>
            <span>Arquivo morto</span>
            <span class="badge bg-secondary">{{ $resumoLista['inativos'] ?? 0 }}</span>
        </a>
    </div>

    <div class="card func-card">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                <div>
                    <h5 class="mb-0">{{ $arquivoMorto ? 'Arquivo morto de funcionários' : 'Funcionários ativos' }}</h5>
                    <small class="text-muted">
                        {{ $arquivoMorto ? 'Colaboradores inativos arquivados para consulta e histórico.' : 'Visão ERP integrada ao RH com foco apenas nos colaboradores ativos.' }}
                    </small>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('funcionarios.exportPdf', $filters) }}" target="_blank" class="btn btn-danger"><i class="bx bx-file"></i> PDF</a>
                    <a href="{{ route('funcionarios.exportExcel', $filters) }}" class="btn btn-dark"><i class="bx bx-spreadsheet"></i> Excel</a>
                    <a href="/rh" class="btn btn-primary"><i class="bx bx-bar-chart-alt"></i> Dashboard RH Executivo</a>
                    @unless($arquivoMorto)
                    <a href="{{ route('funcionarios.create') }}" class="btn btn-success"><i class="bx bx-plus"></i> Novo funcionário</a>
                    @endunless
                </div>
            </div>

            <div class="d-flex gap-3 flex-wrap mb-3">
                <div class="func-kpi"><span class="text-muted">Total cadastrado</span><strong>{{ $resumoLista['total'] ?? 0 }}</strong></div>
                <div class="func-kpi"><span class="text-muted">Ativos</span><strong>{{ $resumoLista['ativos'] ?? 0 }}</strong></div>
                <div class="func-kpi"><span class="text-muted">Inativos arquivados</span><strong>{{ $resumoLista['inativos'] ?? 0 }}</strong></div>
            </div>

            {!! Form::open()->fill($filters)->get(['id' => 'funcionarios-filter-form']) !!}
            {!! Form::hidden('arquivo_morto', $arquivoMorto ? 1 : 0) !!}
            <div class="row g-3 align-items-end">
                <div class="col-lg-4 col-md-6">{!! Form::text('nome', 'Pesquisar por nome')->attrs(['id' => 'filtro-nome', 'autocomplete' => 'off']) !!}</div>
                <div class="col-lg-4 col-md-6">{!! Form::text('cpf', 'Pesquisar por CPF')->attrs(['id' => 'filtro-cpf', 'maxlength' => '14', 'autocomplete' => 'off'])->type('tel') !!}</div>
                <div class="col-lg-3 col-md-6">{!! Form::select('status', 'Status')->options(['' => 'Todos', '1' => 'Ativos', '0' => 'Inativos'])->attrs(['id' => 'filtro-status']) !!}</div>
                <div class="col-lg-1 col-md-6">
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary w-100" type="submit"><i class="bx bx-search"></i></button>
                        <a class="btn btn-danger" href="{{ route('funcionarios.index', $arquivoMorto ? ['arquivo_morto' => 1, 'status' => 0] : ['status' => 1]) }}"><i class="bx bx-eraser"></i></a>
                    </div>
                </div>
            </div>
            {!! Form::close() !!}
        </div>
    </div>

    <div class="card func-card mt-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped func-table mb-0" id="funcionarios-table">
                    <thead>
                        <tr>
                            <th data-sort="number">ID</th>
                            <th data-sort="text">CPF</th>
                            <th data-sort="text">Nome</th>
                            <th data-sort="date">Admissão</th>
                            <th data-sort="text">Função</th>
                            <th data-sort="money">Salário</th>
                            <th data-sort="text">Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody id="funcionarios-table-body">
                        @forelse($data as $item)
                        @php
                            $statusAtivo = !isset($item->ativo) || $item->ativo;
                            $dataAdmissao = $item->fichaAdmissao && $item->fichaAdmissao->data_admissao
                                ? __data_pt($item->fichaAdmissao->data_admissao, false)
                                : __data_pt($item->data_registro ?? $item->created_at, false);
                        @endphp
                        <tr data-status="{{ $statusAtivo ? '1' : '0' }}">
                            <td>{{ method_exists($data, 'firstItem') && $data->firstItem() ? $data->firstItem() + $loop->index : $loop->iteration }}</td>
                            <td class="func-cpf">{{ $item->cpf }}</td>
                            <td>{{ $item->nome }}</td>
                            <td>{{ $dataAdmissao }}</td>
                            <td>{{ $item->funcao ?? '-' }}</td>
                            <td>{{ function_exists('__moeda') ? __moeda($item->salario) : number_format((float)$item->salario, 2, ',', '.') }}</td>
                            <td>
                                @if($statusAtivo)
                                <span class="badge bg-success func-status">Ativo</span>
                                @else
                                <span class="badge bg-danger func-status">Inativo</span>
                                @endif
                            </td>
                            <td class="text-nowrap">
                                <div class="func-actions">
                                    <button type="button" class="btn btn-sm btn-light border func-trigger"><i class="bx bx-dots-vertical-rounded"></i></button>
                                    <div class="func-menu">
                                        <a href="{{ route('funcionarios.imprimir', $item->id) }}" target="_blank"><i class="bx bx-printer"></i> Imprimir ficha</a>
                                        <a href="{{ route('funcionarios.show', $item->id) }}"><i class="bx bx-detail"></i> Ver detalhes</a>
                                        <a href="{{ route('funcionarios.edit', $item->id) }}"><i class="bx bx-edit"></i> Editar cadastro</a>
                                        <a href="{{ route('rh.dossie.show', $item->id) }}"><i class="bx bx-folder-open"></i> Dossiê RH</a>
                                        <a href="{{ route('funcionarios.toggleStatus', $item->id) }}" onclick="return confirm('Deseja realmente {{ $statusAtivo ? 'arquivar' : 'reativar' }} este funcionário?')"><i class="bx {{ $statusAtivo ? 'bx-archive-in' : 'bx-user-check' }}"></i> {{ $statusAtivo ? 'Mover para arquivo morto' : 'Reativar funcionário' }}</a>
                                        <form action="{{ route('funcionarios.destroy', $item->id) }}" method="POST" id="funcionario-delete-{{ $item->id }}" class="m-0">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn-delete func-menu-button" onclick="event.stopPropagation();"><i class="bx bx-trash"></i> Excluir funcionário</button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="text-center py-4 text-muted">{{ $arquivoMorto ? 'Nenhum funcionário inativo arquivado.' : 'Nenhum funcionário ativo encontrado.' }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(method_exists($data, 'links'))
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 px-3 py-3 border-top">
                <small class="text-muted">
                    @if(method_exists($data, 'firstItem') && $data->total())
                        Exibindo {{ $data->firstItem() }} a {{ $data->lastItem() }} de {{ $data->total() }} registros
                    @else
                        Total de registros: {{ count($data) }}
                    @endif
                </small>
                {!! $data->appends($filters)->links() !!}
            </div>
            @endif
        </div>
    </div>
</div>

<script>
(function(){
    const cpfInput = document.getElementById('filtro-cpf');
    const nomeInput = document.getElementById('filtro-nome');
    const statusInput = document.getElementById('filtro-status');
    const table = document.getElementById('funcionarios-table');
    const tbody = document.getElementById('funcionarios-table-body');

    function maskCPF(value){
        value = (value || '').replace(/\D/g, '').slice(0, 11);
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
        return value;
    }

    if(cpfInput){
        cpfInput.addEventListener('input', function(){
            this.value = maskCPF(this.value);
            filterRows();
        });
        cpfInput.value = maskCPF(cpfInput.value);
    }
    if(nomeInput){ nomeInput.addEventListener('input', filterRows); }
    if(statusInput){ statusInput.addEventListener('change', filterRows); }

    function filterRows(){
        const nome = (nomeInput ? nomeInput.value : '').toLowerCase().trim();
        const cpf = (cpfInput ? cpfInput.value : '').replace(/\D/g, '');
        const status = statusInput ? statusInput.value : '';
        const rows = Array.from(tbody.querySelectorAll('tr')).filter(tr => tr.querySelectorAll('td').length);

        rows.forEach(row => {
            const tds = row.querySelectorAll('td');
            const rowNome = (tds[2]?.innerText || '').toLowerCase();
            const rowCpf = (tds[1]?.innerText || '').replace(/\D/g, '');
            const rowStatus = row.getAttribute('data-status') || '';
            const show = (!nome || rowNome.includes(nome)) && (!cpf || rowCpf.includes(cpf)) && (status === '' || rowStatus === status);
            row.style.display = show ? '' : 'none';
        });
    }

    document.querySelectorAll('.func-trigger').forEach(btn => {
        btn.addEventListener('click', function(e){
            e.preventDefault();
            e.stopPropagation();
            const box = this.closest('.func-actions');
            document.querySelectorAll('.func-actions').forEach(item => {
                if(item !== box) item.classList.remove('open');
            });
            box.classList.toggle('open');
        });
    });
    document.addEventListener('click', function(){ document.querySelectorAll('.func-actions').forEach(item => item.classList.remove('open')); });

    if(table){
        table.querySelectorAll('thead th[data-sort]').forEach((th, index) => {
            let asc = true;
            th.addEventListener('click', function(){
                const type = th.getAttribute('data-sort');
                const rows = Array.from(tbody.querySelectorAll('tr')).filter(r => r.querySelectorAll('td').length && r.style.display !== 'none');
                rows.sort((a, b) => {
                    const av = a.children[index].innerText.trim();
                    const bv = b.children[index].innerText.trim();
                    const normalizeDate = v => { const p = v.split('/'); return p.length === 3 ? new Date(p[2], p[1]-1, p[0]).getTime() : 0; };
                    const normalizeMoney = v => parseFloat(v.replace(/\./g,'').replace(',', '.').replace(/[^0-9.-]/g,'')) || 0;
                    const normalizeNumber = v => parseFloat(v.replace(/[^0-9.-]/g,'')) || 0;
                    let result = 0;
                    if(type === 'date') result = normalizeDate(av) - normalizeDate(bv);
                    else if(type === 'money') result = normalizeMoney(av) - normalizeMoney(bv);
                    else if(type === 'number') result = normalizeNumber(av) - normalizeNumber(bv);
                    else result = av.localeCompare(bv, 'pt-BR', {numeric:true, sensitivity:'base'});
                    return asc ? result : -result;
                });
                rows.forEach(r => tbody.appendChild(r));
                asc = !asc;
            });
        });
    }

    filterRows();
})();
</script>
@endsection
