<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RHRescisaoItem extends Model
{
    protected $table = 'rh_rescisao_itens';

    protected $fillable = [
        'rescisao_id',
        'codigo',
        'descricao',
        'tipo',
        'referencia',
        'valor',
    ];

    protected $casts = [
        'referencia' => 'float',
        'valor' => 'float',
    ];

    public function rescisao()
    {
        return $this->belongsTo(RHRescisao::class, 'rescisao_id');
    }
}
