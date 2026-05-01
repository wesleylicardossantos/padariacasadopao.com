@php
    $item = $item ?? null;
    $linhas = $linhas ?? ['manha' => [], 'tarde' => []];
    $periodos = ['manha' => 'MANHÃ', 'tarde' => 'TARDE'];
@endphp

<style>
    .salgado-sheet{background:#fff;border:1px solid #d9dee8;border-radius:18px;box-shadow:0 10px 30px rgba(15,23,42,.06);overflow:hidden}
    .salgado-sheet .header-grid{display:grid;grid-template-columns:1fr 1fr;border-bottom:1px solid #111827}
    .salgado-sheet .header-grid .field{padding:14px 16px;border-right:1px solid #111827;font-weight:700}
    .salgado-sheet .header-grid .field:last-child{border-right:none}
    .salgado-sheet .section-title{padding:10px 12px;text-align:center;font-size:1.2rem;font-weight:800;background:#efefef;border-bottom:1px solid #111827;letter-spacing:.04em}
    .salgado-sheet table{width:100%;border-collapse:collapse}
    .salgado-sheet th,.salgado-sheet td{border-right:1px solid #111827;border-bottom:1px solid #111827;padding:0}
    .salgado-sheet th:last-child,.salgado-sheet td:last-child{border-right:none}
    .salgado-sheet th{background:#f5f5f5;text-align:center;font-size:.95rem;padding:10px 8px}
    .salgado-sheet input,.salgado-sheet textarea{width:100%;border:none;outline:none;padding:11px 10px;background:transparent}
    .salgado-sheet tbody tr:hover{background:#fafcff}
    .salgado-sheet .col-qtd{width:90px}
    .salgado-sheet .col-desc{width:auto}
    .salgado-sheet .col-termino{width:150px}
    .salgado-sheet .col-saldo{width:120px}
    .salgado-sheet .gap-line{height:24px;border-bottom:1px solid #111827;background:#fff}
    .salgado-actions{display:flex;justify-content:end;gap:.75rem;flex-wrap:wrap}
    .salgado-meta{font-size:.9rem;color:#64748b}
</style>

<div class="row g-3 mb-3">
    <div class="col-lg-3 col-md-6">
        <label class="form-label">Data</label>
        <input type="date" class="form-control" name="data" value="{{ old('data', optional($item?->data)->format('Y-m-d') ?? $item?->data) }}" required>
    </div>
    <div class="col-lg-3 col-md-6">
        <label class="form-label">Dia</label>
        <input type="text" class="form-control" name="dia" value="{{ old('dia', $item?->dia) }}" placeholder="Ex: Segunda-feira">
    </div>
    <div class="col-lg-6">
        <label class="form-label">Observações</label>
        <input type="text" class="form-control" name="observacoes" value="{{ old('observacoes', $item?->observacoes) }}" placeholder="Observações operacionais do turno ou produção do dia">
    </div>
</div>

<div class="alert alert-light border d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div>
        <strong>Layout operacional</strong>
        <div class="salgado-meta">Versão SaaS pronta para cadastro, listagem, edição e PDF no formato da folha operacional.</div>
    </div>
    <div class="salgado-meta">Empresa atual: {{ data_get(session('user_logged'), 'nome_empresa', 'Sessão atual') }}</div>
</div>

<div class="salgado-sheet mb-3">
    <div class="header-grid">
        <div class="field">DATA: <span class="fw-normal">{{ old('data', optional($item?->data)->format('d/m/Y') ?? null) }}</span></div>
        <div class="field">DIA: <span class="fw-normal">{{ old('dia', $item?->dia) }}</span></div>
    </div>

    @foreach($periodos as $periodoKey => $periodoLabel)
        <div class="section-title">{{ $periodoLabel }}</div>
        <table>
            <thead>
                <tr>
                    <th class="col-qtd">QTD</th>
                    <th class="col-desc">DESCRIÇÃO</th>
                    <th class="col-termino">TERMINO</th>
                    <th class="col-saldo">SALDO</th>
                </tr>
            </thead>
            <tbody>
                @foreach(($linhas[$periodoKey] ?? []) as $idx => $row)
                    <tr>
                        <td>
                            <input type="number" min="0" name="{{ $periodoKey }}[{{ $idx }}][qtd]" value="{{ old($periodoKey.'.'.$idx.'.qtd', $row['qtd'] ?? '') }}">
                        </td>
                        <td>
                            <input type="text" name="{{ $periodoKey }}[{{ $idx }}][descricao]" value="{{ old($periodoKey.'.'.$idx.'.descricao', $row['descricao'] ?? '') }}">
                        </td>
                        <td>
                            <input type="text" name="{{ $periodoKey }}[{{ $idx }}][termino]" value="{{ old($periodoKey.'.'.$idx.'.termino', $row['termino'] ?? '') }}" placeholder="Ex: 08:30">
                        </td>
                        <td>
                            <input type="number" min="0" name="{{ $periodoKey }}[{{ $idx }}][saldo]" value="{{ old($periodoKey.'.'.$idx.'.saldo', $row['saldo'] ?? '') }}">
                        </td>
                    </tr>
                @endforeach
                @for($extra = 0; $extra < 3; $extra++)
                    @php $rowIndex = count($linhas[$periodoKey] ?? []) + $extra; @endphp
                    <tr>
                        <td><input type="number" min="0" name="{{ $periodoKey }}[{{ $rowIndex }}][qtd]" value="{{ old($periodoKey.'.'.$rowIndex.'.qtd') }}"></td>
                        <td><input type="text" name="{{ $periodoKey }}[{{ $rowIndex }}][descricao]" value="{{ old($periodoKey.'.'.$rowIndex.'.descricao') }}"></td>
                        <td><input type="text" name="{{ $periodoKey }}[{{ $rowIndex }}][termino]" value="{{ old($periodoKey.'.'.$rowIndex.'.termino') }}"></td>
                        <td><input type="number" min="0" name="{{ $periodoKey }}[{{ $rowIndex }}][saldo]" value="{{ old($periodoKey.'.'.$rowIndex.'.saldo') }}"></td>
                    </tr>
                @endfor
            </tbody>
        </table>
        @if(!$loop->last)
            <div class="gap-line"></div>
        @endif
    @endforeach
</div>

<div class="salgado-actions">
    <a href="{{ route('controle.salgados.index') }}" class="btn btn-light">Voltar</a>
    <button type="submit" class="btn btn-primary">Salvar lançamento</button>
</div>
