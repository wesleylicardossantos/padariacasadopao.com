# Reestruturação RH - Fase 4

## Entregas
- migração modular do dashboard executivo RH
- criação das rotas modulares de holerite e resumo financeiro da folha
- controllers legados convertidos em camada de compatibilidade
- centralização adicional da lógica de folha no serviço modular

## Rotas novas/modulares
- `/rh/dashboard-executivo`
- `/rh/folha/resumo-financeiro`
- `/rh/holerite/{id}`

## Observação
As views foram preservadas para reduzir risco de quebra, mas agora os dados saem do módulo `app/Modules/RH`.
