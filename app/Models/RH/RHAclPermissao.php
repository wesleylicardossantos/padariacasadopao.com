<?php

namespace App\Models\RH;

use Illuminate\Database\Eloquent\Model;

class RHAclPermissao extends Model
{
    protected $table = 'rh_acl_permissoes';

    protected $fillable = [
        'codigo',
        'nome',
        'descricao',
        'modulo',
        'ativo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];
}
