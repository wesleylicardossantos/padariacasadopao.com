<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class RHPortalFuncionario extends Model
{
    protected $table = 'rh_portal_funcionarios';

    protected $fillable = [
        'empresa_id',
        'funcionario_id',
        'perfil_id',
        'permissoes_extras',
        'ativo',
        'pode_ver_relatorio_produtos',
        'senha',
        'token_primeiro_acesso',
        'token_recuperacao',
        'token_expira_em',
        'ultimo_login_em',
        'ultimo_login_ip',
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'permissoes_extras' => 'array',
        'pode_ver_relatorio_produtos' => 'boolean',
        'token_expira_em' => 'datetime',
        'ultimo_login_em' => 'datetime',
    ];


    public function perfil()
    {
        return $this->belongsTo(RHPortalPerfil::class, 'perfil_id');
    }

    public function permissoesEfetivas(): array
    {
        $permissoes = [];

        if ($this->relationLoaded('perfil') || $this->perfil) {
            $permissoes = array_merge($permissoes, (array) ($this->perfil->permissoes ?? []));
        }

        $permissoes = array_merge($permissoes, (array) ($this->permissoes_extras ?? []));

        if ($this->pode_ver_relatorio_produtos) {
            $permissoes[] = 'produtos.visualizar';
        }

        if (!in_array('dashboard.visualizar', $permissoes, true)) {
            $permissoes[] = 'dashboard.visualizar';
        }

        if (!in_array('holerites.visualizar', $permissoes, true)) {
            $permissoes[] = 'holerites.visualizar';
        }

        return array_values(array_unique(array_filter($permissoes)));
    }

    public function hasPermission(string $permissao): bool
    {
        return in_array($permissao, $this->permissoesEfetivas(), true);
    }

    public function funcionario()
    {
        return $this->belongsTo(Funcionario::class, 'funcionario_id');
    }
}

