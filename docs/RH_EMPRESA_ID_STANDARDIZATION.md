# Padronização RH com empresa_id

## O que este pacote faz
- adiciona a coluna `empresa_id` nas tabelas RH que ainda não a possuem
- faz backfill a partir das relações já existentes com `funcionarios`, `eventos`, `evento_salarios` e `apuracao_mensals`
- cria índices compostos para consultas por empresa + funcionário

## Migration criada
- `database/migrations/2026_03_27_000001_padroniza_rh_empresa_id.php`

## Tabelas cobertas
- `apuracao_mensals`
- `apuracao_salario_eventos`
- `funcionario_eventos`
- `evento_funcionarios`
- `atividade_eventos`
- `funcionario_os`
- `contato_funcionarios`
- `funcionarios_dependentes`
- `funcionarios_ficha_admissao`
- `rh_ferias`
- `rh_faltas`
- `rh_desligamentos`
- `rh_movimentacoes`
- `rh_ocorrencias` (se existir)
- `rh_documentos` (se existir)

## Como aplicar
```bash
php artisan migrate
php artisan route:clear
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

## Observações
- a migration é segura para tabelas inexistentes: ela ignora o que não estiver no banco
- o rollback foi omitido de propósito para evitar perda de dados após o backfill
- se algum registro ficar sem `empresa_id`, ele não tinha relação consistente suficiente para inferência automática e deve ser corrigido manualmente
