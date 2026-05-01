
@extends('layouts.relatorio')

@section('titulo','Relatório de Estoque')

@section('conteudo')
<table class="report">
<thead>
<tr>
<th>ID</th>
<th>Produto</th>
<th>Categoria</th>
<th>Estoque</th>
<th>Preço</th>
</tr>
</thead>
<tbody>
@foreach($data as $item)
<tr>
<td class="text-center">{{ $loop->iteration }}</td>
<td>{{ $item->nome }}</td>
<td>{{ $item->categoria ?? '-' }}</td>
<td class="text-center">{{ $item->estoque }}</td>
<td class="text-right">{{ number_format($item->valor_venda,2,',','.') }}</td>
</tr>
@endforeach
</tbody>
</table>
@endsection
