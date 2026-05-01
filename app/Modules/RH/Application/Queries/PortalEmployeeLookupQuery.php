<?php

namespace App\Modules\RH\Application\Queries;

use App\Models\Funcionario;

final class PortalEmployeeLookupQuery
{
    public function findByLogin(string $login): ?Funcionario
    {
        $login = trim($login);
        $email = function_exists('mb_strtolower') ? mb_strtolower($login) : strtolower($login);
        $cpf = preg_replace('/\D+/', '', $login);

        return Funcionario::query()
            ->where(function ($query) use ($email, $cpf) {
                if ($email !== '') {
                    $query->orWhereRaw('LOWER(email) = ?', [$email]);
                }

                if ($cpf !== '') {
                    $query->orWhereRaw("REPLACE(REPLACE(REPLACE(cpf, '.', ''), '-', ''), '/', '') = ?", [$cpf]);
                }
            })
            ->orderByDesc('id')
            ->first();
    }
}
