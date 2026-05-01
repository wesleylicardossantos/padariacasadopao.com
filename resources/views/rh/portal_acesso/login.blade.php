@extends('rh.portal_acesso._layout')
@section('title', 'Login do portal')
@section('body')
<form method="POST" action="{{ route('rh.portal_externo.login.post') }}">
    @csrf
    <div class="mb-3">
        <label class="form-label">CPF ou e-mail</label>
        <input type="text" name="login" class="form-control" value="{{ old('login') }}" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Senha</label>
        <input type="password" name="senha" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-primary w-100">Entrar</button>
</form>
<div class="d-flex justify-content-between mt-3 flex-wrap gap-2">
    <a href="{{ route('rh.portal_externo.esqueci_senha') }}">Esqueci minha senha</a>
    <span class="text-muted">Primeiro acesso via link</span>
</div>
@endsection
