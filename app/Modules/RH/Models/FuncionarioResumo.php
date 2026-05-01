<?php

namespace App\Modules\RH\Models;

class FuncionarioResumo
{
    public $nome;
    public $salario;
    public $eventos;

    public function __construct(string $nome, float $salario, int $eventos = 0)
    {
        $this->nome = $nome;
        $this->salario = $salario;
        $this->eventos = $eventos;
    }
}
