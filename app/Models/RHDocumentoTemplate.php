<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RHDocumentoTemplate extends Model
{
    protected $table = 'rh_document_templates';

    protected $fillable = [
        'empresa_id',
        'nome',
        'slug',
        'categoria',
        'tipo_documento',
        'descricao',
        'conteudo_html',
        'conteudo_texto',
        'usa_ia',
        'ativo',
        'versao',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'usa_ia' => 'boolean',
        'ativo' => 'boolean',
    ];
}
