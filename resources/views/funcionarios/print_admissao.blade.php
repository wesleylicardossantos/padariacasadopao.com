<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <title>Ficha Cadastral - Admissão</title>
  <style>
    @page { margin: 18mm 14mm; }
    body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; font-size: 11px; color:#000; }
    .title{ text-align:center; font-weight:700; font-size:14px; margin-bottom:14px; letter-spacing:.3px; }
    .row{ width:100%; clear:both; margin:7px 0; }
    .label{ display:inline-block; font-weight:700; }
    .field{ display:inline-block; border-bottom:1px solid #000; height:14px; vertical-align:bottom; }
    .w-10{width:10%;} .w-12{width:12%;} .w-15{width:15%;} .w-20{width:20%;} .w-25{width:25%;}
    .w-30{width:30%;} .w-35{width:35%;} .w-40{width:40%;} .w-45{width:45%;} .w-50{width:50%;}
    .w-55{width:55%;} .w-60{width:60%;} .w-70{width:70%;} .w-75{width:75%;} .w-80{width:80%;}
    .w-90{width:90%;}
    .mr-6{margin-right:6px;} .mr-10{margin-right:10px;}
    .small{font-size:10px;} .center{text-align:center;}
    .section-note{ margin-top:10px; font-weight:700; text-align:center; }
    .muted{ font-weight:400; }
    .checkbox{ display:inline-block; width:10px; height:10px; border:1px solid #000; margin:0 4px -1px 6px; }
  </style>
</head>
<body>
  <div class="title">FICHA CADASTRAL – ADMISSÃO</div>

  @php
    // Aceita $funcionario (recomendado) e mantém compatibilidade com $item
    $f = $funcionario ?? $item ?? null;

    $nome = $f->nome ?? '';
    $cpf  = $f->cpf ?? '';
    $rg   = $f->rg ?? '';
    $cel  = $f->celular ?? ($f->cel ?? '');
    $tel  = $f->telefone ?? '';
    $sal  = isset($f->salario) ? number_format((float)$f->salario, 2, ',', '.') : '';
    $cargo = $f->funcao ?? ''; // Função do sistema = Cargo no formulário

    $rua = $f->rua ?? ($f->endereco ?? '');
    $numero = $f->numero ?? '';
    $bairro = $f->bairro ?? '';
    $cep = $f->cep ?? '';
    $cidade = $f->cidade ?? '';
    $comp = $f->complemento ?? '';
  @endphp

  <div class="row"><span class="label mr-6">NOME:</span><span class="field w-90">{{ $nome }}</span></div>

  <div class="row">
    <span class="label mr-6">ENDEREÇO:</span><span class="field w-70 mr-10">{{ $rua }}</span>
    <span class="label mr-6">N.º</span><span class="field w-15">{{ $numero }}</span>
  </div>

  <div class="row">
    <span class="label mr-6">COMPLEMENTO:</span><span class="field w-35 mr-10">{{ $comp }}</span>
    <span class="label mr-6">CEP:</span><span class="field w-20 mr-10">{{ $cep }}</span>
    <span class="label mr-6">BAIRRO:</span><span class="field w-25">{{ $bairro }}</span>
  </div>

  <div class="row">
    <span class="label mr-6">CIDADE:</span><span class="field w-40 mr-10">{{ $cidade }}</span>
    <span class="label mr-6">TELEFONE:</span><span class="field w-20 mr-10">{{ $tel }}</span>
    <span class="label mr-6">CEL:</span><span class="field w-20">{{ $cel }}</span>
  </div>

  <div class="row"><span class="label mr-6">NOME DO PAI:</span><span class="field w-80"></span></div>
  <div class="row"><span class="label mr-6">NOME DA MÃE:</span><span class="field w-80"></span></div>

  <div class="row">
    <span class="label mr-6">NATURALIDADE:</span><span class="field w-55 mr-10"></span>
    <span class="label mr-6">U.F.</span><span class="field w-30"></span>
  </div>

  <div class="row">
    <span class="label mr-6">DATA DE NASCIMENTO:</span><span class="field w-20 mr-10"></span>
    <span class="label mr-6">DEFIC. FISICO: SIM</span><span class="checkbox"></span>
    <span class="label mr-6">NÃO</span><span class="checkbox"></span>
  </div>

  <div class="row small">
    <span class="label mr-6">RAÇA/COR:</span>
    <span class="muted">( ) BRANCA</span> <span class="muted">( ) PRETA</span> <span class="muted">( ) PARDA</span> <span class="muted">( )</span>
    <span class="label mr-6">AMARELA</span>
    <span class="label mr-6" style="margin-left:10px;">SEXO:</span>
    <span class="muted">( ) MASCULINO</span> <span class="muted">( ) FEMININO</span>
  </div>

  <div class="row small">
    <span class="label mr-6">ESTADO CIVIL:</span>
    <span class="muted">( ) SOLTEIRO</span> <span class="muted">( ) CASADO</span> <span class="muted">( ) DIVORCIADO</span>
    <span class="muted">( ) SEPARADO</span> <span class="muted">( ) OUTROS</span> <span class="field w-20"></span>
  </div>

  <div class="row"><span class="label mr-6">GRAU INSTRUÇÃO:</span><span class="field w-70"></span></div>

  <div class="row">
    <span class="label mr-6">NUMERO RG:</span><span class="field w-25 mr-10">{{ $rg }}</span>
    <span class="label mr-6">ORGÃO EMISSOR</span><span class="field w-25 mr-10"></span>
    <span class="label mr-6">DATA EMISSÃO:</span><span class="field w-15"></span>
  </div>

  <div class="row"><span class="label mr-6">NUMERO CPF/MF:</span><span class="field w-70">{{ $cpf }}</span></div>

  <div class="section-note small">
    PARA PREENCHIMENTO EXCLUSIVO PELOS SÓCIOS DA EMPRESA<br>(OBRIGATÓRIO):
  </div>

  <div class="row">
    <span class="label mr-6">SALÁRIO R$</span><span class="field w-25 mr-10">{{ $sal }}</span>
    <span class="label mr-6">* CARGO</span><span class="field w-55">{{ $cargo }}</span>
  </div>

  <div class="row small center"><span class="label">FICHA PREENCHIDA POR:</span><span class="field w-60"></span></div>
  <div class="row small center"><span class="label">CASO HAJA QUALQUER OBSERVAÇÃO FAVOR PREENCHER NO VERSO</span></div>
</body>
</html>