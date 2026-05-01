<?php

namespace App\Services\RH;

use GuzzleHttp\Client;
use Illuminate\Support\Arr;

class RHDocumentoAIService
{
    public function gerarDocumento(array $payload): array
    {
        $apiKey = (string) env('OPENAI_API_KEY', '');
        $model = (string) env('OPENAI_MODEL', 'gpt-4.1-mini');
        $provider = 'openai';

        $baseHtml = (string) ($payload['html_base'] ?? '');
        $tipo = (string) ($payload['tipo_documento'] ?? 'documento trabalhista');
        $instrucoes = trim((string) ($payload['instrucoes'] ?? ''));
        $variaveis = (array) ($payload['variaveis'] ?? []);
        $usarIa = (bool) ($payload['usar_ia'] ?? false);

        if (!$usarIa || $apiKey === '') {
            return [
                'provider' => $provider,
                'model' => $model,
                'used_ai' => false,
                'html' => $baseHtml,
                'text' => strip_tags($baseHtml),
            ];
        }

        $promptSistema = 'Você é um assistente jurídico-operacional especializado em documentos de RH no Brasil. ' .
            'Use linguagem formal, não invente dados, preserve placeholders já preenchidos e devolva HTML limpo com parágrafos e listas quando necessário.';

        $promptUsuario = "Tipo do documento: {$tipo}\n" .
            'Variáveis já aplicadas: ' . json_encode($variaveis, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n" .
            'HTML base: ' . $baseHtml . "\n" .
            'Instruções adicionais: ' . ($instrucoes !== '' ? $instrucoes : 'Formalizar e revisar juridicamente o texto.') . "\n" .
            'Devolva apenas HTML válido para impressão em A4.';

        try {
            $client = new Client([
                'base_uri' => 'https://api.openai.com/v1/',
                'timeout' => (int) env('OPENAI_TIMEOUT', 45),
            ]);

            $response = $client->post('chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => $model,
                    'temperature' => 0.2,
                    'messages' => [
                        ['role' => 'system', 'content' => $promptSistema],
                        ['role' => 'user', 'content' => $promptUsuario],
                    ],
                ],
            ]);

            $json = json_decode((string) $response->getBody(), true);
            $html = trim((string) Arr::get($json, 'choices.0.message.content', ''));
            if ($html === '') {
                $html = $baseHtml;
            }

            return [
                'provider' => $provider,
                'model' => $model,
                'used_ai' => true,
                'html' => $this->sanitizeHtmlResponse($html),
                'text' => strip_tags($html),
                'raw' => $json,
            ];
        } catch (\Throwable $e) {
            report($e);

            return [
                'provider' => $provider,
                'model' => $model,
                'used_ai' => false,
                'html' => $baseHtml,
                'text' => strip_tags($baseHtml),
                'error' => $e->getMessage(),
            ];
        }
    }

    private function sanitizeHtmlResponse(string $html): string
    {
        $html = preg_replace('/^```html|```$/m', '', trim($html));
        return trim($html);
    }
}
