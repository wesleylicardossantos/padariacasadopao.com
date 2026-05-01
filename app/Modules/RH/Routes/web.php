<?php

use App\Modules\RH\Controllers\AlertaController;
use App\Modules\RH\Controllers\AlertasInteligentesController;
use App\Modules\RH\Controllers\CustoFuncionarioController;
use App\Http\Controllers\OfficialLaborReferenceController;
use App\Modules\RH\Controllers\DashboardController;
use App\Modules\RH\Controllers\DashboardExecutivoController;
use App\Modules\RH\Controllers\DashboardPremiumController;
use App\Modules\RH\Controllers\DesligamentoController;
use App\Modules\RH\Controllers\DocumentoController;
use App\Http\Controllers\RHAclController;
use App\Modules\RH\Controllers\DocumentoGeradoController;
use App\Modules\RH\Controllers\DossieController;
use App\Modules\RH\Controllers\DreFolhaController;
use App\Modules\RH\Controllers\DreFolhaDetalhadoController;
use App\Modules\RH\Controllers\DreInteligenteController;
use App\Modules\RH\Controllers\DrePreditivoController;
use App\Modules\RH\Controllers\EmpresaEnterpriseController;
use App\Modules\RH\Controllers\FaltaController;
use App\Modules\RH\Controllers\FechamentoFolhaController;
use App\Modules\RH\Controllers\FeriasCalculoController;
use App\Modules\RH\Controllers\FeriasController;
use App\Modules\RH\Controllers\FolhaController;
use App\Modules\RH\Controllers\HoleriteController;
use App\Modules\RH\Controllers\IAAprendizadoController;
use App\Modules\RH\Controllers\IAAprovacaoController;
use App\Modules\RH\Controllers\IAAutonomaController;
use App\Modules\RH\Controllers\IADecisaoController;
use App\Modules\RH\Controllers\IAExternaController;
use App\Modules\RH\Controllers\IAHistoricoController;
use App\Modules\RH\Controllers\ModoAbsurdoController;
use App\Modules\RH\Controllers\ModoMaximoController;
use App\Modules\RH\Controllers\MovimentacaoController;
use App\Modules\RH\Controllers\OcorrenciaController;
use App\Modules\RH\Controllers\PainelDonoController;
use App\Modules\RH\Controllers\PreditivoController;
use App\Modules\RH\Controllers\ResumoFinanceiroController;
use App\Modules\RH\Controllers\SalarioController;
use App\Modules\RH\Controllers\V4DashboardController;
use App\Modules\RH\Controllers\V5DashboardController;
use App\Modules\RH\Controllers\WhatsAppBotController;
use App\Modules\RH\Controllers\WhatsAppInteligenteController;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'tenant.context'])->prefix('rh')->group(function () {
    // aliases legados usados por menu
    Route::get('/rh/oficial/cbo', [OfficialLaborReferenceController::class, 'cbo'])->name('rh.oficial.cbo');
    Route::get('/rh/oficial/funcoes', [OfficialLaborReferenceController::class, 'funcoes'])->name('rh.oficial.funcoes');
    Route::post('/rh/oficial/sync', [OfficialLaborReferenceController::class, 'sync'])->name('rh.oficial.sync');
    Route::get('/funcionarios', [\App\Http\Controllers\FuncionarioController::class, 'index'])->name('rh.funcionarios.index');
    Route::get('/eventos', [\App\Http\Controllers\EventoSalarioController::class, 'index'])->name('rh.eventos.index');
    Route::get('/funcionario-eventos', [\App\Http\Controllers\FuncionarioEventoController::class, 'index'])->name('rh.funcionario_eventos.index');
    Route::get('/apuracao-mensal', [\App\Http\Controllers\ApuracaoMensalController::class, 'index'])->name('rh.apuracao_mensal.index');

    // dashboards e núcleo RH
    Route::get('/', [DashboardController::class, 'index'])->name('rh.dashboard');
    Route::get('/dashboard-v4', fn () => Redirect::route('rh.dashboard'))->name('rh.dashboard.v4');
    Route::get('/dashboard-v5', fn () => Redirect::route('rh.dashboard'))->name('rh.dashboard.v5');
    Route::get('/dashboard-executivo', fn () => Redirect::route('rh.dashboard'))->middleware('rh.permission:rh.dashboard.executivo')->name('rh.dashboard_executivo.index');
    Route::get('/dashboard-premium', fn () => Redirect::route('rh.dashboard'))->name('rh.dashboard_premium.index');
    Route::get('/painel-dono', fn () => Redirect::route('rh.dashboard'))->middleware('rh.permission:rh.dashboard.executivo')->name('rh.painel_dono.index');

    // folha e financeiro
    Route::get('/folha', [FolhaController::class, 'index'])->name('rh.folha.index');
    Route::post('/folha/fechar', [FechamentoFolhaController::class, 'store'])->middleware('rh.permission:rh.dossie.automacao.executar')->name('rh.folha.fechar');
    Route::post('/folha/reabrir', [FechamentoFolhaController::class, 'reabrir'])->middleware('rh.permission:rh.dossie.automacao.executar')->name('rh.folha.reabrir');
    Route::get('/financeiro', [FolhaController::class, 'financeiro'])->name('rh.financeiro');
    Route::get('/folha/resumo-financeiro', [ResumoFinanceiroController::class, 'index'])->name('rh.folha.resumo_financeiro');
    Route::get('/recibo/{id}', [FolhaController::class, 'recibo'])->name('rh.folha.recibo');
    Route::get('/holerite/{id}', [HoleriteController::class, 'show'])->name('rh.holerite.show');
    Route::get('/custo-funcionario', [CustoFuncionarioController::class, 'index'])->name('rh.custo_funcionario.index');

    // DRE e analytics
    Route::get('/dre-folha', [DreFolhaController::class, 'index'])->name('rh.dre_folha.index');
    Route::get('/dre-folha-detalhado', [DreFolhaDetalhadoController::class, 'index'])->name('rh.dre_folha_detalhado.index');
    Route::get('/dre-inteligente', [DreInteligenteController::class, 'index'])->name('rh.dre_inteligente.index');
    Route::get('/dre-preditivo', [DrePreditivoController::class, 'index'])->name('rh.dre_preditivo.index');
    Route::get('/ia-decisao', [IADecisaoController::class, 'index'])->name('rh.ia_decisao.index');
    Route::get('/ia-avancada', [IADecisaoController::class, 'index'])->name('rh.ia_avancada.index');
    Route::get('/preditivo-ia', [PreditivoController::class, 'index'])->name('rh.preditivo_ia.index');
    Route::get('/alertas-inteligentes', [AlertasInteligentesController::class, 'index'])->name('rh.alertas_inteligentes.index');
    Route::get('/alertas-inteligentes/ler/{id}', [AlertasInteligentesController::class, 'ler'])->name('rh.alertas_inteligentes.ler');
    Route::get('/alertas', [AlertaController::class, 'index'])->name('rh.alertas.index');

    // IA / automações
    Route::get('/ia-aprovacao', [IAAprovacaoController::class, 'index'])->name('rh.ia_aprovacao.index');
    Route::get('/ia-aprovacao/aprovar', [IAAprovacaoController::class, 'aprovarGet'])->name('rh.ia_aprovacao.aprovar_get');
    Route::post('/ia-aprovacao/aprovar', [IAAprovacaoController::class, 'aprovar'])->name('rh.ia_aprovacao.aprovar');
    Route::get('/ia-aprendizado', [IAAprendizadoController::class, 'index'])->name('rh.ia_aprendizado.index');
    Route::get('/ia-aprendizado/decidir', [IAAprendizadoController::class, 'decidirGet'])->name('rh.ia_aprendizado.decidir_get');
    Route::post('/ia-aprendizado/decidir', [IAAprendizadoController::class, 'decidir'])->name('rh.ia_aprendizado.decidir');
    Route::get('/ia-historico', [IAHistoricoController::class, 'index'])->name('rh.ia_historico.index');
    Route::get('/ia-externa', [IAExternaController::class, 'index'])->name('rh.ia_externa.index');
    Route::post('/ia-externa/enviar', [IAExternaController::class, 'enviar'])->name('rh.ia_externa.enviar');
    Route::get('/ia-autonoma', [IAAutonomaController::class, 'index'])->name('rh.ia_autonoma.index');
    Route::get('/enterprise-total', [EmpresaEnterpriseController::class, 'index'])->name('rh.enterprise_total.index');
    Route::get('/enterprise-alertas', [EmpresaEnterpriseController::class, 'alertas'])->name('rh.enterprise_total.alertas');
    Route::get('/maximo', [ModoMaximoController::class, 'index'])->name('rh.maximo.index');
    Route::get('/absurdo', [ModoAbsurdoController::class, 'index'])->name('rh.absurdo.index');

    // WhatsApp / bots
    Route::get('/whatsapp-bot', [WhatsAppBotController::class, 'index'])->name('rh.whatsapp_bot.index');
    Route::post('/whatsapp-bot', [WhatsAppBotController::class, 'responder'])->name('rh.whatsapp_bot.responder');
    Route::get('/whatsapp-inteligente', [WhatsAppInteligenteController::class, 'index'])->name('rh.whatsapp_inteligente.index');
    Route::post('/whatsapp-inteligente', [WhatsAppInteligenteController::class, 'responder'])->name('rh.whatsapp_inteligente.responder');

    // pessoas / cadastros
    Route::get('/salarios', [SalarioController::class, 'index'])->name('rh.salarios.index');
    Route::get('/salarios/create', [SalarioController::class, 'create'])->name('rh.salarios.create');
    Route::post('/salarios', [SalarioController::class, 'store'])->name('rh.salarios.store');

    Route::get('/movimentacoes', [MovimentacaoController::class, 'index'])->name('rh.movimentacoes.index');
    Route::get('/movimentacoes/create', [MovimentacaoController::class, 'create'])->name('rh.movimentacoes.create');
    Route::post('/movimentacoes', [MovimentacaoController::class, 'store'])->name('rh.movimentacoes.store');
    Route::get('/movimentacoes/{id}/edit', [MovimentacaoController::class, 'edit'])->name('rh.movimentacoes.edit');
    Route::post('/movimentacoes/{id}', [MovimentacaoController::class, 'update'])->name('rh.movimentacoes.update');

    Route::get('/ferias', [FeriasController::class, 'index'])->name('rh.ferias.index');
    Route::get('/ferias/create', [FeriasController::class, 'create'])->name('rh.ferias.create');
    Route::post('/ferias', [FeriasController::class, 'store'])->name('rh.ferias.store');
    Route::get('/ferias/calculo', [FeriasCalculoController::class, 'index'])->name('rh.ferias.calculo');

    Route::get('/faltas', [FaltaController::class, 'index'])->name('rh.faltas.index');
    Route::get('/faltas/create', [FaltaController::class, 'create'])->name('rh.faltas.create');
    Route::post('/faltas', [FaltaController::class, 'store'])->name('rh.faltas.store');

    Route::get('/desligamentos', [DesligamentoController::class, 'index'])->name('rh.desligamentos.index');
    Route::get('/desligamentos/create', [DesligamentoController::class, 'create'])->name('rh.desligamentos.create');
    Route::post('/desligamentos', [DesligamentoController::class, 'store'])->name('rh.desligamentos.store');
    Route::get('/desligamentos/dashboard-executivo', [DesligamentoController::class, 'dashboardExecutivo'])->name('rh.desligamentos.dashboard_executivo');
    Route::get('/desligamentos/exportar-fgts', [DesligamentoController::class, 'exportarFgts'])->name('rh.desligamentos.exportar_fgts');
    Route::get('/desligamentos/{id}', [DesligamentoController::class, 'show'])->name('rh.desligamentos.show');
    Route::post('/desligamentos/{id}/reativar', [DesligamentoController::class, 'reativar'])->name('rh.desligamentos.reativar');
    Route::delete('/desligamentos/{id}', [DesligamentoController::class, 'destroy'])->name('rh.desligamentos.destroy');

    Route::get('/dossie/{id}', [DossieController::class, 'show'])->middleware('rh.permission:rh.dossie.visualizar')->name('rh.dossie.show');
    Route::post('/dossie/{id}/documentos', [DossieController::class, 'storeDocumento'])->middleware('rh.permission:rh.dossie.documentos.gerenciar')->name('rh.dossie.documentos.store');
    Route::get('/dossie/{id}/documentos/{documentoId}/download', [DossieController::class, 'downloadDocumento'])->name('rh.dossie.documentos.download');
    Route::delete('/dossie/{id}/documentos/{documentoId}', [DossieController::class, 'destroyDocumento'])->middleware('rh.permission:rh.dossie.documentos.excluir')->name('rh.dossie.documentos.destroy');
    Route::post('/dossie/{id}/eventos', [DossieController::class, 'storeEvento'])->middleware('rh.permission:rh.dossie.eventos.gerenciar')->name('rh.dossie.eventos.store');
    Route::delete('/dossie/{id}/eventos/{eventoId}', [DossieController::class, 'destroyEvento'])->middleware('rh.permission:rh.dossie.eventos.gerenciar')->name('rh.dossie.eventos.destroy');
    Route::get('/documentos', [DocumentoController::class, 'index'])->middleware('rh.permission:rh.dossie.documentos.gerenciar')->name('rh.documentos.index');
    Route::get('/documentos/create', [DocumentoController::class, 'create'])->middleware('rh.permission:rh.dossie.documentos.gerenciar')->name('rh.documentos.create');
    Route::post('/documentos', [DocumentoController::class, 'store'])->middleware('rh.permission:rh.dossie.documentos.gerenciar')->name('rh.documentos.store');
    Route::get('/documentos/{id}/preview', [DocumentoController::class, 'preview'])->middleware('rh.permission:rh.dossie.visualizar')->name('rh.documentos.preview');
    Route::get('/documentos/{id}/download', [DocumentoController::class, 'download'])->middleware('rh.permission:rh.dossie.visualizar')->name('rh.documentos.download');
    Route::delete('/documentos/{id}', [DocumentoController::class, 'destroy'])->middleware('rh.permission:rh.dossie.documentos.excluir')->name('rh.documentos.destroy');

    Route::get('/documentos/templates', [DocumentoController::class, 'templatesIndex'])->middleware('rh.permission:rh.dossie.documentos.gerenciar')->name('rh.documentos.templates.index');
    Route::get('/documentos/templates/create', [DocumentoController::class, 'templatesCreate'])->middleware('rh.permission:rh.dossie.documentos.gerenciar')->name('rh.documentos.templates.create');
    Route::post('/documentos/templates', [DocumentoController::class, 'templatesStore'])->middleware('rh.permission:rh.dossie.documentos.gerenciar')->name('rh.documentos.templates.store');
    Route::get('/documentos/templates/{id}/edit', [DocumentoController::class, 'templatesEdit'])->middleware('rh.permission:rh.dossie.documentos.gerenciar')->name('rh.documentos.templates.edit');
    Route::get('/acl', [RHAclController::class, 'index'])->name('rh.acl.index');
    Route::post('/acl/sync-defaults', [RHAclController::class, 'syncDefaults'])->name('rh.acl.sync');
    Route::post('/acl/assign', [RHAclController::class, 'assign'])->name('rh.acl.assign');
    Route::put('/documentos/templates/{id}', [DocumentoController::class, 'templatesUpdate'])->middleware('rh.permission:rh.dossie.documentos.gerenciar')->name('rh.documentos.templates.update');
    Route::delete('/documentos/templates/{id}', [DocumentoController::class, 'templatesDestroy'])->middleware('rh.permission:rh.dossie.documentos.excluir')->name('rh.documentos.templates.destroy');

    Route::get('/documentos/contrato/{id}', [DocumentoGeradoController::class, 'contrato'])->name('rh.documentos.contrato');
    Route::get('/documentos/ferias/{id}', [DocumentoGeradoController::class, 'avisoFerias'])->name('rh.documentos.ferias');
    Route::get('/documentos/desligamento/{id}', [DocumentoGeradoController::class, 'termoDesligamento'])->name('rh.documentos.desligamento');
    Route::get('/documentos/trct/{id}', [DocumentoGeradoController::class, 'trct'])->name('rh.documentos.trct');
    Route::get('/documentos/trct/{id}/pdf', [DocumentoGeradoController::class, 'trctPdf'])->name('rh.documentos.trct.pdf');
    Route::get('/documentos/tqrct/{id}', [DocumentoGeradoController::class, 'tqrct'])->name('rh.documentos.tqrct');
    Route::get('/documentos/tqrct/{id}/pdf', [DocumentoGeradoController::class, 'tqrctPdf'])->name('rh.documentos.tqrct.pdf');
    Route::get('/documentos/homologacao/{id}', [DocumentoGeradoController::class, 'homologacao'])->name('rh.documentos.homologacao');
    Route::get('/documentos/homologacao/{id}/pdf', [DocumentoGeradoController::class, 'homologacaoPdf'])->name('rh.documentos.homologacao.pdf');

    // ocorrências gerais
    Route::get('/ocorrencias', [OcorrenciaController::class, 'index'])->name('rh.ocorrencias.index');
    Route::get('/ocorrencias/create', [OcorrenciaController::class, 'create'])->name('rh.ocorrencias.create');
    Route::post('/ocorrencias', [OcorrenciaController::class, 'store'])->name('rh.ocorrencias.store');
    Route::get('/ocorrencias/{id}/edit', [OcorrenciaController::class, 'edit'])->name('rh.ocorrencias.edit');
    Route::put('/ocorrencias/{id}', [OcorrenciaController::class, 'update'])->name('rh.ocorrencias.update');
    Route::delete('/ocorrencias/{id}', [OcorrenciaController::class, 'destroy'])->name('rh.ocorrencias.destroy');

    Route::get('/modular/health', function () {
        return response()->json([
            'ok' => true,
            'module' => 'RH',
            'mode' => 'full-cleanup',
        ]);
    })->name('rh.modular.health');
});
