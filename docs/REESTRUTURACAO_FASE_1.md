# Reestruturação - Fase 1

## Objetivo desta fase
Iniciar a reorganização do projeto sem quebrar o funcionamento atual.

## O que foi feito

### 1. Centralização do carregamento de rotas
Foi criado o arquivo `app/Support/Routing/RouteFileRegistry.php` para controlar a ordem de carregamento das rotas web.

Ordem aplicada:
1. `routes/web.php`
2. `routes/modules/**`
3. `routes/patches/**`
4. `routes/legacy/**`

### 2. Reorganização física dos arquivos de rota
Os arquivos foram separados por responsabilidade:

- `routes/modules/rh/` → rotas do RH
- `routes/patches/web/` → patches web
- `routes/patches/admin/` → patches administrativos
- `routes/legacy/` → arquivos antigos/legados

### 3. RouteServiceProvider preparado para estrutura modular
O provider agora carrega automaticamente todos os arquivos `.php` dentro de `routes/`, respeitando prioridade.

## Benefícios imediatos
- elimina dependência de inclusão manual de vários arquivos de rota
- reduz risco de rota esquecida e erro 404 por arquivo não carregado
- facilita localizar rotas por módulo
- cria base para modularização progressiva

## Próximos passos recomendados
1. mapear rotas duplicadas entre `web`, `patches` e `legacy`
2. consolidar RH em um único arquivo por submódulo
3. extrair regras de negócio críticas para `Services`
4. iniciar migração controlada para `app/Modules/`
