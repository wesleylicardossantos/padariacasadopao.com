@extends('default.layout',['title'=>'IA Histórico'])
@section('content')
<div class="page-content">
    <h5>Tendência: {{ $tendencia }}</h5>
    <table class="table table-sm">
        <thead>
            <tr>
                <th>Mês</th>
                <th>Receita</th>
                <th>Despesas</th>
                <th>Resultado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($dados as $d)
            <tr>
                <td>{{ $d['mes'] }}/{{ $d['ano'] }}</td>
                <td>{{ number_format($d['receita'],2,',','.') }}</td>
                <td>{{ number_format($d['despesas'],2,',','.') }}</td>
                <td>{{ number_format($d['resultado'],2,',','.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
