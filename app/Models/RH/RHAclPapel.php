<?php

namespace App\Models\RH;

use Illuminate\Database\Eloquent\Model;

class RHAclPapel extends Model
{
    protected $table = 'rh_acl_papeis';

    protected $fillable = [
        'empresa_id',
        'nome',
        'slug',
        'descricao',
        'ativo',
        'is_admin',
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'is_admin' => 'boolean',
    ];

    public function permissoes()
    {
        return $this->belongsToMany(RHAclPermissao::class, 'rh_acl_papel_permissoes', 'papel_id', 'permissao_id')
            ->withTimestamps();
    }

    public function usuarios()
    {
        return $this->belongsToMany(\App\Models\Usuario::class, 'rh_acl_papel_usuarios', 'papel_id', 'usuario_id')
            ->withPivot(['empresa_id', 'ativo'])
            ->withTimestamps();
    }
}
