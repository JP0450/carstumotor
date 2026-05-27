<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;

class ReportService
{
    public function generatePdf(string $view, array $data = [], string $filename = 'report.pdf')
    {
        $pdf = Pdf::loadView($view, $data)->setPaper('a4', 'portrait');

        return $pdf->download($filename);
    }
}
