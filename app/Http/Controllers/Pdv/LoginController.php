<?php

namespace App\Http\Controllers\Pdv;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\Usuario;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $keyENV = env('KEY_APP');
        $login = $request->login;
        $senha = $request->senha;
        $empresaRef = $request->empresa;

        $usuario = Usuario::query()
            ->when($empresaRef, function ($query) use ($empresaRef) {
                $query->whereIn('empresa_id', function ($sub) use ($empresaRef) {
                    $sub->select('id')
                        ->from('empresas')
                        ->where('id', $empresaRef)
                        ->orWhere('hash', $empresaRef)
                        ->orWhere('nome_fantasia', $empresaRef)
                        ->orWhere('razao_social', $empresaRef);
                });
            })
            ->where('login', $login)
            ->where('senha', md5($senha))
            ->first();

        if ($usuario == null) {
            return response()->json([
                'message' => 'Credenciais inválidas.',
            ], 401);
        }

        $empresa = Empresa::find($usuario->empresa_id);
        $token = base64_encode($usuario->id . ';' . $usuario->login . ';' . $keyENV);

        $credenciais = [
            'nome' => $usuario->nome,
            'token' => $token,
            'terminal_token' => $token,
            'id' => $usuario->id,
            'img' => $usuario->img,
            'empresa_id' => $usuario->empresa_id,
            'empresa' => [
                'id' => $empresa?->id,
                'nome' => $empresa?->nome_fantasia ?: $empresa?->razao_social,
                'razao_social' => $empresa?->razao_social,
                'hash' => $empresa?->hash,
            ],
            'operador' => [
                'id' => $usuario->id,
                'nome' => $usuario->nome,
                'login' => $usuario->login,
            ],
        ];

        return response()->json($credenciais, 200);
    }
}
