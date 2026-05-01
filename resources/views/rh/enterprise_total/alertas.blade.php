@extends('default.layout',['title' => 'RH - Alertas Enterprise'])
@section('content')
<style>
.alerta{border-left:4px solid #94a3b8;border-radius:10px;padding:.9rem 1rem;background:#fff}
.alerta.ok{border-left-color:#16a34a;background:#f0fdf4}
.alerta.alerta{border-left-color:#f59e0b;background:#fff7ed}
.alerta.critico{border-left-color:#dc2626;background:#fef2f2}
</style>
<div class="page-content">
    <div class="card">
        <div class="card-body p-4">
            <h5 class="mb-3">Alertas Enterprise</h5>
            <p class="text-muted">Competência {{ str_pad($mes,2,'0',STR_PAD_LEFT) }}/{{ $ano }}</p>
            <div class="d-grid gap-2">
                @foreach($alertas as $alerta)
                    <div class="alerta {{ $alerta['nivel'] }}">{{ $alerta['texto'] }}</div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
