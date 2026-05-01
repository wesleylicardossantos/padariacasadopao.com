<?php

namespace App\Modules\AI\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\AI\Services\BusinessInsightService;
use App\Modules\AI\Services\ForecastService;
use App\Modules\AI\Services\PdvAnomalyService;
use App\Modules\AI\Services\SalesRecommendationService;
use App\Support\Tenancy\InteractsWithTenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GovernanceController extends Controller
{
    use InteractsWithTenantContext;

    public function __construct(
        private BusinessInsightService $insights,
        private ForecastService $forecast,
        private SalesRecommendationService $recommendations,
        private PdvAnomalyService $anomalies,
    ) {
        $this->middleware('tenant.context');
    }

    public function index(Request $request)
    {
        $empresaId = $this->tenantEmpresaId($request);
        return view('enterprise.ai.index', $this->insights->overview($empresaId));
    }

    public function overview(Request $request): JsonResponse
    {
        return response()->json($this->insights->overview($this->tenantEmpresaId($request)));
    }

    public function forecast(Request $request): JsonResponse
    {
        return response()->json($this->forecast->monthlyRevenueForecast($this->tenantEmpresaId($request)));
    }

    public function recommendations(Request $request): JsonResponse
    {
        return response()->json($this->recommendations->recommendations($this->tenantEmpresaId($request)));
    }

    public function anomalies(Request $request): JsonResponse
    {
        return response()->json($this->anomalies->detect($this->tenantEmpresaId($request)));
    }
}
