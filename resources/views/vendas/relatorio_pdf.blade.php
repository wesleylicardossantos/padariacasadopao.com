
@extends('layouts.relatorio')

@section('titulo','Relatório de Vendas')

@section('conteudo')
<table class="report">
<thead>
<tr>
<th>ID</th>
<th>Cliente</th>
<th>Data</th>
<th>Total</th>
<th>Status</th>
</tr>
</thead>
<tbody>
@foreach($data as $item)
<tr>
<td class="text-center">{{ $loop->iteration }}</td>
<td>{{ $item->cliente ?? '-' }}</td>
<td>{{ __data_pt($item->created_at) }}</td>
<td class="text-right">{{ number_format($item->valor_total,2,',','.') }}</td>
<td>{{ $item->status ?? '-' }}</td>
</tr>
@endforeach
</tbody>
</table>
@endsection
