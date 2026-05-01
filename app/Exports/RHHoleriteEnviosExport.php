<?php

namespace App\Exports;

use App\Models\RHHoleriteEnvioLote;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class RHHoleriteEnviosExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(private RHHoleriteEnvioLote $lote)
    {
    }

    public function collection(): Collection
    {
        return $this->lote->envios;
    }

    public function headings(): array
    {
        return [
            'Lote',
            'Competência',
            'Funcionário',
            'E-mail',
            'Status',
            'Tentativas',
            'Última falha',
            'Última tentativa',
            'Enviado em',
        ];
    }

    public function map($envio): array
    {
        return [
            $this->lote->id,
            sprintf('%02d/%04d', $this->lote->mes, $this->lote->ano),
            optional($envio->funcionario)->nome ?: 'Funcionário',
            $envio->email,
            $envio->status,
            (int) $envio->tentativas,
            $envio->erro ?? $envio->ultima_falha,
            optional($envio->ultima_tentativa_em ?? $envio->updated_at)->format('d/m/Y H:i:s'),
            optional($envio->enviado_em)->format('d/m/Y H:i:s'),
        ];
    }
}
