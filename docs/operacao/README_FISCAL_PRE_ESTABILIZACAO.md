# Fiscal - pré-estabilização por façade e adapters

Esta etapa segue o plano enterprise: **não reescrever cálculo ou XML**, apenas criar uma camada estável de preparo, transmissão, cancelamento e consulta.

## O que entrou
- `FiscalFacadeService`
- `LegacyFiscalGatewayInterface`
- `NullFiscalGateway`
- `FiscalAuditService`
- `FiscalOperationsReportCommand`
- requests tipados
- rotas enterprise em `enterprise/fiscal/*`
- tabelas `fiscal_documents` e `fiscal_audits`

## Fluxo recomendado
1. preparar documento
2. transmitir por facade estável
3. consultar status
4. cancelar quando necessário
5. acompanhar o relatório operacional fiscal

## Comandos
```bash
php artisan migrate --force
php artisan fiscal:operations-report 1 --write
```

## Regra de segurança
Nesta fase a integração externa permanece atrás de adapter simulável. A troca para gateway real deve ocorrer apenas depois da homologação do fluxo e da massa de testes.
