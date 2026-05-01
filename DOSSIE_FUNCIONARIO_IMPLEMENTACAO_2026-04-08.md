# Implementação do módulo Dossiê de Funcionário — 2026-04-08

## Entregue nesta versão
- Tela consolidada de dossiê RH por funcionário
- Timeline unificada com:
  - admissão
  - movimentações
  - férias
  - faltas
  - documentos
  - folha/apurações
  - desligamentos
  - eventos manuais do dossiê
- Upload de documentos diretamente no dossiê
- Download de documentos anexados
- Registro manual de eventos do dossiê
- Relações no model `Funcionario` para dossiê, dependentes e documentos RH

## Arquivos principais adicionados
- `app/Models/RHDossie.php`
- `app/Models/RHDossieEvento.php`
- `app/Models/RHDocumento.php`
- `app/Models/FuncionarioDependente.php`
- `database/migrations/2026_04_08_230000_create_rh_dossies_table.php`
- `database/migrations/2026_04_08_230100_create_rh_dossie_eventos_table.php`
- `database/migrations/2026_04_08_230200_extend_rh_documentos_for_dossie_module.php`

## Arquivos alterados
- `app/Http/Controllers/RHDossieController.php`
- `resources/views/rh/dossie/show.blade.php`
- `app/Modules/RH/Routes/web.php`
- `app/Models/Funcionario.php`

## Passos de publicação
1. Fazer backup do banco
2. Rodar as migrations
3. Garantir permissão de escrita em `public/uploads/rh_documentos`
4. Validar acesso à rota `/rh/dossie/{id}`

## Observação
A implementação foi integrada respeitando a base já existente do projeto, reaproveitando tabelas RH como `rh_documentos`, `rh_movimentacoes`, `rh_ferias`, `rh_faltas`, `rh_desligamentos` e `apuracao_mensals`.
