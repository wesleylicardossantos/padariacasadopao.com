<?php

namespace App\Http\Middleware;

use App\Models\Usuario;
use Closure;

class AuthPdv
{
    public function handle($request, Closure $next)
    {
        $token = $request->header('token', $request->header('authorization-token'));
        $spl = explode(';', base64_decode((string) $token));

        if (!isset($spl[1])) {
            return response()->json(['message' => 'Token PDV inválido.'], 401);
        }

        $user = Usuario::where('id', $spl[0])
            ->where('login', $spl[1])
            ->first();

        if ($user != null) {
            $request->merge([
                'empresa_id' => $user->empresa_id,
                'pdv_usuario_id' => $user->id,
            ]);

            return $next($request);
        }

        return response()->json(['message' => 'Token PDV não autorizado.'], 401);
    }
}
