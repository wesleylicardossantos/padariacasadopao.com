# Execução complementar RH documental 2026-04-23

## Escopo executado
- rotas próprias do portal externo para PDFs de rescisão
- endurecimento da detecção de contexto do portal no controller documental
- integração automática de holerites com dossiê e timeline RH
- migration corretiva complementar para colunas documentais
- SQL manual seguro para Hostgator
- auditoria contínua da camada documental RH

## Arquivos alterados
- `routes/web.php`
- `routes/legacy/web1.php`
- `routes/legacy/web2.php`
- `resources/views/rh/portal_funcionario/documentos_rescisao_externo.blade.php`
- `app/Http/Controllers/RHDocumentoGeradoController.php`
- `app/Services/RHHoleritePdfService.php`
- `app/Console/Kernel.php`
- `app/Console/Commands/RefactorRhDocumentalAuditCommand.php`
- `database/migrations/2026_04_23_010000_harden_rh_documental_layer.php`
- `database/sql/2026_04_23_rh_documental_layer_hostgator_safe.sql`

## Resultado funcional
1. O portal externo deixou de depender das rotas administrativas para emissão de TRCT/TQRCT/Homologação.
2. A geração de holerite agora persiste artefatos documentais no dossiê do colaborador quando as tabelas do RH estiverem disponíveis.
3. Eventos de holerite passam a ficar rastreáveis na timeline do dossiê com visibilidade de portal.
4. A camada documental ganhou auditoria dedicada para rotas, views e colunas críticas.
