# Fase 3 Enterprise - Z-API + PDF + Portal API

## Entregas
- Geração persistente do PDF do holerite em `storage/app/public/holerites/...`
- Assinatura digital simples no rodapé do PDF
- Envio de documento por WhatsApp via provider configurado
- Fluxo otimizado para **Z-API** com endpoints separados para texto e documento
- Comando de disparo manual por funcionário
- API incremental para listagem de holerites e URL do PDF

## ENV recomendado para Z-API
```env
APP_URL=https://SEU-DOMINIO
RH_WHATSAPP_PROVIDER=zapi
RH_WHATSAPP_API_URL=https://api.z-api.io/instances/SUA_INSTANCIA/token/SEU_TOKEN
RH_WHATSAPP_API_TOKEN=SEU_TOKEN
RH_WHATSAPP_ZAPI_CLIENT_TOKEN=SEU_CLIENT_TOKEN
RH_WHATSAPP_ZAPI_TEXT_PATH=/send-text
RH_WHATSAPP_ZAPI_DOCUMENT_PATH=/send-document
RH_WHATSAPP_TIMEOUT=20
```

## Importante
- Gere o link público dos PDFs com `php artisan storage:link`
- O `APP_URL` precisa apontar para o domínio público do sistema para o Z-API conseguir baixar o PDF
- Em hospedagens compartilhadas, valide se o diretório `storage/app/public` está gravável

## Comandos
```bash
php artisan rh:competencia-enterprise 1 3 2026 --sync-financeiro --enviar-email --enviar-whatsapp
php artisan rh:enviar-holerite-whatsapp-zapi 1 10 3 2026
```

## API
- `GET /api/v1/portal/holerites?empresa_id=1&funcionario_id=10`
- `GET /api/v1/portal/holerites/{apuracaoId}/pdf-url`
