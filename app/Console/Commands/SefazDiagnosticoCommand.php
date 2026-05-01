<?php

namespace App\Console\Commands;

use App\Models\ConfigNota;
use Illuminate\Console\Command;
use NFePHP\NFe\Tools;
use NFePHP\Common\Soap\SoapCurl;
use App\Support\Fiscal\ReadsLegacyPfxCertificate;

class SefazDiagnosticoCommand extends Command
{
    use ReadsLegacyPfxCertificate;

    protected $signature = 'sefaz:diagnostico {empresa_id}';
    protected $description = 'Valida certificado/configuracao SEFAZ e consulta status do servico';

    public function handle(): int
    {
        $empresaId = (int) $this->argument('empresa_id');
        $config = ConfigNota::where('empresa_id', $empresaId)->first();

        if (!$config) {
            $this->error('ConfigNota nao encontrada para a empresa informada.');
            return self::FAILURE;
        }

        if (empty($config->arquivo)) {
            $this->error('Certificado nao encontrado em ConfigNota::arquivo.');
            return self::FAILURE;
        }

        try {
            $toolsConfig = [
                'atualizacao' => date('Y-m-d H:i:s'),
                'tpAmb' => (int) ($config->ambiente ?? 2),
                'razaosocial' => $config->razao_social,
                'siglaUF' => optional($config->cidade)->uf ?? 'MA',
                'cnpj' => preg_replace('/\D+/', '', (string) $config->cnpj),
                'schemes' => 'PL_009_V4',
                'versao' => '4.00',
                'tokenIBPT' => '',
                'CSC' => (string) ($config->csc ?? ''),
                'CSCid' => (string) ($config->csc_id ?? ''),
            ];

            $certificate = $this->readCertificateFromContent($config->arquivo, (string) $config->senha);
            $tools = new Tools(json_encode($toolsConfig), $certificate);
            $soapCurl = new SoapCurl();
            $soapCurl->httpVersion('1.1');
            $tools->loadSoapClass($soapCurl);
            $tools->model(55);

            $status = $tools->sefazStatus();

            $this->info('Certificado e comunicacao SEFAZ inicializados com sucesso.');
            $this->line($status);
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Falha no diagnostico SEFAZ: ' . $e->getMessage());
            logger()->error('Falha no diagnostico SEFAZ', [
                'empresa_id' => $empresaId,
                'erro' => $e->getMessage(),
            ]);
            return self::FAILURE;
        }
    }
}
