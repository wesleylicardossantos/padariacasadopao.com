@extends('default.layout', ['title' => $title])

@section('css')
<style>
    .rvr-shell { padding: 22px; }
    .rv-card { border: 1px solid #d9e2ef; border-radius: 10px; background: #fff; box-shadow: 0 2px 10px rgba(15, 23, 42, 0.05); }
    .rv-toolbar { display: flex; justify-content: space-between; align-items: end; gap: 12px; margin-bottom: 18px; flex-wrap: wrap; }
    .rv-toolbar .form-label { font-weight: 600; color: #334155; }
    .rv-toolbar .btn { height: 38px; }
    .rv-table-wrap { overflow-x: auto; }
    .rv-table { width: 100%; border-collapse: collapse; min-width: 950px; }
    .rv-table td, .rv-table th { border: 1px solid #cfd8e3; padding: 8px 10px; }
    .rv-title-row th { background: #ffffff; color: #111827; font-size: 19px; font-weight: 800; text-align: center; border-bottom: 0; }
    .rv-head th { background: #2f5597; color: #fff; font-size: 13px; font-weight: 700; text-transform: uppercase; text-align: center; }
    .rv-head th.total-box-title { background: #3f6fb9; }
    .rv-head th.total-box-value { background: #8bc34a; color: #fff; font-size: 14px; }
    .rv-head th.total-box-value strong { font-size: 28px; line-height: 1; margin-right: 8px; }
    .rv-body td { background: #eef5e2; font-size: 24px; color: #163c08; }
    .rv-body td.col-data, .rv-body td.col-dia { background: #fff; font-size: 14px; color: #1f2937; }
    .rv-body td.col-data { text-align: center; width: 90px; }
    .rv-body td.col-dia { text-align: center; width: 80px; }
    .rv-body td.money { white-space: nowrap; font-variant-numeric: tabular-nums; }
    .rv-body td.money span { display: inline-block; min-width: 26px; color: #1f5d11; }
    .rv-empty { text-align: center; padding: 32px; color: #64748b; }
</style>
@endsection

@section('content')
<div class="page-content">
    <div class="rvr-shell">
        <div class="rv-toolbar">
            <div>
                <h4 class="mb-1">Relatório de Vendas - Resumido</h4>
                <div class="text-muted">Página em mesma janela com dados do banco consolidados por dia.</div>
            </div>
            <form method="GET" action="{{ route('controle.relatorio-vendas-resumido') }}" class="d-flex align-items-end gap-2">
                <div>
                    <label class="form-label mb-1">Competência</label>
                    <input type="month" name="mes" class="form-control" value="{{ $mesSelecionado }}">
                </div>
                <button class="btn btn-primary"><i class="bx bx-search"></i> Atualizar</button>
            </form>
        </div>

        <div class="rv-card">
            <div class="rv-table-wrap">
                <table class="rv-table">
                    <thead>
                        <tr class="rv-title-row">
                            <th colspan="7">VENDAS - {{ $tituloCompetencia }}</th>
                            <th class="total-box-title">VENDAS</th>
                        </tr>
                        <tr class="rv-head">
                            <th>DATA</th>
                            <th>DIA</th>
                            <th>V. MANHA</th>
                            <th>V. TARDE</th>
                            <th>V. CARTAO</th>
                            <th>SAIDAS CX</th>
                            <th>VENDA DIA</th>
                            <th class="total-box-value"><strong>R$</strong> {{ number_format((float) $totalVendas, 2, ',', '.') }}</th>
                        </tr>
                    </thead>
                    <tbody class="rv-body">
                        @forelse($linhas as $linha)
                        <tr>
                            <td class="col-data">{{ $linha['data'] }}</td>
                            <td class="col-dia">{{ $linha['dia'] }}</td>
                            <td class="money"><span>R$</span> {{ number_format((float) $linha['v_manha'], 2, ',', '.') }}</td>
                            <td class="money"><span>R$</span> {{ number_format((float) $linha['v_tarde'], 2, ',', '.') }}</td>
                            <td class="money"><span>R$</span> {{ number_format((float) $linha['v_cartao'], 2, ',', '.') }}</td>
                            <td class="money"><span>R$</span> {{ number_format((float) $linha['saidas_cx'], 2, ',', '.') }}</td>
                            <td class="money"><span>R$</span> {{ number_format((float) $linha['venda_dia'], 2, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="rv-empty">Nenhum registro encontrado para a competência informada.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
