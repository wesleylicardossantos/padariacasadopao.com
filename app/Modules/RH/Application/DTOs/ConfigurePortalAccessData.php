<?php

namespace App\Modules\RH\Application\DTOs;

use App\Models\Funcionario;

final class ConfigurePortalAccessData
{
    public function __construct(
        public Funcionario $funcionario,
        public bool $ativo,
        public ?int $perfilId = null,
        public bool $podeVerRelatorioProdutos = false,
        public bool $podeVerRelatorioProdutosExtra = false,
    ) {
    }

    public function permissoesExtras(): array
    {
        return $this->podeVerRelatorioProdutosExtra ? ['produtos.visualizar'] : [];
    }
}
