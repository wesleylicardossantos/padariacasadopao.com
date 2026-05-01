
@extends('layouts.relatorio')

@section('titulo','Relatório de Fluxo de Caixa')

@section('conteudo')
<table class="report">
<thead>
<tr>
<th>ID</th>
<th>Descrição</th>
<th>Tipo</th>
<th>Data</th>
<th>Valor</th>
</tr>
</thead>
<tbody>
@foreach($data as $item)
<tr>
<td class="text-center">{{ $loop->iteration }}</td>
<td>{{ $item->descricao }}</td>
<td>{{ $item->tipo }}</td>
<td>{{ __data_pt($item->created_at) }}</td>
<td class="text-right">{{ number_format($item->valor,2,',','.') }}</td>
</tr>
@endforeach
</tbody>
</table>
@endsection
