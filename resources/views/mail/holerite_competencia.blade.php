<p>Olá, {{ $funcionario->nome ?? 'colaborador(a)' }}.</p>
<p>Segue em anexo o seu holerite da competência <strong>{{ str_pad($mes, 2, '0', STR_PAD_LEFT) }}/{{ $ano }}</strong>.</p>
<p>Em caso de dúvidas, procure o setor responsável pelo RH.</p>
<p>Mensagem automática do sistema.</p>
