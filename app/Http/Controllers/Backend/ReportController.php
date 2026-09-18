<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Report\FinancialReportService;

class ReportController extends Controller
{
    protected FinancialReportService $reportService;

    public function __construct(FinancialReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function index(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());

        $summary = $this->reportService->generateFinancialSummary($startDate, $endDate);

        return view('backend.reports.index', compact('summary', 'startDate', 'endDate'));
    }

    public function exportCsv(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());

        $csv = $this->reportService->generateOrdersCsv($startDate, $endDate);

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"Financial_Report_{$startDate}_to_{$endDate}.csv\"",
        ]);
    }
}