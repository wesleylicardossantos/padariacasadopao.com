<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use App\Modules\RH\Support\Enums\EmployeeStatus;

class Funcionario extends Model
{
    protected static function booted(): void
    {
        static::addGlobalScope('rh_status_visibility', function (Builder $builder) {
            if (!self::shouldApplyStatusVisibilityScope()) {
                return;
            }

            $table = $builder->getModel()->getTable();
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'ativo')) {
                return;
            }

            $request = request();
            $arquivoMorto = $request?->boolean('arquivo_morto', false) ?? false;
            $status = (string) ($request?->query('status', '') ?? '');

            $builder->where(function (Builder $query) use ($arquivoMorto, $status, $table) {
                $qual = $table . '.ativo';

                if ($arquivoMorto || $status == '0') {
                    $query->whereIn($qual, [0, '0', 'N', 'n', 'NAO', 'nao', 'NÃO', 'não', 'I', 'i']);
                    return;
                }

                if ($status === '' || $status == '1') {
                    $query->whereNull($qual)->orWhereIn($qual, [1, '1', 'S', 's', 'SIM', 'sim', 'A', 'a']);
                }
            });
        });
    }

    public function scopeComInativos(Builder $query): Builder
    {
        return $query->withoutGlobalScope('rh_status_visibility');
    }

    public function scopeArquivoMorto(Builder $query): Builder
    {
        return $query->withoutGlobalScope('rh_status_visibility')
            ->whereIn('ativo', [0, '0', 'N', 'n', 'NAO', 'nao', 'NÃO', 'não', 'I', 'i']);
    }

    public function scopeSomenteAtivos(Builder $query): Builder
    {
        return $query->withoutGlobalScope('rh_status_visibility')
            ->where(function (Builder $inner) {
                $inner->whereNull('ativo')->orWhereIn('ativo', [1, '1', 'S', 's', 'SIM', 'sim', 'A', 'a']);
            });
    }


    public function scopeAtivos(Builder $query): Builder
    {
        return $this->scopeSomenteAtivos($query);
    }

    public function scopeInativos(Builder $query): Builder
    {
        return $query->withoutGlobalScope('rh_status_visibility')
            ->whereIn('ativo', [0, '0', 'N', 'n', 'NAO', 'nao', 'NÃO', 'não', 'I', 'i']);
    }

    public function scopeForTenant(Builder $query, ?int $empresaId = null, $filialId = null): Builder
    {
        if ($empresaId && Schema::hasColumn($this->getTable(), 'empresa_id')) {
            $query->where($this->getTable() . '.empresa_id', $empresaId);
        }

        if ($filialId !== null && $filialId !== '' && Schema::hasColumn($this->getTable(), 'filial_id')) {
            $query->where(function (Builder $inner) use ($filialId) {
                $inner->where($this->getTable() . '.filial_id', $filialId)->orWhereNull($this->getTable() . '.filial_id');
            });
        }

        return $query;
    }

    public function isActive(): bool
    {
        return EmployeeStatus::isActiveValue($this->ativo ?? null);
    }

    public function isInactive(): bool
    {
        return ! $this->isActive();
    }

    public function statusLabel(): string
    {
        return $this->isActive() ? 'Ativo' : 'Arquivado';
    }

    protected static function shouldApplyStatusVisibilityScope(): bool
    {
        if (app()->runningInConsole()) {
            return false;
        }

        if (!app()->bound('request')) {
            return false;
        }

        $route = request()->route();
        $routeName = $route?->getName();

        if (in_array($routeName, ['funcionarios.show', 'funcionarios.edit', 'funcionarios.update', 'funcionarios.destroy', 'funcionarios.toggleStatus', 'funcionarios.imprimir'], true)) {
            return false;
        }

        return !request()->boolean('with_inativos', false);
    }

    protected $fillable = [
        'nome', 'bairro', 'numero', 'rua', 'cpf', 'rg', 'telefone', 'celular',
        'email', 'data_registro', 'empresa_id', 'usuario_id', 'percentual_comissao',
        'salario', 'funcao', 'cidade_id', 'ativo'
    ];

    public function contatos()
    {
        return $this->hasMany('App\Models\ContatoFuncionario', 'funcionario_id', 'id');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function cidade()
    {
        return $this->belongsTo(Cidade::class, 'cidade_id');
    }

    public function eventos()
    {
        return $this->hasMany(FuncionarioEvento::class, 'funcionario_id');
    }

    public function eventosAtivos()
    {
        return $this->hasMany(FuncionarioEvento::class, 'funcionario_id')->where('ativo', 1);
    }

    public function fichaAdmissao()
    {
        return $this->hasOne(FuncionarioFichaAdmissao::class, 'funcionario_id');
    }

    public function dependentes()
    {
        return $this->hasMany(FuncionarioDependente::class, 'funcionario_id')->orderBy('nome');
    }

    public function dossieRh()
    {
        return $this->hasOne(RHDossie::class, 'funcionario_id');
    }

    public function documentosRh()
    {
        return $this->hasMany(RHDocumento::class, 'funcionario_id')->orderByDesc('id');
    }

    public function historicos()
    {
        return $this->hasMany(HistoricoFuncionario::class, 'funcionario_id')->orderBy('created_at', 'desc');
    }
}
