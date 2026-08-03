<?php

namespace App\Http\Controllers;

use App\Services\ReportingService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReportingController extends Controller
{
    private const TZ = 'Africa/Casablanca';

    public function __construct(private ReportingService $reportingService) {}

    public function index(Request $request)
    {
        $data = $this->reportingService->getIndexData($request);

        return view('reporting.index', $data);
    }

    public function exportPdf(Request $request)
    {
        $data = $this->reportingService->buildExportPdfData($request);

        $pdf = Pdf::loadView('reporting.pdf', $data)
            ->setPaper('a4', 'portrait')
            ->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => false, 'dpi' => 150]);

        return $pdf->download('rapport-rh-' . $data['startDate']->format('Y-m') . '.pdf');
    }


    public function debug(Request $request)
    {
        $data = $this->reportingService->getDebugData();

        return response()->json($data, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
