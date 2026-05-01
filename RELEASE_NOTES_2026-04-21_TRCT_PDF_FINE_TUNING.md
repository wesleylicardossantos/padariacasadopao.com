# RELEASE NOTES — AJUSTE FINO TRCT / TQRCT / HOMOLOGAÇÃO PDF

## Objetivo
Substituir o layout simplificado de rescisão por um documento técnico ajustado para saída A4 com DOMPDF e compatibilidade opcional com Snappy PDF, mantendo preview HTML e rotas dedicadas para PDF inline.

## Implementado
- Novo builder de dados para TRCT/TQRCT/Homologação.
- Novo renderer PDF com fallback automático para DOMPDF.
- Novas rotas PDF dedicadas.
- Preview HTML com iframe do PDF gerado.
- Botões das telas de rescisão e portal apontando para PDF inline.
- Layout ajustado para ficar praticamente colado ao modelo impresso enviado.

## Driver de PDF
- DOMPDF: ativo e pronto no projeto.
- Snappy: compatível no código; será usado automaticamente quando o pacote/binding existir no ambiente.

## Banco / SQL
- Nenhuma migration nova necessária para esta etapa.
- Nenhum SQL estrutural necessário.
