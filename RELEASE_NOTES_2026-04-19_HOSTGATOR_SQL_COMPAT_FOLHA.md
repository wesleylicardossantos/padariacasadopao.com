# Ajuste HostGator - patch SQL compatível para motor de folha

## O que foi adicionado
- arquivo `database/sql/hostgator/2026_04_19_patch_motor_folha_hostgator_compat.sql`
- patch compatível com phpMyAdmin / HostGator / MySQL antigos
- sem uso de `ADD COLUMN IF NOT EXISTS`
- sincronização de `referencia` e `tipo_calculo`
- criação segura dos eventos padrão: HORA EXTRA 50, HORA EXTRA 100, FALTA e DSR HE

## Objetivo
Permitir aplicar o ajuste do motor de folha em hospedagem compartilhada, evitando o erro de sintaxe mostrado no phpMyAdmin.

## Como usar
1. Fazer backup do banco.
2. Abrir o phpMyAdmin da HostGator.
3. Importar o arquivo:
   - `database/sql/hostgator/2026_04_19_patch_motor_folha_hostgator_compat.sql`
4. Confirmar a criação das colunas:
   - `evento_salarios.referencia`
   - `evento_salarios.tipo_calculo`
   - `funcionario_eventos.referencia`
   - `funcionario_eventos.tipo_calculo`

## Observação técnica
Se o ambiente precisar armazenar referência textual como `220h`, `12 dias` ou `%`, há um bloco opcional no final do SQL para converter `referencia` de decimal para varchar.
