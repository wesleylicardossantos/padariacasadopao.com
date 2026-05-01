@extends('default.layout',['title' => 'Dashboard ERP'])

@section('content')

<div class="page-content">
<div class="card">
<div class="card-body">

<h4>Dashboard do Sistema</h4>

<div class="row">

<div class="col-md-3">
<div class="card bg-primary text-white">
<div class="card-body">
<h5>Vendas Hoje</h5>
<h3>{{ $vendasHoje }}</h3>
</div>
</div>
</div>

<div class="col-md-3">
<div class="card bg-success text-white">
<div class="card-body">
<h5>Faturamento Mês</h5>
<h3>R$ {{ number_format($faturamentoMes,2,',','.') }}</h3>
</div>
</div>
</div>

<div class="col-md-3">
<div class="card bg-warning text-white">
<div class="card-body">
<h5>Contas Pendentes</h5>
<h3>{{ $contasPendentes }}</h3>
</div>
</div>
</div>

<div class="col-md-3">
<div class="card bg-danger text-white">
<div class="card-body">
<h5>Caixas Abertos</h5>
<h3>{{ $caixasAbertos }}</h3>
</div>
</div>
</div>

</div>

<hr>

<a href="/__admin" class="btn btn-dark">Painel Manutenção</a>

</div>
</div>
</div>

@endsection
