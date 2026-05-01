@extends('layouts.relatorio')

@section('titulo','Relatório de Funcionários')
@section('empresa_documento_label', 'CNPJ')

@section('conteudo')
@php
    use App\Models\FuncionarioFichaAdmissao;
@endphp

<table class="meta">
    <tr>
        <td>
            <div class="meta-box">
                <div class="meta-label">Total de funcionários</div>
                <div class="meta-value">{{ count($data) }}</div>
            </div>
        </td>
        <td>
            <div class="meta-box">
                <div class="meta-label">Status exibidos</div>
                <div class="meta-value">Todos</div>
            </div>
        </td>
        <td>
            <div class="meta-box">
                <div class="meta-label">Relatório</div>
                <div class="meta-value">Funcionários</div>
            </div>
        </td>
    </tr>
</table>

<table class="report">
    <thead>
        <tr>
            <th style="width:5%">ID</th>
            <th style="width:15%">CPF</th>
            <th style="width:31%">Nome</th>
            <th style="width:14%">Admissão</th>
            <th style="width:16%">Função</th>
            <th style="width:11%">Salário</th>
            <th style="width:8%">Status</th>
        </tr>
    </thead>

    <tbody>
        @foreach($data as $item)
        @php
            $ficha = FuncionarioFichaAdmissao::where('funcionario_id', $item->id)->first();
            $dataAdmissao = $ficha && !empty($ficha->data_admissao)
                ? \Carbon\Carbon::parse($ficha->data_admissao)->format('d/m/Y')
                : (
                    !empty($item->data_registro)
                        ? \Carbon\Carbon::parse($item->data_registro)->format('d/m/Y')
                        : (!empty($item->created_at) ? \Carbon\Carbon::parse($item->created_at)->format('d/m/Y') : '-')
                );
        @endphp
        <tr>
            <td class="text-center">{{ $loop->iteration }}</td>
            <td style="white-space: nowrap;">{{ $item->cpf }}</td>
            <td>{{ $item->nome }}</td>
            <td>{{ $dataAdmissao }}</td>
            <td>{{ $item->funcao ?? '-' }}</td>
            <td class="text-right">{{ number_format((float)$item->salario,2,',','.') }}</td>
            <td class="text-center">{{ (int)($item->ativo ?? 1) === 1 ? 'Ativo' : 'Inativo' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

@endsection
