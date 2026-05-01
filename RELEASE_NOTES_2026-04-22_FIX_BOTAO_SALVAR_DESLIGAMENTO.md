# Fix botão salvar desligamento (erro 500)

## Causa raiz identificada
O fluxo de gravação da rescisão tentava inserir colunas novas em `rh_rescisoes` que não existem no banco atual em produção.

Erro encontrado no log:
- coluna `observacoes` inexistente; no banco atual a coluna é `observacao`
- a tabela atual também pode não ter `documentos_json`, `competencia` e `processado_em`

## Ajustes aplicados
- compatibilização do payload de `RHRescisaoService` com o schema real da tabela
- fallback entre `observacao` e `observacoes`
- filtro por colunas existentes antes do `create()` em `rh_rescisoes`
- filtro por schema também nos itens `rh_rescisao_itens`
- tratamento de exceção no `store()` do desligamento com log e retorno para a tela sem erro 500

## Arquivos alterados
- app/Services/RHRescisaoService.php
- app/Models/RHRescisao.php
- app/Http/Controllers/RHDesligamentoController.php
