<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class RHHoleriteEnvioLote extends Model
{
    use HasFactory;

    protected $table = 'rh_holerite_envio_lotes';

    protected $fillable = [
        'empresa_id', 'mes', 'ano', 'status', 'queue_connection', 'queue_name',
        'total', 'pendentes', 'processando', 'enviados', 'sem_email', 'falhas',
        'solicitado_por', 'observacao', 'iniciado_em', 'concluido_em',
    ];

    protected $casts = [
        'iniciado_em' => 'datetime',
        'concluido_em' => 'datetime',
    ];

    public function envios()
    {
        return $this->hasMany(RHHoleriteEnvio::class, 'lote_id');
    }

    public function recalculateStatus(): void
    {
        $totais = $this->envios()
            ->selectRaw("COUNT(*) as total")
            ->selectRaw("SUM(CASE WHEN status = 'fila' THEN 1 ELSE 0 END) as pendentes")
            ->selectRaw("SUM(CASE WHEN status = 'processando' THEN 1 ELSE 0 END) as processando")
            ->selectRaw("SUM(CASE WHEN status = 'enviado' THEN 1 ELSE 0 END) as enviados")
            ->selectRaw("SUM(CASE WHEN status = 'sem_email' THEN 1 ELSE 0 END) as sem_email")
            ->selectRaw("SUM(CASE WHEN status = 'falha' THEN 1 ELSE 0 END) as falhas")
            ->selectRaw("SUM(CASE WHEN status = 'cancelado' THEN 1 ELSE 0 END) as cancelados")
            ->first();

        $this->fill([
            'total' => (int) ($totais->total ?? 0),
            'enviados' => (int) ($totais->enviados ?? 0),
            'falhas' => (int) ($totais->falhas ?? 0),
            'sem_email' => (int) ($totais->sem_email ?? 0),
        ]);

        $pendentes = (int) ($totais->pendentes ?? 0);
        $processando = (int) ($totais->processando ?? 0);
        $cancelados = (int) ($totais->cancelados ?? 0);

        if (Schema::hasColumn($this->getTable(), 'pendentes')) {
            $this->pendentes = $pendentes;
        }

        if (Schema::hasColumn($this->getTable(), 'processando')) {
            $this->processando = $processando;
        }

        if ($this->total > 0 && ($this->enviados + $this->sem_email + $this->falhas + $cancelados) >= $this->total) {
            $this->status = 'finalizado';
        } elseif ($processando > 0) {
            $this->status = 'processando';
        } elseif ($pendentes > 0) {
            $this->status = 'fila';
        } elseif ($cancelados > 0) {
            $this->status = 'cancelado';
        } else {
            $this->status = 'fila';
        }

        if (Schema::hasColumn($this->getTable(), 'concluido_em')) {
            $this->concluido_em = $this->status === 'finalizado' || $this->status === 'cancelado'
                ? ($this->concluido_em ?: now())
                : null;
        }

        $this->save();
    }
}
