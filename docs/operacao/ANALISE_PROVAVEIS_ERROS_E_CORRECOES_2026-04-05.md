# Análise técnica estática — prováveis erros remanescentes e correções aplicadas

## Correção aplicada nesta entrega

Foi corrigido o erro persistente entre projeto e banco no fluxo de **conta a receber / conta a pagar** causado por `filial_id` inválido para a empresa atual.

### Causa encontrada
- O código novo do financeiro aceitava `filial_id` vindo do request/sessão sem validar se a filial existia na tabela `filials` e se pertencia à `empresa_id` corrente.
- O dump analisado não contém inserts para `filials`, então qualquer `filial_id` legado vindo da sessão podia estourar FK em `conta_recebers` e `conta_pagars`.
- A tabela `conta_recebers` permite `filial_id` nulo, mas o código estava propagando um id inválido.

### Correção aplicada
- Criado `App\\Support\\Tenancy\\ScopedFilialResolver`.
- `RegisterFinancialEntryData` agora resolve a filial de forma segura por empresa.
- `UpdateReceivableUseCase` e `UpdatePayableUseCase` agora normalizam e validam `filial_id` pela empresa antes de persistir.
- Quando não existe filial válida para a empresa, o fluxo cai para `null` em vez de provocar violação de chave estrangeira.

## Achados estáticos de risco provável no projeto

### 1. Drift real entre código, dump e base operacional
- O projeto contém migrations e patches SQL que não batem integralmente com o dump incluído.
- Há comentários de compatibilidade em migrations antigas e patches SQL paralelos em `database/sql/`.
- Risco: deploy aplicar regras esperadas pelo código em bases ainda não alinhadas.

### 2. Tabela `filials` sem carga no dump analisado
- No dump fornecido não há `INSERT INTO filials`.
- Risco: qualquer fluxo que trate filial como obrigatória em runtime pode falhar com FK ou filtros vazios.

### 3. Contexto multiempresa ainda híbrido
- O projeto continua misturando `TenantContext`, request attributes e `session('user_logged')`.
- Risco: leitura/escrita usando empresa/filial errada em fluxos legados.

### 4. Campos NOT NULL com comportamento legado de string vazia
- Exemplo já confirmado: `conta_recebers.observacao` e `tipo_pagamento` são `NOT NULL`, mas fluxos novos tendem a converter vazio para `null` se não forem tratados.
- Risco semelhante em outros módulos com colunas legadas rígidas.

### 5. Escritas paralelas em domínios críticos
- O projeto mostra coexistência de UseCases novos e controllers/helpers legados.
- Risco: auditoria incompleta, cache inconsistente e divergência entre fluxos.

### 6. Alto volume de acoplamento implícito
- Uso legado de sessão e helpers globais ainda é relevante.
- Risco: comportamento diferente entre homologação, job, HTTP e CLI.

### 7. Views e layout ainda sensíveis a regressão estrutural
- Já houve quebra de renderização por hierarquia do layout.
- Risco: telas carregarem shell/base sem conteúdo útil quando controller/view/layout ficarem desalinhados.

### 8. Busca e filtros dependentes de naming heterogêneo
- O banco usa nomes como `descricao` em `filials`, enquanto consultas manuais e integrações tendem a assumir `nome`.
- Tabelas não seguem padrão uniforme entre domínios.
- Risco: relatórios, telas auxiliares e queries ad hoc falharem por naming inconsistente.

### 9. Dump e código indicam evolução por patches incrementais
- Há vários artefatos `.sql`, comandos de auditoria e arquivos de runtime alignment.
- Risco: correções locais existirem em um ambiente e não em outro.

### 10. Cobertura de testes ainda curta para o tamanho do ERP
- Mesmo com testes presentes, a superfície funcional é muito maior que a cobertura automatizada.
- Risco: regressão silenciosa em fluxos comerciais/fiscais/estoque.

## Prioridade objetiva recomendada
1. Validar produção com `schema:drift-report`, `system:healthcheck` e reconcile.
2. Consolidar tenant/filial por contexto explícito nos fluxos de escrita.
3. Revisar colunas `NOT NULL` legadas em create/update novos.
4. Auditar hotspots de escrita direta em estoque e comercial.
5. Fechar gaps de testes mínimos nos fluxos de conta, venda e estoque.
