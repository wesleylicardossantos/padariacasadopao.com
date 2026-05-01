# Wave Runtime Tenant Context Fix 2026-04-10

## Objetivo
Fechar o gap entre endurecimento em arquivo de rota e middleware efetivo em runtime para os módulos Comercial/Relatórios.

## Ajustes aplicados
- Inclusão explícita de `tenant.context` no construtor dos controllers:
  - `RelatorioController`
  - `DreController`
  - `ProductController`
  - `VendaController`
  - `PedidoController`
- Preservação do `InteractsWithTenantContext` como camada adicional de resolução de empresa.
- Reexecução da auditoria `refactor:comercial-relatorios-tenant-audit --write`.

## Resultado validado
A auditoria passou com `tenant.context=sim` e `verificaEmpresa=sim` para:
- `dre.*`
- `relatorios.*`
- `produtos.*`
- `vendas.*`
- `pedidos.*`

## Observação
A correção foi aplicada no nível do controller para reduzir dependência da ordem de carregamento de arquivos de rota legados.
