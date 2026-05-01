# Hardening de produção fiscal

## Ajustes aplicados
- carregamento seguro de `openssl_legacy.cnf`
- resolução robusta do caminho do certificado
- leitura do PFX por conteúdo (`file_get_contents`)
- validação de existência e permissão do arquivo antes da emissão
- log estruturado de falha de leitura do certificado
- mensagens de erro legíveis para produção

## Arquivos alterados
- app/Services/NFService.php
- app/Services/NFCeService.php
- openssl_legacy.cnf
