<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class StockReconcileCommand extends Command
{
    protected $signature = 'stock:reconcile {empresa_id : Empresa para reconciliar} {--filial_id=} {--limit=500} {--write : Persiste JSON e Markdown em docs/operacao}';

    protected $description = 'Compara o saldo do ledger com o estoque legado por produto/filial.';

    public function handle(): int
    {
        if (!Schema::hasTable('stock_movements') || !Schema::hasTable('estoques')) {
            $this->error('As tabelas stock_movements e estoques precisam existir para rodar a reconciliação.');

            return self::FAILURE;
        }

        $empresaId = (int) $this->argument('empresa_id');
        $filialId = $this->option('filial_id');
        $limit = (int) $this->option('limit');

        $ledgerRows = DB::table('stock_movements')
            ->select('empresa_id', 'filial_id', DB::raw('product_id as produto_id'), DB::raw('MAX(balance_after) as saldo_ledger'))
            ->where('empresa_id', $empresaId)
            ->when($filialId !== null, fn ($query) => $query->where('filial_id', $filialId))
            ->groupBy('empresa_id', 'filial_id', 'product_id')
            ->get();

        $ledgerMap = [];
        foreach ($ledgerRows as $row) {
            $ledgerMap[$this->key($row->empresa_id, $row->filial_id, $row->produto_id)] = (float) $row->saldo_ledger;
        }

        $legacyRows = DB::table('estoques')
            ->select('empresa_id', 'filial_id', 'produto_id', 'quantidade')
            ->where('empresa_id', $empresaId)
            ->when($filialId !== null, fn ($query) => $query->where('filial_id', $filialId))
            ->get();

        $legacyMap = [];
        foreach ($legacyRows as $row) {
            $legacyMap[$this->key($row->empresa_id, $row->filial_id, $row->produto_id)] = (float) $row->quantidade;
        }

        $keys = array_unique(array_merge(array_keys($ledgerMap), array_keys($legacyMap)));
        $divergencias = [];

        foreach ($keys as $key) {
            [$keyEmpresa, $keyFilial, $keyProduto] = explode(':', $key);
            $saldoLedger = $ledgerMap[$key] ?? 0.0;
            $saldoLegado = $legacyMap[$key] ?? 0.0;
            $diff = round($saldoLedger - $saldoLegado, 4);
            if (abs($diff) <= 0.0001) {
                continue;
            }

            $divergencias[] = [
                'empresa_id' => (int) $keyEmpresa,
                'filial_id' => $keyFilial === 'null' ? null : (int) $keyFilial,
                'produto_id' => (int) $keyProduto,
                'saldo_legado' => $saldoLegado,
                'saldo_ledger' => $saldoLedger,
                'diferenca' => $diff,
            ];
        }

        usort($divergencias, fn ($a, $b) => abs($b['diferenca']) <=> abs($a['diferenca']));
        $divergencias = array_slice($divergencias, 0, $limit);

        if ($this->option('write')) {
            $this->writeArtifacts($empresaId, $filialId, $divergencias);
        }

        if (empty($divergencias)) {
            $this->info('Nenhuma divergência encontrada.');
            return self::SUCCESS;
        }

        $this->table(['produto_id', 'filial_id', 'saldo_legado', 'saldo_ledger', 'diferenca'], array_map(fn ($i) => [
            'produto_id' => $i['produto_id'],
            'filial_id' => $i['filial_id'],
            'saldo_legado' => $i['saldo_legado'],
            'saldo_ledger' => $i['saldo_ledger'],
            'diferenca' => $i['diferenca'],
        ], $divergencias));
        $this->warn('Divergências detectadas. Corrija os fluxos de escrita antes de reconciliar em produção.');

        return self::FAILURE;
    }

    private function key($empresaId, $filialId, $produtoId): string
    {
        return implode(':', [$empresaId, $filialId === null ? 'null' : $filialId, $produtoId]);
    }

    private function writeArtifacts(int $empresaId, $filialId, array $divergencias): void
    {
        $dir = base_path('docs/operacao');
        File::ensureDirectoryExists($dir);
        $suffix = $filialId !== null ? "empresa_{$empresaId}_filial_{$filialId}" : "empresa_{$empresaId}";
        File::put("{$dir}/stock_reconcile_{$suffix}.json", json_encode($divergencias, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $lines = [
            '# Stock Reconcile Report',
            '',
            '- Empresa: ' . $empresaId,
            '- Filial: ' . ($filialId ?? 'todas'),
            '- Divergências: ' . count($divergencias),
            '',
            '| produto_id | filial_id | saldo_legado | saldo_ledger | diferenca |',
            '|---|---:|---:|---:|---:|',
        ];
        foreach ($divergencias as $item) {
            $lines[] = sprintf('| %s | %s | %s | %s | %s |', $item['produto_id'], $item['filial_id'] ?? '-', $item['saldo_legado'], $item['saldo_ledger'], $item['diferenca']);
        }
        File::put("{$dir}/stock_reconcile_{$suffix}.md", implode(PHP_EOL, $lines));
    }
}
