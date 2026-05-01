# Status real da wave Runtime Fix 2026-04-10

- Gap de runtime do `tenant.context` resolvido nos módulos Comercial/Relatórios.
- Controllers críticos agora forçam `tenant.context` no construtor, além do endurecimento já existente em rotas e trait de tenant.
- Auditoria `refactor:comercial-relatorios-tenant-audit --write` passou integralmente nas rotas auditadas.
- Esta wave reduz o risco de vazamento cross-tenant em DRE, Relatórios, Produtos, Vendas e Pedidos sem depender exclusivamente da precedência de arquivos de rota legados.
