
@extends('layouts.relatorio')

@section('titulo','Relatório de Clientes')

@section('conteudo')
<table class="report">
<thead>
<tr>
<th>ID</th>
<th>Nome</th>
<th>CPF/CNPJ</th>
<th>Telefone</th>
<th>Email</th>
</tr>
</thead>
<tbody>
@foreach($data as $item)
<tr>
<td class="text-center">{{ $loop->iteration }}</td>
<td>{{ $item->nome }}</td>
<td>{{ $item->cpf_cnpj }}</td>
<td>{{ $item->telefone }}</td>
<td>{{ $item->email }}</td>
</tr>
@endforeach
</tbody>
</table>
@endsection
