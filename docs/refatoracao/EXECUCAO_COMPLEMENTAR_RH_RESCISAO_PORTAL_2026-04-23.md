# Execução complementar RH / Rescisão / Portal - 2026-04-23

## Escopo executado
- endurecimento do contexto de empresa para sessões do portal externo
- autorização e escopo seguro dos PDFs de rescisão no portal do funcionário
- travas de consistência no registro de desligamento
- limpeza defensiva dos itens da rescisão ao excluir desligamento
- migration corretiva complementar
- SQL manual seguro para Hostgator

## Arquivos alterados
- `app/Modules/RH/Support/RHContext.php`
- `app/Http/Controllers/RHDocumentoGeradoController.php`
- `app/Http/Controllers/RHDesligamentoController.php`
- `database/migrations/2026_04_23_000100_harden_rh_rescisao_portal_and_context.php`
- `database/sql/2026_04_23_rh_rescisao_portal_context_hostgator_safe.sql`

## Resultado técnico
1. O portal externo agora resolve `empresa_id` também pela sessão `funcionario_portal`, reduzindo falha de contexto em documentos de rescisão.
2. PDFs de rescisão acessados pelo portal externo passam a respeitar o `funcionario_id` da sessão, evitando vazamento cruzado de documentos.
3. O cadastro de desligamento passou a exigir funcionário da própria empresa e bloqueia duplicidade na mesma data.
4. A exclusão de desligamento remove também os itens vinculados da rescisão antes de apagar o cabeçalho.

## Validação aplicada
- lint dos arquivos alterados
- route:list completo
- auditoria rápida das rotas RH/portal
