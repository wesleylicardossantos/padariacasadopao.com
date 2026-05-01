-- Template jurídico: TERMO DE AUTORIZAÇÃO DE USO DE IMAGEM
-- Seguro para Hostgator/phpMyAdmin. Não altera estrutura e não apaga documentos existentes.
-- Execute somente depois de existir a tabela rh_document_templates.

SET @slug = 'termo-autorizacao-uso-imagem';
SET @nome = 'TERMO DE AUTORIZAÇÃO DE USO DE IMAGEM';
SET @categoria = 'AUTORIZAÇÃO';
SET @tipo_documento = 'termo_autorizacao_uso_imagem';
SET @descricao = 'Autorização de uso de imagem, voz e cessão de direitos (Lei n. 9.610/98).';
SET @conteudo_html = '<style>
    .doc-termo-imagem{font-family:DejaVu Sans, Arial, sans-serif;color:#111;line-height:1.55;font-size:12px;text-align:justify}
    .doc-termo-imagem h1{font-size:15px;text-align:center;margin:0 0 18px 0;text-transform:uppercase;letter-spacing:.2px}
    .doc-termo-imagem p{margin:0 0 12px 0}
    .doc-termo-imagem .linha-local{margin-top:28px;text-align:left}
    .doc-termo-imagem .assinatura{margin-top:34px;text-align:center}
    .doc-termo-imagem .linha-assinatura{border-top:1px solid #111;width:70%;margin:0 auto 6px auto;height:1px}
    .doc-termo-imagem .testemunhas{margin-top:34px}
    .doc-termo-imagem .testemunhas-titulo{font-weight:bold;margin-bottom:20px;text-align:left}
    .doc-termo-imagem .duas-colunas{display:table;width:100%}
    .doc-termo-imagem .coluna{display:table-cell;width:50%;padding:0 16px;vertical-align:top;text-align:left}
    .doc-termo-imagem .linha-testemunha{border-top:1px solid #111;width:90%;height:1px;margin-bottom:7px}
</style>
<div class="doc-termo-imagem">
    <h1>TERMO DE AUTORIZAÇÃO DE USO DE IMAGEM, VOZ E RESPECTIVA CESSÃO DE DIREITOS (LEI N. 9.610/98)</h1>

    <p>Pelo presente instrumento, eu, <strong>{{funcionario_nome}}</strong>, portador(a) do RG nº <strong>{{funcionario_rg}}</strong> e do CPF nº <strong>{{funcionario_cpf}}</strong>, domiciliado(a) na cidade/estado <strong>{{funcionario_municipio}}/{{funcionario_uf}}</strong>, AUTORIZO, de forma gratuita e sem qualquer ônus, à <strong>{{empresa_nome}}</strong>, a utilização de minha(s) imagem(ns) e/ou voz e/ou de informações pessoais em suas divulgações, se houver, em todos os meios de divulgação possíveis, quer sejam na mídia impressa (livros, catálogos, revistas, jornais, entre outros), televisiva (propagandas para televisão aberta e/ou fechada, vídeos, filmes, entre outros), radiofônica (programas de rádio/podcasts), internet, mídia e rede social (Instagram, Facebook, WhatsApp entre outros), banco de dados informatizados, multimídia, entre outros, e nos meios de comunicação interna, como jornais e periódicos em geral, na forma de impresso, voz e imagem.</p>

    <p>A presente autorização e cessão são outorgadas livre e espontaneamente, em caráter gratuito, não incorrendo à autorizada qualquer custo ou ônus, seja a que título for, sendo que estas são firmadas em caráter irrevogável, irretratável, e por prazo indeterminado, obrigando, inclusive, eventuais herdeiros e sucessores outorgantes.</p>

    <p class="linha-local"><strong>{{empresa_municipio}}/{{empresa_uf}}, {{data_hoje_extenso}}.</strong></p>

    <div class="assinatura">
        <div class="linha-assinatura"></div>
        <strong>ASSINATURA</strong><br>
        {{funcionario_nome}}<br>
        CPF: {{funcionario_cpf}}
    </div>

    <div class="testemunhas">
        <div class="testemunhas-titulo">TESTEMUNHAS:</div>
        <div class="duas-colunas">
            <div class="coluna">
                <div class="linha-testemunha"></div>
                CPF:
            </div>
            <div class="coluna">
                <div class="linha-testemunha"></div>
                CPF:
            </div>
        </div>
    </div>
</div>';
SET @conteudo_texto = '.doc-termo-imagem{font-family:DejaVu Sans, Arial, sans-serif;color:#111;line-height:1.55;font-size:12px;text-align:justify} .doc-termo-imagem h1{font-size:15px;text-align:center;margin:0 0 18px 0;text-transform:uppercase;letter-spacing:.2px} .doc-termo-imagem p{margin:0 0 12px 0} .doc-termo-imagem .linha-local{margin-top:28px;text-align:left} .doc-termo-imagem .assinatura{margin-top:34px;text-align:center} .doc-termo-imagem .linha-assinatura{border-top:1px solid #111;width:70%;margin:0 auto 6px auto;height:1px} .doc-termo-imagem .testemunhas{margin-top:34px} .doc-termo-imagem .testemunhas-titulo{font-weight:bold;margin-bottom:20px;text-align:left} .doc-termo-imagem .duas-colunas{display:table;width:100%} .doc-termo-imagem .coluna{display:table-cell;width:50%;padding:0 16px;vertical-align:top;text-align:left} .doc-termo-imagem .linha-testemunha{border-top:1px solid #111;width:90%;height:1px;margin-bottom:7px} TERMO DE AUTORIZAÇÃO DE USO DE IMAGEM, VOZ E RESPECTIVA CESSÃO DE DIREITOS (LEI N. 9.610/98) Pelo presente instrumento, eu, {{funcionario_nome}} , portador(a) do RG nº {{funcionario_rg}} e do CPF nº {{funcionario_cpf}} , domiciliado(a) na cidade/estado {{funcionario_municipio}}/{{funcionario_uf}} , AUTORIZO, de forma gratuita e sem qualquer ônus, à {{empresa_nome}} , a utilização de minha(s) imagem(ns) e/ou voz e/ou de informações pessoais em suas divulgações, se houver, em todos os meios de divulgação possíveis, quer sejam na mídia impressa (livros, catálogos, revistas, jornais, entre outros), televisiva (propagandas para televisão aberta e/ou fechada, vídeos, filmes, entre outros), radiofônica (programas de rádio/podcasts), internet, mídia e rede social (Instagram, Facebook, WhatsApp entre outros), banco de dados informatizados, multimídia, entre outros, e nos meios de comunicação interna, como jornais e periódicos em geral, na forma de impresso, voz e imagem. A presente autorização e cessão são outorgadas livre e espontaneamente, em caráter gratuito, não incorrendo à autorizada qualquer custo ou ônus, seja a que título for, sendo que estas são firmadas em caráter irrevogável, irretratável, e por prazo indeterminado, obrigando, inclusive, eventuais herdeiros e sucessores outorgantes. {{empresa_municipio}}/{{empresa_uf}}, {{data_hoje_extenso}}. ASSINATURA {{funcionario_nome}} CPF: {{funcionario_cpf}} TESTEMUNHAS: CPF: CPF:';

UPDATE rh_document_templates
SET
    nome = @nome,
    categoria = @categoria,
    tipo_documento = @tipo_documento,
    descricao = @descricao,
    conteudo_html = @conteudo_html,
    conteudo_texto = @conteudo_texto,
    usa_ia = 1,
    ativo = 1,
    versao = '1.0',
    updated_at = NOW()
WHERE slug = @slug;

INSERT INTO rh_document_templates
    (empresa_id, nome, slug, categoria, tipo_documento, descricao, conteudo_html, conteudo_texto, usa_ia, ativo, versao, created_at, updated_at)
SELECT
    NULL, @nome, @slug, @categoria, @tipo_documento, @descricao, @conteudo_html, @conteudo_texto, 1, 1, '1.0', NOW(), NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM rh_document_templates WHERE slug = @slug
);
