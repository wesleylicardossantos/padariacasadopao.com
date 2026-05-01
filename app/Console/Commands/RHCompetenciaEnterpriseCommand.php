<?php

namespace App\Console\Commands;

use App\Models\Funcionario;
use App\Models\RHHoleriteEnvio;
use App\Models\RHHoleriteEnvioLote;
use App\Modules\RH\Application\Financeiro\FolhaFinanceiroService;
use App\Modules\RH\Repositories\FuncionarioRepository;
use App\Modules\RH\Services\RHFolhaModuleService;
use App\Services\RHHoleritePdfService;
use App\Services\RHWhatsAppService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

class RHCompetenciaEnterpriseCommand extends Command
{
    protected $signature = 'rh:competencia-enterprise
        {empresa_id : ID da empresa}
        {mes? : Mês da competência}
        {ano? : Ano da competência}
        {--sync-financeiro : sincroniza a folha com contas a pagar}
        {--enviar-email : dispara e-mail do holerite para quem possui e-mail}
        {--enviar-whatsapp : registra/envia WhatsApp para quem possui telefone}
        {--somente-log : apenas registra a análise sem disparos}';

    protected $description = 'Executa rotina enterprise da competência: financeiro, holerites, WhatsApp e trilha de IA.';

    public function __construct(
        private FuncionarioRepository $funcionarios,
        private RHFolhaModuleService $folha,
        private FolhaFinanceiroService $financeiro,
        private RHHoleritePdfService $pdf,
        private RHWhatsAppService $whatsApp,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $empresaId = (int) $this->argument('empresa_id');
        $mes = (int) ($this->argument('mes') ?: date('m'));
        $ano = (int) ($this->argument('ano') ?: date('Y'));

        $resumo = $this->folha->montarResumoDetalhado($empresaId, $mes, $ano);
        $this->registrarExecucaoIa($empresaId, $mes, $ano, $resumo);

        if ($this->option('sync-financeiro')) {
            $total = $this->financeiro->sincronizarCompetencia($empresaId, $mes, $ano);
            $this->info('Integração com financeiro executada: ' . $total . ' apuração(ões).');
        }

        if ($this->option('somente-log')) {
            $this->line('Modo somente-log concluído.');
            return self::SUCCESS;
        }

        $funcionarios = $this->funcionarios->ativosByEmpresa($empresaId)->orderBy('nome')->get();
        $lote = $this->abrirLote($empresaId, $mes, $ano);
        $enviados = 0;
        $whatsapps = 0;
        $documentos = 0;

        foreach ($funcionarios as $funcionario) {
            $envio = $this->registrarEnvio($lote, $funcionario);
            $pdf = $this->pdf->gerarPdfPorFuncionarioEmpresa($empresaId, (int) $funcionario->id, $mes, $ano);

            if ($this->option('enviar-email') && !empty($funcionario->email) && $funcionario->email !== 'null') {
                Mail::raw(
                    'Seu holerite da competência ' . sprintf('%02d/%04d', $mes, $ano) . ' foi gerado com sucesso.',
                    function ($m) use ($funcionario, $pdf, $mes, $ano) {
                        $m->to($funcionario->email, $funcionario->nome)
                          ->subject('Holerite ' . sprintf('%02d/%04d', $mes, $ano))
                          ->attachData($pdf['content'], $pdf['filename'], ['mime' => 'application/pdf']);
                    }
                );
                $enviados++;
                $envio->status = 'enviado';
                $envio->enviado_em = now();
            }

            if ($this->option('enviar-whatsapp')) {
                $msg = 'Olá, ' . $funcionario->nome . '. Seu holerite ' . sprintf('%02d/%04d', $mes, $ano) . ' está disponível.';
                $meta = [
                    'empresa_id' => $empresaId,
                    'funcionario_id' => (int) $funcionario->id,
                    'competencia' => sprintf('%02d/%04d', $mes, $ano),
                    'arquivo' => $pdf['filename'],
                    'tipo' => 'holerite_pdf',
                ];

                $ret = $this->whatsApp->enviarDocumento(
                    $funcionario->celular ?: $funcionario->telefone,
                    $msg,
                    (string) ($pdf['public_url'] ?? ''),
                    $pdf['filename'],
                    $meta,
                );

                if (!empty($ret['ok'])) {
                    $whatsapps++;
                    $documentos++;
                }
                if ($envio->status !== 'enviado') {
                    $envio->status = !empty($ret['ok']) ? 'enviado' : 'fila';
                }
            }

            $envio->payload = [
                'competencia' => sprintf('%02d/%04d', $mes, $ano),
                'arquivo' => $pdf['filename'],
                'arquivo_url' => $pdf['public_url'] ?? null,
                'arquivo_storage' => $pdf['relative_path'] ?? null,
                'hash' => $pdf['hash'] ?? null,
            ];
            $envio->save();
        }

        $lote->recalculateStatus();

        $this->info('Competência processada com sucesso.');
        $this->line('E-mails enviados: ' . $enviados);
        $this->line('WhatsApps processados: ' . $whatsapps);
        $this->line('PDFs enviados por WhatsApp: ' . $documentos);

        return self::SUCCESS;
    }

    private function abrirLote(int $empresaId, int $mes, int $ano): RHHoleriteEnvioLote
    {
        return RHHoleriteEnvioLote::create([
            'empresa_id' => $empresaId,
            'mes' => $mes,
            'ano' => $ano,
            'status' => 'processando',
            'queue_connection' => config('queue.default'),
            'queue_name' => 'default',
            'iniciado_em' => now(),
            'observacao' => 'Lote gerado pela rotina enterprise da competência.',
        ]);
    }

    private function registrarEnvio(RHHoleriteEnvioLote $lote, Funcionario $funcionario): RHHoleriteEnvio
    {
        return RHHoleriteEnvio::create([
            'lote_id' => $lote->id,
            'empresa_id' => $lote->empresa_id,
            'funcionario_id' => $funcionario->id,
            'email' => $funcionario->email,
            'status' => 'fila',
            'tentativas' => 0,
        ]);
    }

    private function registrarExecucaoIa(int $empresaId, int $mes, int $ano, array $resumo): void
    {
        try {
            if (!Schema::hasTable('rh_ia_execucoes')) {
                return;
            }

            DB::table('rh_ia_execucoes')->insert([
                'empresa_id' => $empresaId,
                'competencia' => sprintf('%04d-%02d', $ano, $mes),
                'engine' => 'enterprise-rh-audit-v3',
                'payload_json' => json_encode([
                    'totais' => [
                        'folhaTotal' => $resumo['folhaTotal'] ?? 0,
                        'salarioBase' => $resumo['totalSalarioBase'] ?? 0,
                        'eventos' => $resumo['totalEventos'] ?? 0,
                        'descontos' => $resumo['totalDescontos'] ?? 0,
                    ],
                    'alertas' => $resumo['alertasFinanceiros'] ?? [],
                    'pesoFolha' => $resumo['pesoFolha'] ?? 0,
                    'capitalComprometido' => $resumo['capitalComprometido'] ?? 0,
                    'fase' => 'fase3-zapi-pdf',
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            $this->warn('Não foi possível registrar a execução de IA: ' . $e->getMessage());
        }
    }
}
