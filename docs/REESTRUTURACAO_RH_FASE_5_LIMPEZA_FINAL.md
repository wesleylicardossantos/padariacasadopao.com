# Reestruturação RH - Fase 5 (limpeza final)

## O que foi feito
- todas as rotas RH foram centralizadas em `app/Modules/RH/Routes/web.php`
- o bloco RH legado foi removido de `routes/web.php`
- controladores restantes do RH passaram a ter equivalentes no módulo novo (`app/Modules/RH/Controllers`)
- os equivalentes novos usam herança dos controladores legados para manter a lógica atual com menor risco de regressão
- rotas avulsas de patches antigos do RH foram consolidadas no módulo

## Resultado prático
- uma única fonte de verdade para rotas RH
- menos duplicidade de nomes e caminhos
- base pronta para remover controladores legados aos poucos numa próxima fase

## Observação
Esta etapa limpa a superfície do módulo RH sem reescrever toda a lógica interna de cada tela. A lógica atual continua funcionando por compatibilidade, mas a entrada HTTP agora está centralizada no módulo novo.
