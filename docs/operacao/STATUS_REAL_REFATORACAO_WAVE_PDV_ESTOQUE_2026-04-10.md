# Status real da wave PDV/Estoque 2026-04-10

- Consolidação de TenantContext em StockController, Pdv\ProdutoController e controllers do PDV offline.
- Adição do middleware tenant.context nas rotas críticas de estoque, pdv-offline e mobile/pdv.
- Blindagem de lookups por ID em estoque para impedir acesso cross-tenant a produtos e filiais.
- Criação do comando refactor:pdv-estoque-tenant-audit para auditoria contínua da wave.
