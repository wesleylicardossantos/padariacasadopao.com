<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('rh_document_templates')) {
            Schema::create('rh_document_templates', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('empresa_id')->nullable()->index();
                $table->string('nome', 150);
                $table->string('slug', 160)->nullable()->index();
                $table->string('categoria', 60)->nullable()->index();
                $table->string('tipo_documento', 80)->nullable()->index();
                $table->string('descricao', 255)->nullable();
                $table->longText('conteudo_html')->nullable();
                $table->longText('conteudo_texto')->nullable();
                $table->boolean('usa_ia')->default(true);
                $table->boolean('ativo')->default(true);
                $table->string('versao', 20)->default('1.0');
                $table->unsignedBigInteger('created_by')->nullable()->index();
                $table->unsignedBigInteger('updated_by')->nullable()->index();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('rh_documento_logs')) {
            Schema::create('rh_documento_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('empresa_id')->nullable()->index();
                $table->unsignedBigInteger('documento_id')->nullable()->index();
                $table->unsignedBigInteger('funcionario_id')->nullable()->index();
                $table->string('acao', 60)->index();
                $table->unsignedBigInteger('usuario_id')->nullable()->index();
                $table->text('detalhes')->nullable();
                $table->json('payload_resumo')->nullable();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('rh_documentos')) {
            Schema::table('rh_documentos', function (Blueprint $table) {
                if (!Schema::hasColumn('rh_documentos', 'template_id')) {
                    $table->unsignedBigInteger('template_id')->nullable()->after('funcionario_id')->index();
                }
                if (!Schema::hasColumn('rh_documentos', 'conteudo_html')) {
                    $table->longText('conteudo_html')->nullable()->after('arquivo');
                }
                if (!Schema::hasColumn('rh_documentos', 'conteudo_texto')) {
                    $table->longText('conteudo_texto')->nullable()->after('conteudo_html');
                }
                if (!Schema::hasColumn('rh_documentos', 'status')) {
                    $table->string('status', 40)->nullable()->after('origem')->index();
                }
                if (!Schema::hasColumn('rh_documentos', 'hash_conteudo')) {
                    $table->string('hash_conteudo', 64)->nullable()->after('status')->index();
                }
                if (!Schema::hasColumn('rh_documentos', 'ia_provider')) {
                    $table->string('ia_provider', 40)->nullable()->after('hash_conteudo');
                }
                if (!Schema::hasColumn('rh_documentos', 'ia_model')) {
                    $table->string('ia_model', 80)->nullable()->after('ia_provider');
                }
            });
        }

        $this->seedTemplates();
    }

    public function down(): void
    {
        if (Schema::hasTable('rh_documentos')) {
            Schema::table('rh_documentos', function (Blueprint $table) {
                foreach (['template_id', 'conteudo_html', 'conteudo_texto', 'status', 'hash_conteudo', 'ia_provider', 'ia_model'] as $column) {
                    if (Schema::hasColumn('rh_documentos', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        Schema::dropIfExists('rh_documento_logs');
        Schema::dropIfExists('rh_document_templates');
    }

    private function seedTemplates(): void
    {
        if (!Schema::hasTable('rh_document_templates')) {
            return;
        }

        $templates = [
            [
                'slug' => 'contrato-trabalho-clt',
                'nome' => 'Contrato de Trabalho CLT',
                'categoria' => 'contrato',
                'tipo_documento' => 'contrato_trabalho',
                'descricao' => 'Contrato base para admissão de colaborador.',
                'conteudo_html' => '<p><strong>{{empresa_nome}}</strong>, inscrita no CNPJ {{empresa_cnpj}}, estabelece o presente contrato de trabalho com <strong>{{funcionario_nome}}</strong>, CPF {{funcionario_cpf}}, para exercer a função de <strong>{{funcionario_cargo}}</strong>, com salário de <strong>{{funcionario_salario}}</strong>.</p><p>O ingresso do colaborador ocorreu em {{funcionario_data_admissao}}, observadas as regras internas, a CLT e as políticas vigentes da empresa.</p><p>{{observacoes_adicionais}}</p><div class="assinatura"><div class="linha"></div><div>{{usuario_responsavel}}</div><div>Responsável pela empresa</div></div>',
            ],
            [
                'slug' => 'termo-experiencia',
                'nome' => 'Termo de Experiência',
                'categoria' => 'contrato',
                'tipo_documento' => 'termo_experiencia',
                'descricao' => 'Termo para vínculo em experiência.',
                'conteudo_html' => '<p>Fica ajustado que {{funcionario_nome}}, CPF {{funcionario_cpf}}, exercerá a função de {{funcionario_cargo}} em período de experiência, a partir de {{funcionario_data_admissao}}, mediante salário de {{funcionario_salario}}.</p><p>O contrato será acompanhado pelo RH e pelas lideranças responsáveis, observadas as rotinas, a política interna e a legislação trabalhista aplicável.</p><p>{{observacoes_adicionais}}</p><div class="assinatura"><div class="linha"></div><div>{{usuario_responsavel}}</div><div>Responsável RH</div></div>',
            ],
            [
                'slug' => 'advertencia-escrita',
                'nome' => 'Advertência Escrita',
                'categoria' => 'disciplinar',
                'tipo_documento' => 'advertencia',
                'descricao' => 'Modelo base de advertência formal.',
                'conteudo_html' => '<p>Por meio deste documento, o colaborador <strong>{{funcionario_nome}}</strong>, CPF {{funcionario_cpf}}, fica formalmente advertido em razão do seguinte fato: <strong>{{motivo_documento}}</strong>.</p><p>Solicita-se imediata adequação de conduta, sob pena de aplicação de medidas disciplinares progressivas previstas nas normas internas e na legislação trabalhista.</p><p>{{observacoes_adicionais}}</p><div class="assinatura"><div class="linha"></div><div>{{funcionario_nome}}</div><div>Colaborador</div></div><div class="assinatura"><div class="linha"></div><div>{{usuario_responsavel}}</div><div>Gestão / RH</div></div>',
            ],
            [
                'slug' => 'suspensao-disciplinar',
                'nome' => 'Suspensão Disciplinar',
                'categoria' => 'disciplinar',
                'tipo_documento' => 'suspensao',
                'descricao' => 'Suspensão disciplinar padronizada.',
                'conteudo_html' => '<p>Aplicamos a presente suspensão disciplinar ao colaborador <strong>{{funcionario_nome}}</strong>, função {{funcionario_cargo}}, em razão de: <strong>{{motivo_documento}}</strong>.</p><p>A medida decorre da reincidência e do descumprimento das normas internas da empresa, com ciência expressa do colaborador.</p><p>{{observacoes_adicionais}}</p><div class="assinatura"><div class="linha"></div><div>{{funcionario_nome}}</div><div>Colaborador</div></div><div class="assinatura"><div class="linha"></div><div>{{usuario_responsavel}}</div><div>Gestão / RH</div></div>',
            ],
            [
                'slug' => 'termo-rescisao',
                'nome' => 'Termo de Rescisão',
                'categoria' => 'rescisao',
                'tipo_documento' => 'rescisao',
                'descricao' => 'Termo base para desligamento.',
                'conteudo_html' => '<p>Fica formalizado o desligamento de <strong>{{funcionario_nome}}</strong>, CPF {{funcionario_cpf}}, ocupante do cargo de {{funcionario_cargo}}, admitido em {{funcionario_data_admissao}}, com rescisão em {{data_rescisao}}.</p><p>Tipo de rescisão: <strong>{{tipo_rescisao}}</strong>. Motivo registrado: <strong>{{motivo_documento}}</strong>.</p><p>As verbas, documentos complementares e orientações finais seguirão o fechamento oficial da folha e dos procedimentos internos da empresa.</p><p>{{observacoes_adicionais}}</p><div class="assinatura"><div class="linha"></div><div>{{usuario_responsavel}}</div><div>Responsável RH</div></div>',
            ],
            [
                'slug' => 'aviso-previo',
                'nome' => 'Aviso Prévio',
                'categoria' => 'rescisao',
                'tipo_documento' => 'aviso_previo',
                'descricao' => 'Aviso prévio base.',
                'conteudo_html' => '<p>Comunicamos ao colaborador <strong>{{funcionario_nome}}</strong> o aviso prévio referente ao encerramento do vínculo empregatício, nos termos da legislação aplicável e das rotinas da empresa.</p><p>Data de admissão: {{funcionario_data_admissao}}. Cargo: {{funcionario_cargo}}. Motivo: {{motivo_documento}}.</p><p>{{observacoes_adicionais}}</p><div class="assinatura"><div class="linha"></div><div>{{funcionario_nome}}</div><div>Colaborador</div></div><div class="assinatura"><div class="linha"></div><div>{{usuario_responsavel}}</div><div>Responsável RH</div></div>',
            ],
            [
                'slug' => 'termo-responsabilidade',
                'nome' => 'Termo de Responsabilidade',
                'categoria' => 'empresa',
                'tipo_documento' => 'termo_responsabilidade',
                'descricao' => 'Termo interno de responsabilidade.',
                'conteudo_html' => '<p>O colaborador <strong>{{funcionario_nome}}</strong>, CPF {{funcionario_cpf}}, declara ciência sobre o uso adequado de bens, documentos, sistemas e informações corporativas de {{empresa_nome}}.</p><p>Compromete-se a observar sigilo, zelo patrimonial, boas práticas operacionais e cumprimento das políticas internas.</p><p>{{observacoes_adicionais}}</p><div class="assinatura"><div class="linha"></div><div>{{funcionario_nome}}</div><div>Colaborador</div></div><div class="assinatura"><div class="linha"></div><div>{{usuario_responsavel}}</div><div>Responsável pela empresa</div></div>',
            ],
            [
                'slug' => 'termo-lgpd-funcionario',
                'nome' => 'Termo LGPD do Funcionário',
                'categoria' => 'juridico',
                'tipo_documento' => 'lgpd',
                'descricao' => 'Ciência sobre tratamento de dados pessoais.',
                'conteudo_html' => '<p>{{funcionario_nome}}, CPF {{funcionario_cpf}}, declara ciência de que seus dados pessoais e dados funcionais serão tratados por {{empresa_nome}} para execução do contrato de trabalho, cumprimento de obrigações legais e gestão de pessoas.</p><p>O tratamento observará controles internos, segurança da informação e legislação vigente.</p><p>{{observacoes_adicionais}}</p><div class="assinatura"><div class="linha"></div><div>{{funcionario_nome}}</div><div>Colaborador</div></div><div class="assinatura"><div class="linha"></div><div>{{usuario_responsavel}}</div><div>Responsável RH</div></div>',
            ],
        ];

        foreach ($templates as $template) {
            $exists = DB::table('rh_document_templates')->where('slug', $template['slug'])->exists();
            if (!$exists) {
                DB::table('rh_document_templates')->insert(array_merge($template, [
                    'empresa_id' => null,
                    'conteudo_texto' => strip_tags($template['conteudo_html']),
                    'usa_ia' => 1,
                    'ativo' => 1,
                    'versao' => '1.0',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }
    }
};
