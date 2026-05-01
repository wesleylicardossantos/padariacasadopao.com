# Release Notes - Dashboard RH Executivo Visual

## Ajustes aplicados
- menu RH com apenas um item de dashboard: **Dashboard RH Executivo**
- correção do item ativo para o dashboard raiz `/rh`, evitando ficar marcado em todas as telas do RH
- rotas legadas de dashboards redirecionadas para o painel unificado:
  - `/rh/dashboard-v4`
  - `/rh/dashboard-v5`
  - `/rh/dashboard-executivo`
  - `/rh/dashboard-premium`
  - `/rh/painel-dono`
- melhoria visual do dashboard unificado:
  - hero principal refinado
  - resumo executivo no topo
  - título atualizado para **Dashboard RH Executivo**
  - remoção do atalho `RH V5` da hero
  - textos e seções ajustados para leitura mais executiva

## Arquivos alterados
- `app/Helpers/Menu.php`
- `resources/views/default/menu.blade.php`
- `app/Modules/RH/Routes/web.php`
- `resources/views/rh/dashboard.blade.php`
