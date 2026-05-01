@extends('default.layout', ['title' => 'Estoque Baixo'])

@section('content')
<div class="page-content"><div class="card"><div class="card-body"><div class="d-flex justify-content-between align-items-center mb-3"><h4 class="mb-0">Produtos com Estoque Baixo</h4><div><a href="/__admin" class="btn btn-dark">Dashboard</a></div></div>@if(count($items) > 0)<table class="table table-bordered"><thead><tr><th>ID</th><th>Produto</th><th>Estoque</th></tr></thead><tbody>@foreach($items as $p)<tr><td>{{ $p->id }}</td><td>{{ $p->nome ?? '-' }}</td><td style="color:red">{{ $p->estoque }}</td></tr>@endforeach</tbody></table>@else<div class="alert alert-success mb-0">Nenhum produto com estoque baixo.</div>@endif</div></div></div>
@endsection
