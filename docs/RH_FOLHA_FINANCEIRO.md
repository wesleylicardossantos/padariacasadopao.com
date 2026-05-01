# Integração Folha x Financeiro

## O que foi implementado
- geração automática da apuração mensal com opção para já criar contas a pagar no financeiro;
- sincronização manual de uma competência já existente com o financeiro;
- criação automática da categoria `Folha de Pagamento` em `categoria_contas` quando necessário;
- atualização da conta a pagar vinculada à apuração quando ela ainda não foi paga.

## Fluxo
1. RH > Apuração Mensal
2. Informar mês/ano
3. Opcionalmente informar o vencimento da folha
4. Marcar `Gerar contas a pagar`
5. Clicar em `Gerar automática`

Também é possível usar o botão `Integrar folha com financeiro` para competências já geradas.

## Regras
- cada apuração mensal gera uma conta a pagar individual por funcionário;
- se a conta vinculada já possuir pagamento, ela não é sobrescrita;
- a referência gerada no financeiro segue o padrão `Folha MES/ANO - Nome do Funcionário`.
