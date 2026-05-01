<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Support\Tenancy\InteractsWithTenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class FinanceiroController extends Controller
{
    use InteractsWithTenantContext;

    public function __construct()
    {
        $this->middleware('tenant.context');
    }

    public function index(Request $request)
    {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $dateColumn = in_array($request->get('tipo'), ['data_registro', 'paid_at', 'created_at', 'updated_at'], true)
            ? $request->get('tipo')
            : 'data_registro';
        $estado = $request->get('estado');
        $statusColumn = Schema::hasColumn('payments', 'status') ? 'status' : (Schema::hasColumn('payments', 'estado') ? 'estado' : null);

        $empresaId = $this->tenantEmpresaId($request);

        $data = Payment::query()
            ->where('empresa_id', $empresaId)
            ->when($request->filled('nome'), fn ($query) => $query->where('descricao', 'like', '%' . $request->nome . '%'))
            ->when($startDate, fn ($query) => $query->where($dateColumn, '>=', $startDate))
            ->when($endDate, fn ($query) => $query->where($dateColumn, '<=', $endDate))
            ->when($statusColumn && $estado !== null && $estado !== '' && $estado !== 'todos', fn ($query) => $query->where($statusColumn, $estado))
            ->orderByDesc($dateColumn)
            ->paginate((int) env('PAGINACAO', 20));

        return view('financeiro.index', compact('data'));
    }

    public function list(Request $request)
    {
        $empresaId = $this->tenantEmpresaId($request);

        $data = Payment::query()
            ->where('empresa_id', $empresaId)
            ->latest('created_at')
            ->paginate((int) env('PAGINACAO', 20));
        return view('financeiro.list', compact('data'));
    }
}
