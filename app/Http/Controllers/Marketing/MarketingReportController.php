<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Services\Marketing\Reporting\MarketingReportExporter;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MarketingReportController extends Controller
{
    public function exportLeads(Request $request, MarketingReportExporter $exporter): StreamedResponse
    {
        $user = $request->user();
        if ($user === null || ! in_array(strtolower((string) $user->role), ['manager', 'admin', 'super_admin'], true)) {
            abort(403);
        }

        return $exporter->exportCsv($request->only(['from', 'to', 'campaign', 'source', 'service']));
    }
}
