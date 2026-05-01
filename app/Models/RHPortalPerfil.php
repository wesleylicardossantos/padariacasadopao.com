<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RHPortalPerfil extends Model
{
    protected $table = 'rh_portal_perfis';

    public const PERMISSOES = [
        'dashboard.visualizar' => 'Dashboard do portal',
        'holerites.visualizar' => 'Visualizar holerites',
        'produtos.visualizar' => 'Consultar produtos',
        'documentos.visualizar' => 'Visualizar documentos',
        'comissoes.visualizar' => 'Consultar comissões',
        'pedidos.visualizar' => 'Consultar pedidos',
        'dossie.visualizar' => 'Consultar dossiê do funcionário',
        'documentos.rescisao.visualizar' => 'Consultar documentos de rescisão',
    ];

    protected $fillable = [
        'empresa_id',
        'nome',
        'slug',
        'descricao',
        'ativo',
        'permissoes',
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'permissoes' => 'array',
    ];

    public static function permissoesDisponiveis(): array
    {
        return self::PERMISSOES;
    }

    public static function perfisPadrao(): array
    {
        return [
            [
                'nome' => 'Portal básico',
                'slug' => 'portal-basico',
                'descricao' => 'Acesso ao dashboard e aos holerites do funcionário.',
                'ativo' => true,
                'permissoes' => ['dashboard.visualizar', 'holerites.visualizar'],
            ],
            [
                'nome' => 'Portal comercial',
                'slug' => 'portal-comercial',
                'descricao' => 'Holerites e consulta de produtos.',
                'ativo' => true,
                'permissoes' => ['dashboard.visualizar', 'holerites.visualizar', 'produtos.visualizar'],
            ],
            [
                'nome' => 'Portal completo',
                'slug' => 'portal-completo',
                'descricao' => 'Perfil pronto para evoluções futuras do portal.',
                'ativo' => true,
                'permissoes' => array_keys(self::PERMISSOES),
            ],
        ];
    }

    public function portalFuncionarios()
    {
        return $this->hasMany(RHPortalFuncionario::class, 'perfil_id');
    }
}
