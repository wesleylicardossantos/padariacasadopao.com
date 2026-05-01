@extends('default.layout', ['title' => 'RH Modular - Folha'])
@section('content')
<div class="container-fluid">
    <div class="card mt-3 border-0 shadow-sm" style="border-radius: 18px;">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                <div>
                    <h4 class="mb-1">Resumo da folha modular</h4>
                    <p class="text-muted mb-0">Base criada para a próxima refatoração do cálculo da folha e descontos.</p>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="/rh/folha" class="btn btn-outline-secondary">Abrir folha atual</a>
                    <a href="/rh/modular" class="btn btn-primary">Voltar ao dashboard modular</a>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6"><div class="card h-100 border-0 bg-light"><div class="card-body"><small class="text-muted d-block">Funcionários listados</small><strong>{{ $totalFuncionarios }}</strong></div></div></div>
                <div class="col-md-6"><div class="card h-100 border-0 bg-light"><div class="card-body"><small class="text-muted d-block">Total da folha</small><strong>R$ {{ number_format((float) $totalFolha, 2, ',', '.') }}</strong></div></div></div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>Funcionário</th>
                            <th>Salário base</th>
                            <th>Eventos vinculados</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($funcionarios as $funcionario)
                            <tr>
                                <td>{{ $funcionario->nome }}</td>
                                <td>R$ {{ number_format((float) $funcionario->salario, 2, ',', '.') }}</td>
                                <td>{{ $funcionario->eventos->count() }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">Nenhum funcionário encontrado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
