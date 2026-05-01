<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('rh_document_templates')) {
            return;
        }

        $payload = [
            'nome' => 'Contrato Individual de Trabalho',
            'slug' => 'contrato-trabalho-clt',
            'categoria' => 'contrato',
            'tipo_documento' => 'contrato_trabalho',
            'descricao' => 'Contrato profissional com cláusulas condicionais por tipo e regime.',
            'conteudo_html' => <<<'HTML'
<div class="contrato-rh">
<h1 class="titulo-documento">CONTRATO INDIVIDUAL DE TRABALHO</h1>
<p class="subtitulo-documento">({{tipo_contrato_label}}) · Regime {{regime_trabalho}}</p>
<p>Pelo presente instrumento particular, de um lado <strong>{{empresa_nome}}</strong>, {{empresa_tipo_pessoa}}, inscrita no CPF/CNPJ sob o n.º <strong>{{empresa_cnpj}}</strong>, com sede em <strong>{{empresa_endereco}}</strong>, neste ato representada por <strong>{{empresa_representante_legal}}</strong>, CPF <strong>{{empresa_representante_cpf}}</strong>, doravante denominada <strong>EMPREGADORA</strong>.</p>
<p>E de outro lado <strong>{{funcionario_nome}}</strong>, {{funcionario_nacionalidade}}, {{funcionario_estado_civil}}, {{funcionario_profissao}}, portador(a) do CPF inscrito sob o nº <strong>{{funcionario_cpf}}</strong>, RG <strong>{{funcionario_rg}}</strong>, CTPS <strong>{{funcionario_ctps}}</strong>, série <strong>{{funcionario_ctps_serie}}</strong>, residente e domiciliado(a) em <strong>{{funcionario_endereco}}</strong>, doravante denominado(a) <strong>EMPREGADO(A)</strong>.</p>
<p>As partes, de comum acordo, contratam o presente <strong>CONTRATO INDIVIDUAL DE TRABALHO</strong>, que será regido pela Consolidação das Leis do Trabalho – CLT e pelas cláusulas seguintes:</p>
<h2>CLÁUSULA PRIMEIRA – DO OBJETO</h2>
<p>1.1. O presente contrato tem por objeto a prestação de serviços pelo(a) <strong>EMPREGADO(A)</strong>, que integra o quadro funcional da EMPREGADORA.</p>
<h2>CLÁUSULA SEGUNDA – DA FUNÇÃO</h2>
<p>2.1. O(A) EMPREGADO(A) exercerá a função de <strong>{{funcionario_cargo}}</strong>, comprometendo-se a desempenhar as seguintes atividades: {{funcionario_atividades}}.</p>
<p>2.2. O(A) EMPREGADO(A) poderá ser designado(a) para outra função compatível com sua condição pessoal, observadas as disposições legais.</p>
<h2>CLÁUSULA TERCEIRA – DA REMUNERAÇÃO</h2>
<p>3.1. Pela prestação de serviços, a EMPREGADORA pagará ao(à) EMPREGADO(A) o salário de <strong>R$ {{funcionario_salario}}</strong>, a ser pago {{periodicidade_pagamento}}, sujeito aos descontos legais e a eventuais adiantamentos.</p>
<p>3.2. O pagamento será realizado por {{forma_pagamento_documento}}, até o 5º dia útil do mês subsequente ao vencido.</p>
<p>3.3. Dados bancários: Banco {{banco}}, Agência {{agencia}}, Conta Corrente {{conta_corrente}}.</p>
<p>3.4. Benefícios concedidos: {{beneficios_descricao}}</p>
<h2>CLÁUSULA QUARTA – DOS DESCONTOS</h2>
<p>4.1. O(A) EMPREGADO(A) {{autoriza_contribuicao_sindical}} autoriza a contribuição sindical quando legalmente cabível e autorizada, bem como os demais descontos previstos em lei, norma coletiva ou reparação de danos na forma do art. 462 da CLT.</p>
<h2>CLÁUSULA QUINTA – DA JORNADA DE TRABALHO</h2>
<p>5.1. A jornada de trabalho observará o seguinte regime: {{jornada_descricao}}.</p>
<p>5.2. Serão assegurados o descanso semanal remunerado, os intervalos legais e, quando houver horas extras, sua remuneração ou compensação na forma da lei.</p>
{{#presencial}}
<h2>CLÁUSULA SEXTA – DO LOCAL DE TRABALHO</h2>
<p>6.1. O(A) EMPREGADO(A) prestará serviços em <strong>{{local_trabalho}}</strong>, em regime <strong>{{regime_trabalho}}</strong>.</p>
<p>6.2. Qualquer alteração relevante de local ou de regime obedecerá à legislação aplicável e, quando necessário, será formalizada por aditivo contratual.</p>
{{/presencial}}
{{#teletrabalho}}
<h2>CLÁUSULA SEXTA – DO TELETRABALHO</h2>
<p>6.1. As atividades do(a) EMPREGADO(A) serão exercidas preponderantemente fora das dependências da EMPREGADORA, em regime de <strong>{{regime_trabalho}}</strong>, com uso de tecnologias de informação e comunicação.</p>
<p>6.2. A EMPREGADORA disponibilizará ou reembolsará os recursos necessários ao desempenho das atividades, na forma ajustada entre as partes e da legislação aplicável.</p>
{{/teletrabalho}}
{{#indeterminado}}
<h2>CLÁUSULA SÉTIMA – DO PRAZO DO CONTRATO</h2>
<p>7.1. O presente contrato é firmado por <strong>prazo indeterminado</strong>, iniciando-se em {{funcionario_data_admissao}}.</p>
{{/indeterminado}}
{{#determinado}}
<h2>CLÁUSULA SÉTIMA – DO PRAZO DO CONTRATO</h2>
<p>7.1. O presente contrato é firmado por <strong>prazo determinado</strong>.</p>
<p>7.2. {{prazo_contrato_descricao}}</p>
{{/determinado}}
{{#intermitente}}
<h2>CLÁUSULA SÉTIMA – DO PRAZO DO CONTRATO</h2>
<p>7.1. O presente contrato é firmado na modalidade de <strong>trabalho intermitente</strong>, com alternância de períodos de prestação de serviços e de inatividade, na forma da lei.</p>
<p>7.2. {{prazo_contrato_descricao}}</p>
{{/intermitente}}
<h2>CLÁUSULA OITAVA – DA CONFIDENCIALIDADE</h2>
<p>8.1. O(A) EMPREGADO(A) compromete-se a manter sigilo sobre informações confidenciais da EMPREGADORA durante e após a vigência deste contrato.</p>
<p>8.2. A violação do dever de confidencialidade poderá ensejar sanções disciplinares, rescisão por justa causa e reparação por perdas e danos. {{confidencialidade_multa}}</p>
<h2>CLÁUSULA NONA – DA RESCISÃO</h2>
<p>9.1. A rescisão contratual observará as disposições legais aplicáveis, com pagamento das verbas devidas e entrega dos documentos obrigatórios.</p>
<h2>CLÁUSULA DÉCIMA – DAS CONSIDERAÇÕES FINAIS</h2>
<p>10.1. Este contrato poderá ser alterado apenas mediante acordo escrito entre as partes.</p>
<p>10.2. Fica eleito o foro da comarca de {{foro_cidade}}, com renúncia a qualquer outro, para dirimir dúvidas ou questões decorrentes deste contrato.</p>
<p>E, por estarem justas e contratadas, as partes assinam o presente instrumento em 2 (duas) vias de igual teor e forma.</p>
<p class="data-direita">{{foro_cidade}}, {{data_hoje_extenso}}.</p>
<table class="assinaturas sem-quebra">
<tr>
<td>
<div class="linha-assinatura"></div>
<div class="assinatura-nome">{{funcionario_nome}}</div>
<div class="assinatura-papel">EMPREGADO(A)</div>
</td>
<td>
<div class="linha-assinatura"></div>
<div class="assinatura-nome">{{empresa_nome}}</div>
<div class="assinatura-papel">EMPREGADORA</div>
</td>
</tr>
</table>
</div>
HTML,
            'conteudo_texto' => 'Contrato Individual de Trabalho',
            'usa_ia' => 1,
            'ativo' => 1,
            'versao' => '2.0',
            'updated_at' => now(),
        ];

        $exists = DB::table('rh_document_templates')->where('slug', 'contrato-trabalho-clt')->exists();
        if ($exists) {
            DB::table('rh_document_templates')->where('slug', 'contrato-trabalho-clt')->update($payload);
            return;
        }

        DB::table('rh_document_templates')->insert(array_merge($payload, [
            'empresa_id' => null,
            'created_at' => now(),
        ]));
    }

    public function down(): void
    {
    }
};
