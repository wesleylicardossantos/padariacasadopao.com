# Correção global de tenancy

Aplicado:
- middleware `ResolveTenantContext` no grupo `web` e `api`;
- injeção de `empresa_id` no request para compatibilidade com controllers legados que usam `request()->empresa_id`;
- persistência em `app('tenant.empresa_id')`, `request attributes` e `session('empresa_id')`.

Objetivo:
- evitar listagens vazias quando a aplicação depende de `request()->empresa_id` e o tenant estava apenas em sessão ou usuário autenticado.
