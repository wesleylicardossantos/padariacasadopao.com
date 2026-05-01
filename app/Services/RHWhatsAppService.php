<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class RHWhatsAppService
{
    public function limparNumero(?string $numero): string
    {
        $numero = preg_replace('/\D+/', '', (string) $numero);

        if ($numero !== '' && !Str::startsWith($numero, '55')) {
            $numero = '55' . $numero;
        }

        return $numero;
    }

    public function gerarLink(?string $numero, string $mensagem): ?string
    {
        $numero = $this->limparNumero($numero);
        if ($numero === '') {
            return null;
        }

        return 'https://wa.me/' . $numero . '?text=' . rawurlencode($mensagem);
    }

    public function enviar(?string $numero, string $mensagem, array $meta = []): array
    {
        $link = $this->gerarLink($numero, $mensagem);
        $numeroLimpo = $this->limparNumero($numero);

        if (!$link) {
            return $this->finalizar([
                'ok' => false,
                'manual' => true,
                'mensagem' => 'Funcionário sem número de WhatsApp cadastrado.',
                'link' => null,
            ], $meta, 'whatsapp');
        }

        $provider = $this->provider();
        $url = $this->baseUrl();
        if ($url === '') {
            return $this->finalizar([
                'ok' => true,
                'manual' => true,
                'mensagem' => 'Integração pronta para WhatsApp Web. Abra o link para concluir o envio.',
                'link' => $link,
                'provider' => 'manual',
            ], $meta, 'whatsapp');
        }

        try {
            $response = $this->client($provider)->post($this->endpointUrl($provider, 'text'), $this->montarPayload($provider, $numeroLimpo, $mensagem));

            return $this->finalizar([
                'ok' => $response->successful(),
                'manual' => false,
                'mensagem' => $response->successful()
                    ? 'Mensagem enviada ao provedor de WhatsApp.'
                    : ('Falha no provedor: ' . $response->body()),
                'link' => $link,
                'provider' => $provider,
                'status_code' => $response->status(),
                'response' => $response->json() ?? $response->body(),
            ], $meta, 'whatsapp');
        } catch (\Throwable $e) {
            return $this->finalizar([
                'ok' => false,
                'manual' => true,
                'mensagem' => 'Falha no envio automático. Abra o link para concluir manualmente.',
                'erro' => $e->getMessage(),
                'link' => $link,
                'provider' => $provider,
            ], $meta, 'whatsapp');
        }
    }

    public function enviarDocumento(?string $numero, string $mensagem, string $documentUrl, string $filename, array $meta = []): array
    {
        $link = $this->gerarLink($numero, $mensagem . "\n" . $documentUrl);
        $numeroLimpo = $this->limparNumero($numero);

        if ($numeroLimpo === '') {
            return $this->finalizar([
                'ok' => false,
                'manual' => true,
                'mensagem' => 'Funcionário sem número de WhatsApp cadastrado.',
                'link' => null,
            ], $meta, 'whatsapp_documento');
        }

        $provider = $this->provider();
        $url = $this->baseUrl();

        if ($url === '' || $documentUrl === '') {
            return $this->finalizar([
                'ok' => true,
                'manual' => true,
                'mensagem' => 'Documento preparado para envio manual no WhatsApp.',
                'link' => $link,
                'provider' => $url === '' ? 'manual' : $provider,
                'document_url' => $documentUrl,
            ], $meta, 'whatsapp_documento');
        }

        try {
            $payload = $this->montarPayloadDocumento($provider, $numeroLimpo, $mensagem, $documentUrl, $filename);
            $response = $this->client($provider)->post($this->endpointUrl($provider, 'document'), $payload);

            return $this->finalizar([
                'ok' => $response->successful(),
                'manual' => false,
                'mensagem' => $response->successful()
                    ? 'Documento enviado ao provedor de WhatsApp.'
                    : ('Falha no provedor: ' . $response->body()),
                'link' => $link,
                'provider' => $provider,
                'document_url' => $documentUrl,
                'status_code' => $response->status(),
                'response' => $response->json() ?? $response->body(),
            ], $meta, 'whatsapp_documento');
        } catch (\Throwable $e) {
            return $this->finalizar([
                'ok' => false,
                'manual' => true,
                'mensagem' => 'Falha no envio automático do PDF. Use o link manual.',
                'erro' => $e->getMessage(),
                'link' => $link,
                'provider' => $provider,
                'document_url' => $documentUrl,
            ], $meta, 'whatsapp_documento');
        }
    }

    private function montarPayload(string $provider, string $numero, string $mensagem): array
    {
        return match ($provider) {
            'zapi' => [
                'phone' => $numero,
                'message' => $mensagem,
            ],
            'ultramsg' => [
                'to' => $numero,
                'body' => $mensagem,
            ],
            'meta' => [
                'messaging_product' => 'whatsapp',
                'to' => $numero,
                'type' => 'text',
                'text' => ['body' => $mensagem],
            ],
            default => [
                'number' => $numero,
                'phone' => $numero,
                'message' => $mensagem,
                'text' => $mensagem,
            ],
        };
    }

    private function montarPayloadDocumento(string $provider, string $numero, string $mensagem, string $documentUrl, string $filename): array
    {
        return match ($provider) {
            'zapi' => [
                'phone' => $numero,
                'document' => $documentUrl,
                'fileName' => $filename,
                'caption' => $mensagem,
            ],
            'ultramsg' => [
                'to' => $numero,
                'filename' => $filename,
                'document' => $documentUrl,
                'caption' => $mensagem,
            ],
            'meta' => [
                'messaging_product' => 'whatsapp',
                'to' => $numero,
                'type' => 'document',
                'document' => [
                    'link' => $documentUrl,
                    'filename' => $filename,
                    'caption' => $mensagem,
                ],
            ],
            default => [
                'number' => $numero,
                'phone' => $numero,
                'document' => $documentUrl,
                'fileName' => $filename,
                'caption' => $mensagem,
            ],
        };
    }

    private function client(string $provider)
    {
        $req = Http::timeout((int) env('RH_WHATSAPP_TIMEOUT', 20))->acceptJson();
        $token = trim((string) env('RH_WHATSAPP_API_TOKEN', ''));
        $clientToken = trim((string) env('RH_WHATSAPP_ZAPI_CLIENT_TOKEN', ''));

        if ($provider === 'zapi') {
            if ($clientToken !== '') {
                $req = $req->withHeaders(['Client-Token' => $clientToken]);
            }
            if ($token !== '') {
                $req = $req->withHeaders(['Authorization' => 'Bearer ' . $token]);
            }
            return $req;
        }

        if ($token !== '') {
            $req = $req->withToken($token);
        }

        return $req;
    }

    private function provider(): string
    {
        return strtolower(trim((string) env('RH_WHATSAPP_PROVIDER', 'generic')));
    }

    private function baseUrl(): string
    {
        return rtrim(trim((string) env('RH_WHATSAPP_API_URL', '')), '/');
    }

    private function endpointUrl(string $provider, string $type): string
    {
        $baseUrl = $this->baseUrl();
        if ($baseUrl === '') {
            return '';
        }

        if ($provider === 'zapi') {
            $path = $type === 'document'
                ? trim((string) env('RH_WHATSAPP_ZAPI_DOCUMENT_PATH', '/send-document'), '/')
                : trim((string) env('RH_WHATSAPP_ZAPI_TEXT_PATH', '/send-text'), '/');

            return $baseUrl . '/' . $path;
        }

        return $baseUrl;
    }

    private function finalizar(array $resultado, array $meta = [], string $canal = 'whatsapp'): array
    {
        $this->logIntegracao($canal, $resultado, $meta);
        return $resultado;
    }

    private function logIntegracao(string $canal, array $resultado, array $meta = []): void
    {
        try {
            if (!Schema::hasTable('integracao_logs')) {
                return;
            }

            DB::table('integracao_logs')->insert([
                'empresa_id' => $meta['empresa_id'] ?? null,
                'funcionario_id' => $meta['funcionario_id'] ?? null,
                'canal' => $canal,
                'status' => !empty($resultado['ok']) ? 'sucesso' : (!empty($resultado['manual']) ? 'manual' : 'falha'),
                'payload_json' => json_encode([
                    'meta' => $meta,
                    'resultado' => $resultado,
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // falha de log não deve interromper o fluxo principal
        }
    }
}
