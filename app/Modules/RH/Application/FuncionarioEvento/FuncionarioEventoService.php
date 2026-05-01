<?php

namespace App\Modules\RH\Application\FuncionarioEvento;

use App\Models\FuncionarioEvento;
use Illuminate\Support\Facades\DB;

class FuncionarioEventoService
{
    public function replaceForFuncionario(int $funcionarioId, array $payload, int $empresaId): void
    {
        DB::transaction(function () use ($funcionarioId, $payload, $empresaId) {
            FuncionarioEvento::where('funcionario_id', $funcionarioId)->delete();

            foreach ($this->linhas($payload) as $linha) {
                FuncionarioEvento::create([
                    'empresa_id' => $empresaId,
                    'evento_id' => $linha['evento_id'],
                    'funcionario_id' => $funcionarioId,
                    'condicao' => $linha['condicao'],
                    'metodo' => $linha['metodo'],
                    'valor' => __convert_value_bd($linha['valor']),
                    'ativo' => $linha['ativo'],
                ]);
            }
        });
    }

    private function linhas(array $payload): array
    {
        $eventos = $payload['evento'] ?? [];
        $condicoes = $payload['condicao'] ?? [];
        $metodos = $payload['metodo'] ?? [];
        $valores = $payload['valor'] ?? [];
        $ativos = $payload['ativo'] ?? [];

        $linhas = [];
        foreach ($eventos as $i => $eventoId) {
            if (empty($eventoId)) {
                continue;
            }

            $linhas[] = [
                'evento_id' => $eventoId,
                'condicao' => $condicoes[$i] ?? null,
                'metodo' => $metodos[$i] ?? null,
                'valor' => $valores[$i] ?? 0,
                'ativo' => $ativos[$i] ?? 1,
            ];
        }

        return $linhas;
    }
}
