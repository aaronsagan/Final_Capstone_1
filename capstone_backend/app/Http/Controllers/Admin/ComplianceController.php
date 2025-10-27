<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Charity, CharityDocument, User};
use App\Services\SecurityService;
use Illuminate\Http\Request;

class ComplianceController extends Controller
{
    protected $securityService;

    public function __construct(SecurityService $securityService)
    {
        $this->securityService = $securityService;
    }

    public function generateReport()
    {
        return response()->json($this->securityService->generateComplianceReport());
    }

    public function complianceStatus()
    {
        $status = $this->securityService->checkComplianceStatus();

        return response()->json([
            'compliance_issues' => $status,
            'generated_at' => now()->toISOString(),
        ]);
    }

    public function charitiesByStatus()
    {
        $statusCounts = Charity::selectRaw('verification_status, COUNT(*) as count')
            ->groupBy('verification_status')
            ->get()
            ->pluck('count', 'verification_status');

        return response()->json([
            'status_breakdown' => $statusCounts,
            'total_charities' => Charity::count(),
        ]);
    }
}
