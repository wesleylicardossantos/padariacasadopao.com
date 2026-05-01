# RH Controller Refactor

## O que foi refatorado
- `FuncionarioController`
- `FuncionarioEventoController`
- `ApuracaoMensalController`
- `EventoSalarioController`
- `RHFeriasController`
- `RHFaltaController`
- `RHMovimentacaoController`

## Novas peças criadas
- `app/Modules/RH/Http/Controllers/Concerns/InteractsWithRH.php`
- `app/Modules/RH/Application/Funcionario/FuncionarioService.php`
- `app/Modules/RH/Application/FuncionarioEvento/FuncionarioEventoService.php`
- `app/Modules/RH/Application/ApuracaoMensal/ApuracaoMensalService.php`

## Ganhos
- tenant (`empresa_id`) centralizado
- controllers menores e com menos regra de negócio
- queries com filtro de empresa consistente
- RH férias saiu de SQL solto para model Eloquent
- eventos/apuração passaram a usar serviços de aplicação
- correção de precedence bug em `RHContext::empresaId()`

## Validação recomendada
- RH > Funcionários
- RH > Eventos
- RH > Funcionários x Eventos
- RH > Apuração Mensal
- RH > Férias
- RH > Faltas
- RH > Movimentações
