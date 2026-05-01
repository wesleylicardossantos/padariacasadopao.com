# Fase 2 Enterprise

## O que foi adicionado
- rotina `rh:competencia-enterprise` para processar competência com financeiro, holerites e WhatsApp
- hardening do portal com bloqueio temporário por tentativas inválidas
- dashboard executivo `/enterprise-center`
- API incremental `GET /api/v1/portal/overview`
- logs em `integracao_logs` e `portal_audit_logs` quando as tabelas estiverem disponíveis

## Comando principal
```bash
php artisan rh:competencia-enterprise EMPRESA_ID MES ANO --sync-financeiro --enviar-email --enviar-whatsapp
```

## ENV recomendado
```env
RH_PORTAL_MAX_TENTATIVAS=5
RH_PORTAL_BLOQUEIO_MINUTOS=15
RH_WHATSAPP_PROVIDER=generic
RH_WHATSAPP_API_URL=
RH_WHATSAPP_API_TOKEN=
RH_WHATSAPP_TIMEOUT=20
```
