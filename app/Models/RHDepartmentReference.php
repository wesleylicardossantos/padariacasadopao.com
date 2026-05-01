<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RHDepartmentReference extends Model
{
    protected $table = 'rh_department_references';

    protected $fillable = [
        'codigo',
        'descricao',
        'ordem',
        'ativo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];
}
