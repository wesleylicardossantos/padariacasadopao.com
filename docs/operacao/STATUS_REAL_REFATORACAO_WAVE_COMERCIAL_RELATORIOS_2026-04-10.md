# Status real da wave Comercial/Relatórios 2026-04-10

- Consolidação de TenantContext em RelatorioController, DreController, ProductController, VendaController e PedidoController.
- Adição do middleware tenant.context nas rotas críticas de DRE, relatórios, produtos, vendas e pedidos.
- Blindagem de lookups por ID em DRE, produtos, vendas e pedidos para impedir acesso cross-tenant.
- Criação do comando refactor:comercial-relatorios-tenant-audit para auditoria contínua da wave.
