@extends('rh.portal_acesso._layout')
@section('title', 'Recuperar senha')
@section('body')
<form method="POST" action="{{ route('rh.portal_externo.esqueci_senha.enviar') }}">
    @csrf
    <div class="mb-3">
        <label class="form-label">CPF ou e-mail</label>
        <input type="text" name="login" class="form-control" value="{{ old('login') }}" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Receber por</label>
        <select class="form-select" name="canal">
            <option value="email">E-mail</option>
            <option value="whatsapp">WhatsApp</option>
        </select>
    </div>
    <button type="submit" class="btn btn-primary w-100">Enviar link</button>
</form>
<div class="mt-3 text-center">
    <a href="{{ route('rh.portal_externo.login') }}">Voltar ao login</a>
</div>
@endsection
