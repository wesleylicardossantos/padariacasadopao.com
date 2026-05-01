<?php

namespace App\Console\Commands;

use App\Models\Funcionario;
use App\Services\RHHoleritePdfService;
use App\Services\RHWhatsAppService;
use Illuminate\Console\Command;

class RHEnviarHoleriteWhatsappZapiCommand extends Command
{
    protected $signature = 'rh:enviar-holerite-whatsapp-zapi
        {empresa_id : ID da empresa}
        {funcionario_id : ID do funcionário}
        {mes? : Mês da competência}
        {ano? : Ano da competência}';

    protected $description = 'Gera o PDF do holerite e envia pelo provider configurado, com foco no fluxo Z-API.';

    public function __construct(
        private RHHoleritePdfService $pdfService,
        private RHWhatsAppService $whatsAppService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $empresaId = (int) $this->argument('empresa_id');
        $funcionarioId = (int) $this->argument('funcionario_id');
        $mes = (int) ($this->argument('mes') ?: date('m'));
        $ano = (int) ($this->argument('ano') ?: date('Y'));

        $funcionario = Funcionario::query()
            ->where('empresa_id', $empresaId)
            ->findOrFail($funcionarioId);

        $pdf = $this->pdfService->gerarPdfPorFuncionarioEmpresa($empresaId, $funcionarioId, $mes, $ano);
        $mensagem = 'Olá, ' . $funcionario->nome . '. Segue seu holerite da competência ' . sprintf('%02d/%04d', $mes, $ano) . '.';

        $resultado = $this->whatsAppService->enviarDocumento(
            $funcionario->celular ?: $funcionario->telefone,
            $mensagem,
            (string) ($pdf['public_url'] ?? ''),
            $pdf['filename'],
            [
                'empresa_id' => $empresaId,
                'funcionario_id' => $funcionarioId,
                'competencia' => sprintf('%02d/%04d', $mes, $ano),
                'tipo' => 'holerite_pdf_manual',
            ]
        );

        $this->line('Provider: ' . ($resultado['provider'] ?? 'n/d'));
        $this->line('Mensagem: ' . ($resultado['mensagem'] ?? 'n/d'));
        if (!empty($resultado['document_url'])) {
            $this->line('Documento: ' . $resultado['document_url']);
        }
        if (!empty($resultado['link'])) {
            $this->line('Fallback manual: ' . $resultado['link']);
        }

        return !empty($resultado['ok']) ? self::SUCCESS : self::FAILURE;
    }
}
