<?php

namespace App\Http\Middleware;

use App\Models\Funcionario;
use App\Models\RHPortalFuncionario;
use Closure;

class VerificaPortalFuncionario
{
    public function handle($request, Closure $next)
    {
        $portal = session('funcionario_portal');
        if (!$portal || empty($portal['funcionario_id'])) {
            return redirect('/portal')->with('flash_erro', 'Faça login para acessar o portal do funcionário.');
        }

        $funcionario = Funcionario::query()
            ->comInativos()
            ->where('id', (int) $portal['funcionario_id'])
            ->where('empresa_id', (int) $portal['empresa_id'])
            ->first();

        if (!$funcionario) {
            session()->forget('funcionario_portal');
            return redirect('/portal')->with('flash_erro', 'Acesso do funcionário não encontrado.');
        }

        $acesso = RHPortalFuncionario::query()
            ->where('empresa_id', (int) $portal['empresa_id'])
            ->where('funcionario_id', (int) $portal['funcionario_id'])
            ->first();

        if (!$acesso || !($acesso->ativo ?? false)) {
            session()->forget('funcionario_portal');
            return redirect('/portal')->with('flash_erro', 'Acesso do portal do funcionário está desativado.');
        }

        if (method_exists($funcionario, 'isInactive') && $funcionario->isInactive()) {
            session()->forget('funcionario_portal');
            return redirect('/portal')->with('flash_erro', 'Funcionário inativo ou arquivado. Solicite reativação ao RH.');
        }

        $request->attributes->set('portal_funcionario', $funcionario);
        $request->attributes->set('portal_acesso', $acesso);
        return $next($request);
    }
}
