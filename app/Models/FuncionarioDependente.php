<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FuncionarioDependente extends Model
{
    protected $table = 'funcionarios_dependentes';

    protected $fillable = [
        'empresa_id',
        'funcionario_id',
        'nome',
        'data_nascimento',
        'local_nascimento',
        'parentesco',
    ];

    protected $casts = [
        'data_nascimento' => 'date',
    ];

    public function funcionario()
    {
        return $this->belongsTo(Funcionario::class, 'funcionario_id');
    }
}
