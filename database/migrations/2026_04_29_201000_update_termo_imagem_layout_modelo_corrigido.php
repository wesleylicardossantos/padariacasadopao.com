<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('rh_document_templates')) {
            return;
        }

        $html = <<<'HTML'
<style>
.termo-imagem-modelo{font-family:Arial,Helvetica,sans-serif!important;color:#000!important;font-size:16px!important;line-height:1.62!important;width:100%!important;max-width:720px!important;margin:0 auto!important;padding:18px 22px 0 22px!important;box-sizing:border-box!important;}
.termo-imagem-modelo *{box-sizing:border-box!important;color:#000!important;}
.termo-imagem-modelo .titulo{font-size:17px!important;line-height:1.35!important;font-weight:700!important;text-align:center!important;text-transform:uppercase!important;margin:0 0 44px 0!important;letter-spacing:.2px!important;}
.termo-imagem-modelo p{font-size:16px!important;line-height:1.62!important;text-align:justify!important;margin:0 0 14px 0!important;text-indent:0!important;}
.termo-imagem-modelo .p-recuo{text-indent:48px!important;}
.termo-imagem-modelo .linha{display:inline-block!important;border-bottom:1px solid #000!important;height:18px!important;line-height:18px!important;vertical-align:baseline!important;text-align:center!important;padding:0 3px!important;white-space:nowrap!important;overflow:hidden!important;}
.termo-imagem-modelo .linha.nome{width:390px!important;}
.termo-imagem-modelo .linha.rg{width:220px!important;}
.termo-imagem-modelo .linha.cpf{width:260px!important;}
.termo-imagem-modelo .linha.cidade-estado{width:330px!important;}
.termo-imagem-modelo .empresa{font-weight:700!important;}
.termo-imagem-modelo .data-local{text-align:center!important;margin:44px 0 0 0!important;font-size:16px!important;line-height:1.4!important;}
.termo-imagem-modelo .data-local .cidade{display:inline-block!important;border-bottom:1px solid #000!important;width:245px!important;height:18px!important;line-height:18px!important;text-align:center!important;vertical-align:baseline!important;}
.termo-imagem-modelo .data-local .dia{display:inline-block!important;border-bottom:1px solid #000!important;width:42px!important;height:18px!important;line-height:18px!important;text-align:center!important;vertical-align:baseline!important;}
.termo-imagem-modelo .data-local .mes{display:inline-block!important;border-bottom:1px solid #000!important;width:145px!important;height:18px!important;line-height:18px!important;text-align:center!important;vertical-align:baseline!important;}
.termo-imagem-modelo .data-local .ano{display:inline-block!important;border-bottom:1px solid #000!important;width:58px!important;height:18px!important;line-height:18px!important;text-align:center!important;vertical-align:baseline!important;}
.termo-imagem-modelo .assinatura{margin-top:78px!important;text-align:center!important;}
.termo-imagem-modelo .assinatura .linha-assinatura{border-top:1px solid #000!important;width:430px!important;height:1px!important;margin:0 auto 5px auto!important;font-size:1px!important;line-height:1px!important;}
.termo-imagem-modelo .assinatura .rotulo{font-size:14px!important;line-height:1!important;text-align:center!important;}
.termo-imagem-modelo .testemunhas{margin-top:78px!important;font-size:16px!important;line-height:1.4!important;}
.termo-imagem-modelo .testemunhas .label{font-weight:700!important;margin-bottom:48px!important;}
.termo-imagem-modelo .testemunha-linha{border-top:1px solid #000!important;width:330px!important;height:1px!important;margin:0 0 8px 0!important;font-size:1px!important;line-height:1px!important;}
.termo-imagem-modelo .cpf{margin:0 0 42px 0!important;}
@media print{.termo-imagem-modelo{padding-top:14px!important;}}
</style>
<div class="termo-imagem-modelo">
    <div class="titulo">TERMO DE AUTORIZAÇÃO DE USO DE IMAGEM, VOZ E RESPECTIVA CESSÃO DE<br>DIREITOS (LEI N. 9.610/98)</div>

    <p>Pelo presente instrumento, eu,<span class="linha nome">{{funcionario_nome}}</span> ,</p>
    <p>portador do RG nº <span class="linha rg">{{funcionario_rg}}</span> e do CPF nº <span class="linha cpf">{{funcionario_cpf}}</span>,</p>
    <p>domiciliado na cidade/estado <span class="linha cidade-estado">{{funcionario_municipio}}/{{funcionario_uf}}</span>, AUTORIZO, de</p>

    <p>forma gratuita e sem qualquer ônus, à <span class="empresa">{{empresa_nome}}</span>, a utilização de minha(s)</p>
    <p>imagem(ns) e/ou voz e/ou de informações pessoais em suas divulgações, se houver,</p>
    <p>em todos os meios de divulgação possíveis, quer sejam na mídia impressa (livros,</p>
    <p>catálogos, revistas, jornais, entre outros), televisiva (propagandas para televisão aberta</p>
    <p>e/ou fechada, vídeos, filmes, entre outros), radiofônica (programas de rádio/podcasts),</p>
    <p>internet, mídia e rede social (Instagram, Facebook, WhatsApp entre outros ), banco de</p>
    <p>dados informatizados, multimídia, entre outros, e nos meios de comunicação interna,</p>
    <p>como jornais e periódicos em geral, na forma de impresso, voz e imagem.</p>

    <p class="p-recuo">A presente autorização e cessão são outorgadas livre e espontaneamente, em</p>
    <p>caráter gratuito, não incorrendo à autorizada qualquer custo ou ônus, seja a que</p>
    <p>título for, sendo que estas são firmadas em caráter irrevogável, irretratável, e por</p>
    <p>prazo indeterminado, obrigando, inclusive, eventuais herdeiros e sucessores</p>
    <p>outorgantes.</p>

    <div class="data-local"><span class="cidade">{{empresa_municipio}}</span>/ {{empresa_uf}}, <span class="dia">{{data_documento_dia}}</span> de <span class="mes">{{data_documento_mes_extenso}}</span> de <span class="ano">{{data_documento_ano}}</span></div>

    <div class="assinatura">
        <div class="linha-assinatura">&nbsp;</div>
        <div class="rotulo">ASSINATURA</div>
    </div>

    <div class="testemunhas">
        <div class="label">TESTEMUNHAS:</div>
        <div class="testemunha-linha">&nbsp;</div>
        <div class="cpf">CPF:</div>
        <div class="testemunha-linha">&nbsp;</div>
        <div>CPF:</div>
    </div>
</div>
HTML;

        $payload = [
            'empresa_id' => null,
            'nome' => 'TERMO DE AUTORIZAÇÃO DE USO DE IMAGEM',
            'slug' => 'termo-autorizacao-uso-imagem',
            'categoria' => 'AUTORIZAÇÃO',
            'tipo_documento' => 'termo_autorizacao_uso_imagem',
            'descricao' => 'Autorização de uso de imagem, voz e respectiva cessão de direitos (Lei n. 9.610/98).',
            'conteudo_html' => $html,
            'conteudo_texto' => trim(preg_replace('/\s+/', ' ', strip_tags($html))),
            'usa_ia' => 0,
            'ativo' => 1,
            'versao' => '1.2',
            'updated_at' => now(),
        ];

        $query = DB::table('rh_document_templates')->where('slug', 'termo-autorizacao-uso-imagem');
        if ($query->exists()) {
            $query->update($payload);
            return;
        }

        $payload['created_at'] = now();
        DB::table('rh_document_templates')->insert($payload);
    }

    public function down(): void
    {
        // Mantém o template para preservar documentos já gerados.
    }
};
