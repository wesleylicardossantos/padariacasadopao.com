<?php

namespace App\Modules\Fiscal\Services;

use App\Modules\Fiscal\Models\FiscalDocument;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FiscalOperationsReportService
{
    public function summary(int $empresaId): array
    {
        $base = [
            'empresa_id' => $empresaId,
            'documents_total' => 0,
            'prepared_total' => 0,
            'transmitted_total' => 0,
            'cancelled_total' => 0,
            'latest_documents' => [],
            'audit_total' => 0,
            'updated_at' => now()->toDateTimeString(),
        ];

        if (!Schema::hasTable('fiscal_documents')) {
            return $base;
        }

        $query = FiscalDocument::query()->where('empresa_id', $empresaId);
        $base['documents_total'] = (clone $query)->count();
        $base['prepared_total'] = (clone $query)->where('status', 'prepared')->count();
        $base['transmitted_total'] = (clone $query)->whereIn('status', ['transmitted', 'authorized'])->count();
        $base['cancelled_total'] = (clone $query)->where('status', 'cancelled')->count();
        $base['latest_documents'] = (clone $query)->latest('id')->limit(10)->get()->toArray();

        if (Schema::hasTable('fiscal_audits')) {
            $base['audit_total'] = DB::table('fiscal_audits')->where('empresa_id', $empresaId)->count();
        }

        return $base;
    }
}
