<?php

namespace App\Modules\Estoque\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $table = 'stock_movements';

    protected $fillable = [
        'empresa_id',
        'filial_id',
        'product_id',
        'type',
        'quantity',
        'balance_after',
        'unit_cost',
        'source',
        'source_id',
        'notes',
        'metadata',
        'performed_by',
        'occurred_at',
    ];

    protected $casts = [
        'quantity' => 'float',
        'balance_after' => 'float',
        'unit_cost' => 'float',
        'metadata' => 'array',
        'occurred_at' => 'datetime',
    ];
}
