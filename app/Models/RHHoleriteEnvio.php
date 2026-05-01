<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class RHHoleriteEnvio extends Model
{
    use HasFactory;

    protected $table = 'rh_holerite_envios';

    protected $fillable = [
        'lote_id', 'empresa_id', 'apuracao_mensal_id', 'funcionario_id', 'email',
        'status', 'erro', 'tentativas', 'ultima_falha', 'ultima_tentativa_em', 'enviado_em',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
        'ultima_tentativa_em' => 'datetime',
        'enviado_em' => 'datetime',
    ];

    public function getErroCompatAttribute(): ?string
    {
        return $this->attributes['erro']
            ?? $this->attributes['ultima_falha']
            ?? null;
    }

    public function setErroCompatAttribute(?string $value): void
    {
        if (Schema::hasColumn($this->getTable(), 'erro')) {
            $this->attributes['erro'] = $value;
        }

        if (Schema::hasColumn($this->getTable(), 'ultima_falha')) {
            $this->attributes['ultima_falha'] = $value;
        }
    }

    public function getUltimaTentativaCompatAttribute()
    {
        return $this->attributes['ultima_tentativa_em']
            ?? $this->attributes['updated_at']
            ?? null;
    }

    public function lote()
    {
        return $this->belongsTo(RHHoleriteEnvioLote::class, 'lote_id');
    }

    public function funcionario()
    {
        return $this->belongsTo(Funcionario::class, 'funcionario_id');
    }

    public function apuracao()
    {
        return $this->belongsTo(ApuracaoMensal::class, 'apuracao_mensal_id');
    }
}
