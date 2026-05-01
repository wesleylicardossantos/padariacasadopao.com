# Correção layout nativo do sistema

Problema: as telas SaaS estavam usando `layouts.app`, layout genérico criado apenas para evitar erro de view, por isso ficavam fora do padrão visual do ERP.

Correção aplicada: as telas SaaS agora usam `default.layout`, o layout real do sistema, preservando sidebar, topo, assets, menu, tema, Bootstrap e padrão visual existente.

Arquivos alterados:
- resources/views/enterprise/saas/executive.blade.php
- resources/views/enterprise/saas/scale.blade.php
- resources/views/enterprise/saas/observability.blade.php

Não há SQL obrigatório nesta correção.
Após aplicar, limpar cache de views: `php artisan view:clear` ou remover os arquivos de `storage/framework/views`.
