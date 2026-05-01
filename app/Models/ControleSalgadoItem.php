<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ControleSalgadoItem extends Model
{
    protected $table = 'controle_salgado_itens';

    protected $fillable = [
        'controle_salgado_id',
        'periodo',
        'ordem',
        'descricao',
        'qtd',
        'termino',
        'saldo',
    ];

    protected $casts = [
        'qtd' => 'integer',
        'saldo' => 'integer',
        'ordem' => 'integer',
    ];

    public function controle()
    {
        return $this->belongsTo(ControleSalgado::class, 'controle_salgado_id');
    }
}
