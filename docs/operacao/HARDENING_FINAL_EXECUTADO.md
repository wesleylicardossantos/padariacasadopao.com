# Hardening final executado

## Objetivo
Adicionar uma camada final de hardening sem redesenhar a arquitetura nem introduzir risco de quebra em produção.

## Alterações aplicadas

### 1. Security headers globais
Foi criado o middleware `App\Http\Middleware\SecurityHeaders` e registrado na stack global do HTTP Kernel.

Headers aplicados:
- `X-Content-Type-Options: nosniff`
- `X-Frame-Options: SAMEORIGIN`
- `Referrer-Policy: strict-origin-when-cross-origin`
- `Permissions-Policy` com superfície mínima
- `Content-Security-Policy-Report-Only` em modo observável, sem bloquear produção
- `Strict-Transport-Security` opcional via configuração, habilitado apenas quando explicitamente ligado

### 2. Configuração central em `config/hardening.php`
Foi adicionada a seção `security_headers` para controlar:
- X-Frame-Options
- Referrer-Policy
- Permissions-Policy
- CSP em modo report-only
- HSTS e seus parâmetros

### 3. Hardening final report enriquecido
O comando `php artisan hardening:final-report --write` agora também verifica:
- existência do middleware de security headers
- registro no Kernel
- estado das flags de security headers

## Motivo técnico
Esta camada final endurece a superfície HTTP sem risco alto de regressão funcional, porque:
- a CSP entra em `Report-Only`
- HSTS fica desligado por padrão
- headers escolhidos são compatíveis com aplicações Laravel legadas em hospedagem compartilhada

## Próxima validação operacional
Após deploy:
1. abrir o sistema logado e anônimo
2. validar uploads, iframes internos e impressão
3. confirmar no DevTools os headers da resposta
4. só depois considerar ativar HSTS em produção
