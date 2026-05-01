<?php

namespace App\Services\RH;

use Dompdf\Dompdf;
use Dompdf\Options;

class RHDocumentoPdfService
{
    public function gerar(string $titulo, string $html): string
    {
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($this->wrapHtml($titulo, $html), 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }

    private function wrapHtml(string $titulo, string $body): string
    {
        $body = trim($body);
        $titulo = trim(strip_tags($titulo));

        $possuiTituloNoCorpo = (bool) preg_match('/<(h1|h2)[^>]*>/i', $body)
            || stripos($body, 'titulo-documento') !== false;

        $cabecalho = '';
        if (! $possuiTituloNoCorpo && $titulo !== '') {
            $cabecalho = '<h1 class="titulo-documento">' . e($titulo) . '</h1>';
        }

        return '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><style>'
            . '@page{margin:10mm 8mm 12mm 8mm;}'
            . 'body{font-family:Arial,Helvetica,sans-serif;color:#111827;font-size:11px;line-height:1.45;margin:0;}'
            . '.documento{max-width:100%;}'
            . '.titulo-documento{font-size:18px;font-weight:700;letter-spacing:.4px;text-align:center;margin:0 0 14px;text-transform:uppercase;}'
            . '.subtitulo-documento{font-size:12px;text-align:center;margin:0 0 22px;color:#111827;}'
            . 'h1{font-size:18px;font-weight:700;text-align:center;margin:0 0 16px;text-transform:uppercase;}'
            . 'h2,h3,h4{font-size:14px;font-weight:700;margin:18px 0 8px;text-transform:uppercase;}'
            . 'p{margin:0 0 10px;text-align:justify;}'
            . 'ul,ol{margin:0 0 12px 20px;padding:0;}'
            . 'li{margin:0 0 6px;}'
            . 'table{width:100%;border-collapse:collapse;margin:10px 0;}'
            . 'td,th{border:1px solid #a1a1aa;padding:6px;vertical-align:top;}'
            . '.assinaturas{margin-top:56px;width:100%;table-layout:fixed;}'
            . '.assinaturas td{border:none;padding:0 18px;text-align:center;vertical-align:top;}'
            . '.linha-assinatura{border-top:1px solid #111827;height:0;margin:0 auto 10px;max-width:280px;}'
            . '.assinatura-nome{font-weight:700;text-transform:uppercase;margin-bottom:4px;}'
            . '.assinatura-papel{font-weight:700;}'
            . '.data-direita{text-align:right !important;margin-top:30px;margin-bottom:54px;}'
            . '.texto-centralizado{text-align:center;}'
            . '.sem-quebra{page-break-inside:avoid;}'
            . '.trct-document{font-size:10px;line-height:1.18;color:#111;}'
            . '.trct-document table{margin:0;table-layout:fixed;}'
            . '.trct-document td,.trct-document th{border:1px solid #1f2937;padding:3px 4px;vertical-align:top;word-wrap:break-word;}'
            . '.trct-document .title{font-size:15px;font-weight:700;text-align:center;text-transform:uppercase;letter-spacing:.3px;}'
            . '.trct-document .mini{font-size:8px;line-height:1.05;}'
            . '.trct-document .code{font-size:8px;font-weight:400;display:block;margin-bottom:1px;}'
            . '.trct-document .value{font-size:7px;font-weight:400;line-height:1;}'
            . '.trct-document .value.big{font-size:8px;line-height:1;}'
            . '.trct-document .value.xbig{font-size:11px;line-height:1.05;}'
            . '.trct-document .value.center{text-align:center;}'
            . '.trct-document .side{width:26px;text-align:center;font-size:8px;font-weight:700;padding:0;}'
            . '.trct-document .side .vertical{writing-mode:vertical-rl;transform:rotate(180deg);padding:8px 0;letter-spacing:.3px;}'
            . '.trct-document .tight td{padding:2px 3px;}'
            . '.trct-document .money{text-align:right;font-size:11px;font-weight:700;}'
            . '.trct-document .center{text-align:center;}'
            . '.trct-document .top-gap{margin-top:6px;}'
            . '.trct-document .no-border{border:none !important;}'
            . '.trct-document .signature-box{height:48px;}'
            . '.trct-document .homolog-box{height:92px;}'
            . '.trct-document .bank-box{height:116px;}'
            . '.trct-document .orgao-box{height:116px;}'
            . '</style></head><body>'
            . '<div class="documento">'
            . $cabecalho
            . $body
            . '</div>'
            . '</body></html>';
    }
}
