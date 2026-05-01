<?php

namespace App\Modules\RH\Support;

use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Response;

class RescisaoPdfRenderer
{
    public function render(string $html, string $fileName, string $driver = 'dompdf'): Response
    {
        $driver = strtolower(trim($driver));

        if ($driver === 'snappy' && $this->snappyAvailable()) {
            /** @var mixed $snappy */
            $snappy = app('snappy.pdf.wrapper');
            $snappy->loadHTML($html);
            $snappy->setPaper('a4');
            $snappy->setOrientation('portrait');
            $snappy->setOption('encoding', 'UTF-8');
            $snappy->setOption('margin-top', 3);
            $snappy->setOption('margin-right', 3);
            $snappy->setOption('margin-bottom', 3);
            $snappy->setOption('margin-left', 3);
            $snappy->setOption('print-media-type', true);
            $snappy->setOption('background', true);
            $snappy->setOption('disable-smart-shrinking', true);
            $snappy->setOption('dpi', 144);
            $snappy->setOption('image-dpi', 144);
            $snappy->setOption('zoom', 1);
            return $snappy->inline($fileName);
        }

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('dpi', 144);
        $options->set('isFontSubsettingEnabled', true);
        $options->set('defaultMediaType', 'print');
        $options->set('chroot', base_path());

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return response($dompdf->output())
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $fileName . '"');
    }

    public function snappyAvailable(): bool
    {
        return class_exists('Barryvdh\\Snappy\\ServiceProvider') && app()->bound('snappy.pdf.wrapper');
    }
}
