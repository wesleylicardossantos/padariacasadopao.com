@extends('default.layout')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="mb-1">RBAC Profissional do RH</h3>
            <small class="text-muted">Empresa {{ $empresaId ?: 'global' }}</small>
        </div>
        <form method="POST" action="{{ route('rh.acl.sync') }}">
            @csrf
            <button class="btn btn-primary">Sincronizar papéis padrão</button>
        </form>
    </div>

    <div class="row g-3">
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header">Papéis e permissões</div>
                <div class="card-body">
                    @foreach($papeis as $papel)
                        <div class="border rounded p-2 mb-2">
                            <div class="fw-bold">{{ $papel->nome }} @if($papel->is_admin)<span class="badge bg-dark">admin</span>@endif</div>
                            <div class="small text-muted mb-2">{{ $papel->descricao }}</div>
                            <div class="d-flex flex-wrap gap-1">
                                @foreach($papel->permissoes as $perm)
                                    <span class="badge bg-info text-dark">{{ $perm->codigo }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header">Atribuir papel ao usuário</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('rh.acl.assign') }}" class="row g-2 mb-4">
                        @csrf
                        <div class="col-md-5">
                            <label class="form-label">Usuário</label>
                            <select name="usuario_id" class="form-select" required>
                                @foreach($usuarios as $usuario)
                                    <option value="{{ $usuario->id }}">{{ $usuario->nome }} (#{{ $usuario->id }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Papel</label>
                            <select name="papel_id" class="form-select" required>
                                @foreach($papeis as $papel)
                                    <option value="{{ $papel->id }}">{{ $papel->nome }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button class="btn btn-success w-100">Atribuir</button>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-sm table-striped">
                            <thead><tr><th>Usuário</th><th>Papéis ativos</th></tr></thead>
                            <tbody>
                            @foreach($usuarios as $usuario)
                                <tr>
                                    <td>{{ $usuario->nome }}</td>
                                    <td>
                                        @forelse(($assignments[$usuario->id] ?? collect()) as $assignment)
                                            <span class="badge bg-secondary">papel #{{ $assignment->papel_id }}</span>
                                        @empty
                                            <span class="text-muted">Sem papel atribuído</span>
                                        @endforelse
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
