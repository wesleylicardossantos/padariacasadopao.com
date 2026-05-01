@extends('rh.portal_acesso._layout')
@section('title', 'Definir senha')
@section('body')
<div class="alert alert-light border">Funcionário: <strong>{{ $funcionario->nome ?? 'Funcionário' }}</strong></div>
<form method="POST" action="{{ $tipo === 'primeiro_acesso' ? route('rh.portal_externo.primeiro_acesso.salvar', $token) : route('rh.portal_externo.redefinir_senha.salvar', $token) }}">
    @csrf
    <div class="mb-3">
        <label class="form-label">Nova senha</label>
        <input type="password" name="senha" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Confirmar senha</label>
        <input type="password" name="senha_confirmation" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-primary w-100">Salvar senha</button>
</form>
@endsection
