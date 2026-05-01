<?php

namespace App\Http\Controllers;

use App\Models\ControleSalgado;
use App\Models\ControleSalgadoItem;
use App\Support\Tenancy\TenantContext;
use Dompdf\Dompdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ControleSalgadoController extends Controller
{
    private const ITENS_PADRAO_MANHA = [
        'Pão de Queijo',
        'Bolo de Tapioca',
        'Folhado Queijo',
        'Folhado queijo/presunto',
        'Folhado Frango',
    ];

    private const ITENS_PADRAO_TARDE = [
        'Pão de Queijo',
        'Bolo de Tapioca',
        'Folhado Queijo',
        'Folhado queijo/presunto',
        'Folhado Frango',
        'Croassant queijo',
        'Croassant queijo/presunto',
        'Croassant de frango',
        'Almofadinha',
        'Italiano frango',
        'Italiano queijo / presunto',
        'Enrolado salsisha',
        'Coxinha frango',
        'Risole carne',
    ];

    public function index(Request $request)
    {
        $empresaId = TenantContext::empresaId($request);

        $data = ControleSalgado::query()
            ->when($empresaId > 0, fn ($q) => $q->where('empresa_id', $empresaId))
            ->when($request->filled('data_inicio'), fn ($q) => $q->whereDate('data', '>=', $request->data_inicio))
            ->when($request->filled('data_fim'), fn ($q) => $q->whereDate('data', '<=', $request->data_fim))
            ->withCount([
                'itens as itens_manha_count' => fn ($q) => $q->where('periodo', 'manha'),
                'itens as itens_tarde_count' => fn ($q) => $q->where('periodo', 'tarde'),
            ])
            ->orderByDesc('data')
            ->orderByDesc('id')
            ->paginate((int) env('PAGINACAO', 20))
            ->appends($request->query());

        return view('controle_salgados.index', [
            'data' => $data,
            'filters' => [
                'data_inicio' => $request->data_inicio,
                'data_fim' => $request->data_fim,
            ],
        ]);
    }

    public function create()
    {
        return view('controle_salgados.create', [
            'item' => new ControleSalgado([
                'data' => now()->toDateString(),
                'dia' => ucfirst(now()->translatedFormat('l')),
            ]),
            'linhas' => $this->buildDefaultRows(),
        ]);
    }

    public function store(Request $request)
    {
        $payload = $this->validated($request);
        $empresaId = TenantContext::empresaId($request);
        $userId = TenantContext::userId($request);

        DB::beginTransaction();
        try {
            $controle = ControleSalgado::create([
                'empresa_id' => $empresaId,
                'data' => $payload['data'],
                'dia' => $payload['dia'] ?? null,
                'observacoes' => $payload['observacoes'] ?? null,
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);

            $this->syncItens($controle, $payload);

            DB::commit();
            session()->flash('flash_sucesso', 'Controle de salgados salvo com sucesso!');
        } catch (\Throwable $e) {
            DB::rollBack();
            session()->flash('flash_erro', 'Erro ao salvar controle de salgados: ' . $e->getMessage());
            if (function_exists('__saveLogError')) {
                __saveLogError($e, $empresaId);
            }
            return redirect()->back()->withInput();
        }

        return redirect()->route('controle.salgados.index');
    }

    public function show(Request $request, $id)
    {
        $item = $this->findOrFailScoped($request, $id);

        return view('controle_salgados.show', compact('item'));
    }

    public function edit(Request $request, $id)
    {
        $item = $this->findOrFailScoped($request, $id);

        return view('controle_salgados.edit', [
            'item' => $item,
            'linhas' => $this->buildRowsFromModel($item),
        ]);
    }

    public function update(Request $request, $id)
    {
        $payload = $this->validated($request);
        $empresaId = TenantContext::empresaId($request);
        $userId = TenantContext::userId($request);
        $controle = $this->findOrFailScoped($request, $id);

        DB::beginTransaction();
        try {
            $controle->update([
                'data' => $payload['data'],
                'dia' => $payload['dia'] ?? null,
                'observacoes' => $payload['observacoes'] ?? null,
                'updated_by' => $userId,
            ]);

            $controle->itens()->delete();
            $this->syncItens($controle, $payload);

            DB::commit();
            session()->flash('flash_sucesso', 'Controle de salgados atualizado com sucesso!');
        } catch (\Throwable $e) {
            DB::rollBack();
            session()->flash('flash_erro', 'Erro ao atualizar controle de salgados: ' . $e->getMessage());
            if (function_exists('__saveLogError')) {
                __saveLogError($e, $empresaId);
            }
            return redirect()->back()->withInput();
        }

        return redirect()->route('controle.salgados.index');
    }

    public function destroy(Request $request, $id)
    {
        $empresaId = TenantContext::empresaId($request);
        $controle = $this->findOrFailScoped($request, $id);

        try {
            $controle->delete();
            session()->flash('flash_sucesso', 'Lançamento removido com sucesso!');
        } catch (\Throwable $e) {
            session()->flash('flash_erro', 'Erro ao remover lançamento: ' . $e->getMessage());
            if (function_exists('__saveLogError')) {
                __saveLogError($e, $empresaId);
            }
        }

        return redirect()->route('controle.salgados.index');
    }

    public function pdf(Request $request, $id)
    {
        $item = $this->findOrFailScoped($request, $id);
        $html = view('controle_salgados.pdf', compact('item'))->render();

        $domPdf = new Dompdf(['enable_remote' => true]);
        $domPdf->loadHtml($html, 'UTF-8');
        $domPdf->setPaper('A4', 'portrait');
        $domPdf->render();

        return response($domPdf->output())
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="controle-salgados-'.$item->id.'.pdf"');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'data' => ['required', 'date'],
            'dia' => ['nullable', 'string', 'max:60'],
            'observacoes' => ['nullable', 'string', 'max:1000'],
            'manha' => ['nullable', 'array'],
            'manha.*.descricao' => ['nullable', 'string', 'max:255'],
            'manha.*.qtd' => ['nullable', 'integer', 'min:0'],
            'manha.*.termino' => ['nullable', 'string', 'max:30'],
            'manha.*.saldo' => ['nullable', 'integer', 'min:0'],
            'tarde' => ['nullable', 'array'],
            'tarde.*.descricao' => ['nullable', 'string', 'max:255'],
            'tarde.*.qtd' => ['nullable', 'integer', 'min:0'],
            'tarde.*.termino' => ['nullable', 'string', 'max:30'],
            'tarde.*.saldo' => ['nullable', 'integer', 'min:0'],
        ]);
    }

    private function syncItens(ControleSalgado $controle, array $payload): void
    {
        foreach (['manha', 'tarde'] as $periodo) {
            $rows = $payload[$periodo] ?? [];
            foreach ($rows as $ordem => $row) {
                $descricao = trim((string) ($row['descricao'] ?? ''));
                $hasValue = $descricao !== ''
                    || $row['qtd'] !== null
                    || ($row['termino'] ?? '') !== ''
                    || $row['saldo'] !== null;

                if (! $hasValue) {
                    continue;
                }

                ControleSalgadoItem::create([
                    'controle_salgado_id' => $controle->id,
                    'periodo' => $periodo,
                    'ordem' => $ordem + 1,
                    'descricao' => $descricao,
                    'qtd' => $row['qtd'] ?? null,
                    'termino' => trim((string) ($row['termino'] ?? '')) ?: null,
                    'saldo' => $row['saldo'] ?? null,
                ]);
            }
        }
    }

    private function findOrFailScoped(Request $request, int $id): ControleSalgado
    {
        $empresaId = TenantContext::empresaId($request);

        return ControleSalgado::query()
            ->with('itens')
            ->when($empresaId > 0, fn ($q) => $q->where('empresa_id', $empresaId))
            ->findOrFail($id);
    }

    private function buildDefaultRows(): array
    {
        return [
            'manha' => $this->mapDescriptions(self::ITENS_PADRAO_MANHA),
            'tarde' => $this->mapDescriptions(self::ITENS_PADRAO_TARDE),
        ];
    }

    private function buildRowsFromModel(ControleSalgado $item): array
    {
        $manha = $item->itens->where('periodo', 'manha')->sortBy('ordem')->values()->map(function ($row) {
            return [
                'descricao' => $row->descricao,
                'qtd' => $row->qtd,
                'termino' => $row->termino,
                'saldo' => $row->saldo,
            ];
        })->all();

        $tarde = $item->itens->where('periodo', 'tarde')->sortBy('ordem')->values()->map(function ($row) {
            return [
                'descricao' => $row->descricao,
                'qtd' => $row->qtd,
                'termino' => $row->termino,
                'saldo' => $row->saldo,
            ];
        })->all();

        return [
            'manha' => $manha ?: $this->mapDescriptions(self::ITENS_PADRAO_MANHA),
            'tarde' => $tarde ?: $this->mapDescriptions(self::ITENS_PADRAO_TARDE),
        ];
    }

    private function mapDescriptions(array $items): array
    {
        return collect($items)->map(fn ($descricao) => [
            'descricao' => $descricao,
            'qtd' => null,
            'termino' => null,
            'saldo' => null,
        ])->all();
    }
}
