<?php

namespace Jiny\Service\Http\Controllers\Admin\PlanPrice;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Service\Models\ServicePlan;
use Jiny\Service\Models\ServicePlanPrice;

class EditController extends Controller
{
    public function __invoke(Request $request, $planId, $priceId)
    {
        $plan = ServicePlan::with('service')->findOrFail($planId);

        $price = ServicePlanPrice::where('service_plan_id', $planId)
                   ->findOrFail($priceId);

        // 결제 주기 옵션
        $billingPeriods = [
            'monthly' => '월간',
            'quarterly' => '분기',
            'yearly' => '연간',
            'once' => '일회성',
        ];

        // 통화 옵션
        $currencies = [
            'KRW' => '원 (₩)',
            'USD' => '달러 ($)',
            'JPY' => '엔 (¥)',
            'EUR' => '유로 (€)',
        ];

        // 기본 기능 템플릿
        $defaultFeatures = [
            'priority_support' => '우선 지원',
            'custom_branding' => '커스텀 브랜딩',
            'api_access' => 'API 접근',
            'advanced_analytics' => '고급 분석',
            'white_label' => '화이트 라벨',
            'sla_guarantee' => 'SLA 보장',
            'dedicated_manager' => '전담 매니저',
            'custom_integration' => '커스텀 연동',
        ];

        return view('jiny-service::admin.plan_price.edit', compact(
            'plan',
            'price',
            'billingPeriods',
            'currencies',
            'defaultFeatures'
        ));
    }
}