<?php

namespace App\Support\Fiscal;

use NFePHP\Common\Certificate;

trait ReadsLegacyPfxCertificate
{
    protected function enableLegacyOpenSsl(): void
    {
        try {
            $candidates = [
                base_path('openssl_legacy.cnf'),
                app_path('openssl-legacy.cnf'),
            ];

            foreach ($candidates as $conf) {
                if (is_string($conf) && file_exists($conf) && function_exists('putenv')) {
                    putenv('OPENSSL_CONF=' . $conf);
                    $_ENV['OPENSSL_CONF'] = $conf;
                    $_SERVER['OPENSSL_CONF'] = $conf;
                    break;
                }
            }
        } catch (\Throwable $e) {
            logger()->warning('Nao foi possivel configurar OPENSSL legacy.', [
                'erro' => $e->getMessage(),
            ]);
        }
    }

    protected function readCertificateFromContent(string $pfxContent, string $password): Certificate
    {
        $this->enableLegacyOpenSsl();
        return Certificate::readPfx($pfxContent, $password);
    }
}
