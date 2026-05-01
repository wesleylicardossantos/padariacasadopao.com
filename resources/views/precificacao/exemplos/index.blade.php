@extends('default.layout',['title' => 'Exemplos de Precificação'])
@section('content')
<div class="page-content">
    <style>
        .mc-shell{display:flex;flex-direction:column;gap:18px}
        .mc-card{border:1px solid #e7ebf3;border-radius:18px;background:#fff;box-shadow:0 10px 30px rgba(15,23,42,.05)}
        .mc-toolbar,.mc-pills,.mc-actions{display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap}
        .mc-title{font-size:1.6rem;font-weight:800;color:#122033;margin:0}
        .mc-subtitle{margin:4px 0 0;color:#6b7280}
        .mc-pill{padding:.65rem 1rem;border-radius:14px;border:1px solid #dbe4f0;background:#fff;font-weight:700;color:#24364b;text-decoration:none;display:inline-flex}
        .mc-pill.active{background:#f5f8fc;border-color:#b7c8de;color:#0f172a}
        .mc-btn{padding:.75rem 1rem;border-radius:14px;font-weight:700;border:1px solid #dbe4f0;background:#fff;color:#1f2937;text-decoration:none;display:inline-flex;align-items:center;gap:8px}
        .mc-btn.primary{background:#0f172a;color:#fff;border-color:#0f172a}
        .mc-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:16px}
        .mc-badge{display:inline-flex;padding:.3rem .65rem;border-radius:999px;background:#eef4ff;color:#3657a7;font-size:.78rem;font-weight:700}
    </style>
    <div class="mc-shell">
        <div class="mc-toolbar">
            <div>
                <div class="mc-pills mb-3">
                    <a href="{{ route('produtos.index') }}" class="mc-pill">Mercadoria</a>
                    <a href="{{ route('precificacao.receitas.index') }}" class="mc-pill">Receita</a>
                    <a href="{{ route('precificacao.exemplos.index') }}" class="mc-pill active">Exemplos</a>
                    <a href="{{ route('precificacao.index') }}" class="mc-pill">Itens de cardápio</a>
                </div>
                <h4 class="mc-title">Exemplos / Kit avançado padaria</h4>
                <p class="mc-subtitle">Atalhos práticos para os modelos padrão implantados no seu módulo de precificação.</p>
            </div>
            <div class="mc-actions">
                <form action="{{ route('precificacao.kit-padaria.instalar') }}" method="POST" onsubmit="return confirm('Implantar ou atualizar o kit avançado de padaria neste projeto?')">
                    @csrf
                    <button type="submit" class="mc-btn">Implantar kit avançado padaria</button>
                </form>
                <a href="{{ route('precificacao.sugestoes.index') }}" class="mc-btn primary">Atualizar preços</a>
                <a href="{{ route('precificacao.dashboard-executivo.index') }}" class="mc-btn">Centro de comando</a>
            </div>
        </div>

        <div class="mc-card p-4">
            <div class="row g-3">
                @foreach($exemplos as $exemplo)
                    <div class="col-lg-4 col-md-6">
                        <div class="mc-card p-4 h-100">
                            <span class="mc-badge mb-3">{{ $exemplo['grupo'] }}</span>
                            <h5 class="fw-bold mb-2">{{ $exemplo['nome'] }}</h5>
                            <p class="text-muted mb-3">Modelo pronto para revisar, recalcular e usar como referência no módulo.</p>
                            <a href="{{ route($exemplo['rota']) }}" class="mc-btn">Abrir fichas técnicas</a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="mc-card p-4">
            <h5 class="fw-bold mb-3">Últimos produtos precificados</h5>
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th>Receita</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($produtos as $produto)
                            <tr>
                                <td>{{ $produto->nome ?? '-' }}</td>
                                <td>{{ $produto->receita->nome ?? '-' }}</td>
                                <td>{{ $produto->ativo ? 'Ativo' : 'Inativo' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted">Nenhum produto precificado encontrado ainda.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
