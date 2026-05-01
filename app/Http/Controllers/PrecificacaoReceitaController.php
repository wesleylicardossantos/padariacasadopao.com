<?php

namespace App\Http\Controllers;

use App\Models\PrecificacaoReceita;
use App\Support\PrecificacaoSchema;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\Request;

class PrecificacaoReceitaController extends Controller
{
    public function index(Request $request)
    {
        $empresaId = TenantContext::empresaId($request);
        $estruturaOk = PrecificacaoSchema::hasTable('precificacao_receitas');
        $itensOk = PrecificacaoSchema::hasTable('precificacao_receita_itens');
        $colunas = [
            'empresa_id' => PrecificacaoSchema::hasColumn('precificacao_receitas', 'empresa_id'),
            'rendimento' => PrecificacaoSchema::hasColumn('precificacao_receitas', 'rendimento'),
            'unidade_rendimento' => PrecificacaoSchema::hasColumn('precificacao_receitas', 'unidade_rendimento'),
            'status' => PrecificacaoSchema::hasColumn('precificacao_receitas', 'status'),
        ];

        $receitas = collect();
        if ($estruturaOk) {
            $query = PrecificacaoReceita::query();
            if ($colunas['empresa_id'] && $empresaId) {
                $query->where('empresa_id', $empresaId);
            }
            if ($itensOk) {
                $query->withCount('itens');
            }
            $receitas = $query->orderBy('nome')->limit(200)->get();
        }

        return view('precificacao.receitas.index', [
            'title' => 'Fichas Técnicas',
            'estruturaOk' => $estruturaOk,
            'itensOk' => $itensOk,
            'colunas' => $colunas,
            'receitas' => $receitas,
        ]);
    }

    public function create(Request $request)
    {
        $estruturaOk = PrecificacaoSchema::hasTable('precificacao_receitas');
        return view('precificacao.receitas.form', [
            'title' => 'Nova Ficha Técnica',
            'estruturaOk' => $estruturaOk,
            'receita' => new PrecificacaoReceita([
                'status' => 'ativo',
                'unidade_rendimento' => 'UN',
                'rendimento' => 1,
            ]),
            'formAction' => route('precificacao.receitas.store'),
            'method' => 'POST',
            'isEdit' => false,
        ]);
    }

    public function store(Request $request)
    {
        if (!PrecificacaoSchema::hasTable('precificacao_receitas')) {
            return redirect()->route('precificacao.receitas.index')->with('error', 'Tabela de fichas técnicas não encontrada no banco.');
        }

        $data = $this->validatedData($request);
        if (PrecificacaoSchema::hasColumn('precificacao_receitas', 'empresa_id')) {
            $empresaId = TenantContext::empresaId($request);
            if ($empresaId) {
                $data['empresa_id'] = $empresaId;
            }
        }

        PrecificacaoReceita::create($data);

        return redirect()->route('precificacao.receitas.index')->with('success', 'Ficha técnica criada com sucesso.');
    }

    public function edit($id)
    {
        if (!PrecificacaoSchema::hasTable('precificacao_receitas')) {
            return redirect()->route('precificacao.receitas.index')->with('error', 'Tabela de fichas técnicas não encontrada no banco.');
        }

        $receita = PrecificacaoReceita::findOrFail($id);

        return view('precificacao.receitas.form', [
            'title' => 'Editar Ficha Técnica',
            'estruturaOk' => true,
            'receita' => $receita,
            'formAction' => route('precificacao.receitas.update', $receita->id),
            'method' => 'PUT',
            'isEdit' => true,
        ]);
    }

    public function update(Request $request, $id)
    {
        if (!PrecificacaoSchema::hasTable('precificacao_receitas')) {
            return redirect()->route('precificacao.receitas.index')->with('error', 'Tabela de fichas técnicas não encontrada no banco.');
        }

        $receita = PrecificacaoReceita::findOrFail($id);
        $receita->update($this->validatedData($request));

        return redirect()->route('precificacao.receitas.index')->with('success', 'Ficha técnica atualizada com sucesso.');
    }

    public function duplicate($id)
    {
        if (!PrecificacaoSchema::hasTable('precificacao_receitas')) {
            return redirect()->route('precificacao.receitas.index')->with('error', 'Tabela de fichas técnicas não encontrada no banco.');
        }

        $receita = PrecificacaoReceita::with('itens')->findOrFail($id);
        $nova = $receita->replicate();
        $nova->nome = ($receita->nome ?? 'Ficha') . ' (Cópia)';
        $nova->push();

        if (PrecificacaoSchema::hasTable('precificacao_receita_itens')) {
            foreach ($receita->itens as $item) {
                $novoItem = $item->replicate();
                $novoItem->receita_id = $nova->id;
                $novoItem->save();
            }
        }

        return redirect()->route('precificacao.receitas.edit', $nova->id)->with('success', 'Ficha técnica duplicada com sucesso.');
    }

    public function destroy($id)
    {
        if (!PrecificacaoSchema::hasTable('precificacao_receitas')) {
            return redirect()->route('precificacao.receitas.index')->with('error', 'Tabela de fichas técnicas não encontrada no banco.');
        }

        $receita = PrecificacaoReceita::findOrFail($id);
        if (PrecificacaoSchema::hasTable('precificacao_receita_itens')) {
            $receita->itens()->delete();
        }
        $receita->delete();

        return redirect()->route('precificacao.receitas.index')->with('success', 'Ficha técnica excluída com sucesso.');
    }

    protected function validatedData(Request $request): array
    {
        $data = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'rendimento' => ['nullable', 'numeric', 'min:0'],
            'unidade_rendimento' => ['nullable', 'string', 'max:20'],
            'status' => ['nullable', 'string', 'max:50'],
        ]);

        if (!PrecificacaoSchema::hasColumn('precificacao_receitas', 'rendimento')) {
            unset($data['rendimento']);
        }
        if (!PrecificacaoSchema::hasColumn('precificacao_receitas', 'unidade_rendimento')) {
            unset($data['unidade_rendimento']);
        }
        if (!PrecificacaoSchema::hasColumn('precificacao_receitas', 'status')) {
            unset($data['status']);
        }

        return $data;
    }
}
