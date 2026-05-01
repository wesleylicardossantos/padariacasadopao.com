# Dossiê SaaS - exclusão de documentos e eventos

## Entrega
- coluna de ações em Documentos do colaborador
- lixeira por linha na Timeline do dossiê
- exclusão segura de documentos do dossiê
- exclusão de eventos do dossiê
- logs de auditoria em logger()
- sincronização automática do dossiê após exclusão

## Rotas novas
- DELETE /rh/dossie/{id}/documentos/{documentoId}
- DELETE /rh/dossie/{id}/eventos/{eventoId}

## Observação
A lixeira da timeline aparece para linhas que pertencem ao próprio módulo do dossiê (eventos do `rh_dossie_eventos` e documentos de `rh_documentos`).
