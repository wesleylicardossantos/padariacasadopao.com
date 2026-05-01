# Wave 3 — PDV bridge sem quebra

Esta onda avança a refatoração do PDV legado sem trocar contratos externos.

## Objetivo

Mover a lógica de borda dos controllers legados de PDV para uma camada bridge reaproveitando os services já existentes no módulo `App\Modules\PDV`.

## Alterações incluídas

- `app/Http/Controllers/Pdv/OfflineBootstrapController.php`
  - agora delega para `LegacyOfflineBootstrapBridgeService`
- `app/Http/Controllers/Pdv/OfflineVendaSyncController.php`
  - agora delega para `LegacyOfflineSyncBridgeService`
- `app/Http/Controllers/Pdv/OfflineSyncMonitorController.php`
  - agora delega para `LegacyOfflineSyncMonitorBridgeService`
- `app/Modules/PDV/Data/SyncMonitorFilterData.php`
  - centraliza filtros do monitor de sincronização
- `app/Modules/PDV/Services/LegacyBridge/*`
  - nova camada de compatibilidade para borda legacy → módulo
- `tests/Feature/Architecture/PdvBridgeControllersTest.php`
  - garante dependência dos controllers nos bridge services

## O que foi preservado

- rotas existentes
- payloads JSON existentes
- view `pdv_offline.monitor`
- redirects do monitor
- serviços centrais do módulo PDV

## Limites desta onda

- não reescreve o motor transacional de sincronização
- não altera schema do banco
- não substitui toda a superfície do PDV legado

## Próxima onda sugerida

- extração de login/config/caixa do PDV legado
- testes de regressão para sync offline e monitor
- endurecimento de idempotência e observabilidade
