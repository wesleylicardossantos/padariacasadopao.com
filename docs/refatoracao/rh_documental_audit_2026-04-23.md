# Auditoria documental RH

- Gerado em: 2026-04-23 11:33:24

## Rotas auditadas

- rh.dossie.show | existe=sim
- rh.dossie.documentos.store | existe=sim
- rh.dossie.documentos.download | existe=sim
- rh.portal_externo.documentos_rescisao | existe=sim
- rh.portal_externo.documentos_rescisao.trct.pdf | existe=sim
- rh.portal_externo.documentos_rescisao.tqrct.pdf | existe=sim
- rh.portal_externo.documentos_rescisao.homologacao.pdf | existe=sim
- rh.portal_funcionario.pdf | existe=sim

## Tabelas auditadas

- rh_dossies | existe=nao
  - schema_error=could not find driver (Connection: mysql, SQL: select * from information_schema.tables where table_schema = wesl4494_db_saas and table_name = rh_dossies and table_type = 'BASE TABLE')
  - empresa_id=nao
  - funcionario_id=nao
  - status=nao
  - ultima_atualizacao_em=nao
- rh_dossie_eventos | existe=nao
  - schema_error=could not find driver (Connection: mysql, SQL: select * from information_schema.tables where table_schema = wesl4494_db_saas and table_name = rh_dossie_eventos and table_type = 'BASE TABLE')
  - empresa_id=nao
  - funcionario_id=nao
  - categoria=nao
  - titulo=nao
  - data_evento=nao
  - visibilidade_portal=nao
- rh_documentos | existe=nao
  - schema_error=could not find driver (Connection: mysql, SQL: select * from information_schema.tables where table_schema = wesl4494_db_saas and table_name = rh_documentos and table_type = 'BASE TABLE')
  - empresa_id=nao
  - funcionario_id=nao
  - tipo=nao
  - categoria=nao
  - arquivo=nao
  - origem=nao
  - status=nao
  - hash_conteudo=nao
- rh_rescisoes | existe=nao
  - schema_error=could not find driver (Connection: mysql, SQL: select * from information_schema.tables where table_schema = wesl4494_db_saas and table_name = rh_rescisoes and table_type = 'BASE TABLE')
  - empresa_id=nao
  - funcionario_id=nao
  - data_rescisao=nao
  - total_liquido=nao
  - status=nao
