<?php

namespace Jiny\Service\Http\Controllers\Admin\Plan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Service\Models\ServicePlan;
use Jiny\Service\Models\SiteService;

class EditController extends Controller
{
    public function __invoke(Request $request, $id)
    {
        $plan = ServicePlan::with('service')->findOrFail($id);

        // 서비스 목록 조회
        $services = SiteService::select('id', 'title')
                              ->where('enable', true)
                              ->orderBy('title')
                              ->get();

        // 플랜 타입 옵션
        $planTypes = [
            'basic' => 'Basic',
            'standard' => 'Standard',
            'premium' => 'Premium',
            'enterprise' => 'Enterprise',
            'custom' => 'Custom'
        ];

        // 결제 타입 옵션
        $billingTypes = [
            'subscription' => 'Subscription',
            'one_time' => 'One Time',
            'usage_based' => 'Usage Based',
            'hybrid' => 'Hybrid'
        ];

        // 기본 피처 템플릿
        $defaultFeatures = [
            'api_access' => 'API Access',
            'customer_support' => 'Customer Support',
            'data_export' => 'Data Export',
            'custom_branding' => 'Custom Branding',
            'advanced_analytics' => 'Advanced Analytics',
            'priority_support' => 'Priority Support',
            'white_label' => 'White Label',
            'custom_integrations' => 'Custom Integrations'
        ];

        // 업그레이드/다운그레이드 가능한 플랜 목록 (현재 플랜 제외)
        $availablePlans = ServicePlan::where('service_id', $plan->service_id)
                                   ->where('id', '!=', $plan->id)
                                   ->where('is_active', true)
                                   ->select('id', 'plan_name', 'plan_code')
                                   ->orderBy('monthly_price')
                                   ->get();

        // 현재 플랜의 구독자 수
        $subscribersCount = $plan->serviceUsers()->count();

        // 삭제 가능 여부 확인
        $canDelete = $subscribersCount === 0;
        $deleteReason = $subscribersCount > 0 ? "구독자가 {$subscribersCount}명 있어 삭제할 수 없습니다." : null;

        return view('jiny-service::admin.plan.edit', compact(
            'plan',
            'services',
            'planTypes',
            'billingTypes',
            'defaultFeatures',
            'availablePlans',
            'subscribersCount',
            'canDelete',
            'deleteReason'
        ));
    }
}