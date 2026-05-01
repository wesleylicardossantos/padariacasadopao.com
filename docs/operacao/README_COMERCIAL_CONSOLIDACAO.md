# Comercial — Consolidação segura

Esta etapa adiciona uma camada enterprise controlada para o domínio Comercial, sem quebrar telas e rotas legadas.

## Entradas adicionadas
- `POST /enterprise/comercial/customers`
- `POST /enterprise/comercial/sales`
- `POST /enterprise/comercial/orders`
- `POST /enterprise/comercial/budgets`
- `GET /enterprise/comercial/portfolio`
- `GET /enterprise/comercial/snapshot`
- `GET /enterprise/comercial/kpis`

## Regras desta etapa
- Não altera contratos legados existentes.
- Toda escrita nova passa por transaction e auditoria comercial.
- Escopo por `empresa_id` é obrigatório via middleware/tenant context.
- Fluxos fiscais continuam fora desta etapa.

## Próximo passo após estabilização
- Validar uso real em homologação.
- Mapear pontos de integração com estoque/financeiro que ainda dependem do legado.
- Só depois iniciar extração segura do fluxo comercial mais crítico que estiver em produção.
