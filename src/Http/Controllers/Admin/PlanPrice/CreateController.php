<?php

namespace Jiny\Service\Http\Controllers\Admin\PlanPrice;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Service\Models\ServicePlan;
use Jiny\Service\Models\ServicePlanPrice;

class CreateController extends Controller
{
    public function __invoke(Request $request, $planId)
    {
        $plan = ServicePlan::with('service')->findOrFail($planId);

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

        // 다음 정렬순서 제안
        $nextPos = ServicePlanPrice::where('service_plan_id', $planId)->max('pos') + 1;

        // 기본 가격 제안 (기존 가격들 참조)
        $suggestedPrices = $this->getSuggestedPrices($planId);

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

        // 가격 규칙 템플릿
        $pricingRuleTemplates = [
            'early_bird' => [
                'name' => '얼리버드 할인',
                'type' => 'percentage_discount',
                'value' => 20,
                'conditions' => [
                    ['field' => 'signup_date', 'operator' => '<', 'value' => '2024-12-31']
                ]
            ],
            'bulk_discount' => [
                'name' => '대량 구매 할인',
                'type' => 'percentage_discount',
                'value' => 15,
                'conditions' => [
                    ['field' => 'quantity', 'operator' => '>=', 'value' => 10]
                ]
            ],
            'loyalty_discount' => [
                'name' => '기존 고객 할인',
                'type' => 'percentage_discount',
                'value' => 10,
                'conditions' => [
                    ['field' => 'customer_type', 'operator' => '=', 'value' => 'existing']
                ]
            ],
        ];

        return view('jiny-service::admin.plan_price.create', compact(
            'plan',
            'billingPeriods',
            'currencies',
            'nextPos',
            'suggestedPrices',
            'defaultFeatures',
            'pricingRuleTemplates'
        ));
    }

    protected function getSuggestedPrices($planId)
    {
        $existingPrices = ServicePlanPrice::where('service_plan_id', $planId)
                            ->orderBy('price')
                            ->get(['billing_period', 'price']);

        $suggestions = [];

        // 기본 가격 제안
        if ($existingPrices->isEmpty()) {
            $suggestions = [
                'monthly' => 50000,
                'quarterly' => 140000,
                'yearly' => 500000,
                'once' => 300000,
            ];
        } else {
            // 기존 가격 기준으로 제안
            $basePrice = $existingPrices->first()->price;

            $suggestions = [
                'monthly' => $basePrice,
                'quarterly' => round($basePrice * 2.8),  // 약 7% 할인
                'yearly' => round($basePrice * 10),      // 약 17% 할인
                'once' => round($basePrice * 6),         // 6개월치 일회성
            ];
        }

        return $suggestions;
    }
}