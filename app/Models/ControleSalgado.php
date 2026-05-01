<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ControleSalgado extends Model
{
    protected $table = 'controle_salgados';

    protected $fillable = [
        'empresa_id',
        'data',
        'dia',
        'observacoes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'data' => 'date',
    ];

    public function itens()
    {
        return $this->hasMany(ControleSalgadoItem::class, 'controle_salgado_id')->orderBy('periodo')->orderBy('ordem');
    }

    public function itensManha()
    {
        return $this->hasMany(ControleSalgadoItem::class, 'controle_salgado_id')
            ->where('periodo', 'manha')
            ->orderBy('ordem');
    }

    public function itensTarde()
    {
        return $this->hasMany(ControleSalgadoItem::class, 'controle_salgado_id')
            ->where('periodo', 'tarde')
            ->orderBy('ordem');
    }
}
