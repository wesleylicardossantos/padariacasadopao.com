# Correção reaplicada do log (21/04/2026)

## Ajustes executados

### 1. RepresentanteController
- Removido fluxo inseguro no `store()` que fazia `merge()` com criação de usuário dentro do array.
- Criação do usuário passou a ocorrer de forma direta e consistente antes da criação do representante.

### 2. FinanceiroController
- Corrigido filtro por status/estado para não aplicar filtro quando o valor for `todos`.
- Adicionada compatibilidade com bases que possuem a coluna `status` ou `estado` na tabela `payments`.

### 3. CompraFiscalController
- Blindada a importação de XML para os cenários:
  - arquivo não enviado
  - XML inválido/corrompido
  - ausência de `infNFe`
  - ausência da chave no atributo `Id`
- Tratamento com mensagem amigável + log técnico.

### 4. resources/views/dfe/show.blade.php
- Adicionadas inicializações defensivas para variáveis opcionais.
- Corrigido fechamento condicional inconsistente (`@endisset` -> `@endif`).

## Validação
- `php -l` executado nos arquivos alterados sem erro.
