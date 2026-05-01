# Release Notes — Controle de Salgados SaaS

## Entrega
Implementado o módulo completo **Controle de Salgados** integrado ao projeto Laravel monolítico com foco em operação SaaS multiempresa.

## O que foi entregue
- Menu lateral em **Controle > Controle de Salgados**
- Rotas completas de listagem, criação, edição, exclusão, visualização e PDF
- Persistência em banco com `empresa_id`
- Migration Laravel robusta
- SQL manual para implantação direta
- Models:
  - `ControleSalgado`
  - `ControleSalgadoItem`
- Controller:
  - `ControleSalgadoController`
- Views:
  - listagem
  - create
  - edit
  - show
  - PDF no layout da folha operacional enviada

## Estrutura funcional
Cada lançamento possui:
- cabeçalho com data, dia e observações
- linhas de produção para **MANHÃ**
- linhas de produção para **TARDE**
- campos por item:
  - QTD
  - DESCRIÇÃO
  - TERMINO
  - SALDO

## Arquivos principais
- `app/Http/Controllers/ControleSalgadoController.php`
- `app/Models/ControleSalgado.php`
- `app/Models/ControleSalgadoItem.php`
- `resources/views/controle_salgados/*`
- `database/migrations/2026_04_07_160000_create_controle_salgados_table.php`
- `database/migrations/2026_04_07_160100_create_controle_salgado_itens_table.php`
- `database/sql/2026_04_07_controle_salgados.sql`

## Observações técnicas
- Escopo por empresa aplicado no controller usando `TenantContext`
- Gravação transacional com `DB::beginTransaction()`
- PDF gerado com `Dompdf`
- Layout do PDF espelha a folha operacional da imagem enviada
