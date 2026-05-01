<div class="row g-3 mb-3">
    <div class="col-md-6"><strong>Empresa:</strong> {{ $empresa->razao_social ?? $empresa->nome ?? '--' }}</div>
    <div class="col-md-3"><strong>CNPJ:</strong> {{ $empresa->cpf_cnpj ?? $empresa->cnpj ?? '--' }}</div>
    <div class="col-md-3"><strong>Documento:</strong> {{ $tituloSecundario ?? 'RESCISÃO' }}</div>
    <div class="col-md-6"><strong>Funcionário:</strong> {{ $funcionario->nome ?? '--' }}</div>
    <div class="col-md-3"><strong>CPF:</strong> {{ $funcionario->cpf ?? '--' }}</div>
    <div class="col-md-3"><strong>Data rescisão:</strong> {{ optional($rescisao->data_rescisao)->format('d/m/Y') }}</div>
</div>
<div class="table-responsive mb-3">
    <table class="table table-bordered">
        <thead><tr><th>Rubrica</th><th>Tipo</th><th class="text-end">Valor</th></tr></thead>
        <tbody>
            @foreach($rescisao->itens as $item)
                <tr>
                    <td>{{ $item->descricao }}</td>
                    <td>{{ ucfirst($item->tipo) }}</td>
                    <td class="text-end">{{ __moeda($item->valor) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr><th colspan="2">Total bruto</th><th class="text-end">{{ __moeda($rescisao->total_bruto) }}</th></tr>
            <tr><th colspan="2">Total descontos</th><th class="text-end">{{ __moeda($rescisao->total_descontos) }}</th></tr>
            <tr><th colspan="2">Total líquido</th><th class="text-end">{{ __moeda($rescisao->total_liquido) }}</th></tr>
        </tfoot>
    </table>
</div>
<div><strong>Motivo:</strong> {{ $rescisao->motivo }}</div>
<div><strong>Tipo de aviso:</strong> {{ ucfirst($rescisao->tipo_aviso ?: '--') }}</div>
