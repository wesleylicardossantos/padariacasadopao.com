<?php

namespace App\Modules\Estoque\Models;

use Illuminate\Database\Eloquent\Model;

class StockWriteAudit extends Model
{
    protected $table = 'stock_write_audits';

    protected $fillable = [
        'empresa_id',
        'filial_id',
        'produto_id',
        'event',
        'legacy_stock_id',
        'before_state',
        'after_state',
        'guard_source',
        'guard_allowed',
        'performed_by',
        'request_path',
        'notes',
    ];

    protected $casts = [
        'before_state' => 'array',
        'after_state' => 'array',
        'guard_allowed' => 'boolean',
    ];
}
