# SEFAZ MA homologação

Esta rodada aplicou correções reais na camada fiscal já existente do projeto.

## O que foi ajustado
- correção do carregamento de certificado A1 com OpenSSL legacy
- correção de bug de recursão infinita em `NFCeService`
- padronização do carregamento de certificado em `NFService`, `NFCeService`, `NFeRemessaService`, `NFeEntradaService`, `DevolucaoService` e `DFeService`
- novo comando `php artisan sefaz:diagnostico {empresa_id}`

## Dados recebidos para esta rodada
- UF: MA
- ambiente: homologação
- documentos: NFe e NFCe
- CSC ID informado pelo usuário: `000001`
- certificado A1 validado localmente em modo legacy OpenSSL

## Importante
O projeto já usa `ConfigNota->arquivo`, `ConfigNota->senha`, `ConfigNota->csc` e `ConfigNota->csc_id` nos fluxos atuais.

Portanto, para emissão real:
1. confirme na tela de configuração fiscal que o certificado A1 está salvo em `ConfigNota`
2. confirme que `csc` e `csc_id` estão preenchidos
3. rode `php artisan sefaz:diagnostico EMPRESA_ID`

## Limite desta entrega
Sem o arquivo/rota/tela exato de emissão a preservar, esta rodada foca em endurecer a infraestrutura SEFAZ já existente do sistema, em vez de redesenhar o fluxo HTTP inteiro.
