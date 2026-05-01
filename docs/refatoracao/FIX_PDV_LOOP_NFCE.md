# Fix PDV - loop infinito ao emitir NFC-e

## Causa
O método `readCertificate()` em `app/Services/NFCeService.php` entrava em recursão infinita.

## Correção aplicada
- leitura real do certificado via `Certificate::readPfx(...)`
- suporte a `openssl_legacy.cnf`
- melhoria de tratamento de erro no `public/js/frontBox.js`
- reset de `emitirNfce` nos fluxos de falha para evitar spinner preso
