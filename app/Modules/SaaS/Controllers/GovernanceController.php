<?php

namespace App\Modules\SaaS\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SaaS\Services\BillingService;
use App\Modules\SaaS\Services\OnboardingService;
use App\Modules\SaaS\Services\PlanLimitService;
use App\Modules\SaaS\Services\PlanService;
use App\Modules\SaaS\Services\SubscriptionLifecycleService;
use App\Modules\SaaS\Services\SubscriptionService;
use App\Modules\SaaS\Services\TenantHealthService;
use App\Modules\SaaS\Services\TenantScalabilityService;
use App\Support\Tenancy\InteractsWithTenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GovernanceController extends Controller
{
    use InteractsWithTenantContext;

    protected PlanService $plans;
    protected SubscriptionService $subscriptions;
    protected BillingService $billing;
    protected PlanLimitService $limits;
    protected OnboardingService $onboarding;
    protected SubscriptionLifecycleService $lifecycle;

    public function __construct(
        PlanService $plans,
        SubscriptionService $subscriptions,
        BillingService $billing,
        PlanLimitService $limits,
        OnboardingService $onboarding,
        SubscriptionLifecycleService $lifecycle,
        private TenantScalabilityService $scale,
        private TenantHealthService $health,
    ) {
        $this->middleware('tenant.context');
        $this->plans = $plans;
        $this->subscriptions = $subscriptions;
        $this->billing = $billing;
        $this->limits = $limits;
        $this->onboarding = $onboarding;
        $this->lifecycle = $lifecycle;
    }

    public function index(Request $request)
    {
        $empresaId = $this->resolveEmpresaId($request);
        return view('enterprise.saas.index', $this->buildPayload($empresaId));
    }

    public function overview(Request $request): JsonResponse
    {
        $empresaId = $this->resolveEmpresaId($request);
        return response()->json($this->buildPayload($empresaId));
    }

    public function plans(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'plans' => $this->plans->visiblePlans(),
        ]);
    }

    public function usage(Request $request): JsonResponse
    {
        $empresaId = $this->resolveEmpresaId($request);
        return response()->json([
            'success' => true,
            'empresa_id' => $empresaId,
            'usage' => $this->limits->limitsMatrix($empresaId),
        ]);
    }

    public function billing(Request $request): JsonResponse
    {
        $empresaId = $this->resolveEmpresaId($request);
        return response()->json([
            'success' => true,
            'empresa_id' => $empresaId,
            'billing' => $this->billing->summary($empresaId),
        ]);
    }

    public function scaleReadiness(Request $request): JsonResponse
    {
        $empresaId = $this->resolveEmpresaId($request);
        return response()->json($this->scale->readiness($empresaId));
    }

    public function tenantHealth(Request $request): JsonResponse
    {
        $empresaId = $this->resolveEmpresaId($request);
        return response()->json($this->health->health($empresaId));
    }

    public function platformOverview(Request $request): JsonResponse
    {
        return response()->json($this->scale->platformOverview());
    }

    private function buildPayload(int $empresaId): array
    {
        return [
            'success' => true,
            'module' => 'SaaS',
            'empresa_id' => $empresaId,
            'visible_plans' => $this->plans->visiblePlans(),
            'active_subscription' => $empresaId > 0 ? $this->subscriptions->activeByEmpresa($empresaId) : null,
            'billing' => $empresaId > 0 ? $this->billing->summary($empresaId) : null,
            'usage' => $empresaId > 0 ? $this->limits->limitsMatrix($empresaId) : [],
            'onboarding' => $empresaId > 0 ? $this->onboarding->status($empresaId) : [],
            'lifecycle' => $empresaId > 0 ? $this->lifecycle->current($empresaId) : [],
            'scale_readiness' => $empresaId > 0 ? $this->scale->readiness($empresaId) : [],
            'tenant_health' => $empresaId > 0 ? $this->health->health($empresaId) : [],
            'updated_at' => now()->format('d/m/Y H:i:s'),
        ];
    }

    private function resolveEmpresaId(Request $request): int
    {
        return $this->tenantEmpresaId($request);
    }
}
