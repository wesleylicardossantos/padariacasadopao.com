# Status real da wave Financeiro/Fiscal 2026-04-10

- Consolidação de TenantContext em FinanceiroController, NfseController, ContigenciaController, BoletoController e RemessaBoletoController.
- Adição do middleware tenant.context nas rotas web críticas de financeiro e fiscal.
- Blindagem de lookups por ID para impedir acesso cross-tenant em NFSe, remessas, boletos e contingência.
- Correção do FinanceiroController::list para filtrar pagamentos pela empresa atual.
- Criação do comando refactor:financeiro-fiscal-tenant-audit para auditoria contínua da wave.
