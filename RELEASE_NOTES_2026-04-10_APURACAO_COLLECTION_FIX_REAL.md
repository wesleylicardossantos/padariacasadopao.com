# Correção real da apuração automática

- sanitização defensiva das linhas de eventos da folha
- normalização de evento_id, referência, valor e textos antes de gravar
- ordenação protegida contra Collection em evento_id
- redução do risco de erro `Illuminate\Database\Eloquent\Collection could not be converted to int`
