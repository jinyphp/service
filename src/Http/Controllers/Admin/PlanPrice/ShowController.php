<?php

namespace Jiny\Service\Http\Controllers\Admin\PlanPrice;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Service\Models\ServicePlan;
use Jiny\Service\Models\ServicePlanPrice;

class ShowController extends Controller
{
    public function __invoke(Request $request, $planId, $priceId)
    {
        $plan = ServicePlan::with('service')->findOrFail($planId);

        $price = ServicePlanPrice::where('service_plan_id', $planId)
                   ->findOrFail($priceId);

        // 같은 결제 주기의 다른 가격 옵션들
        $relatedPrices = ServicePlanPrice::where('service_plan_id', $planId)
                           ->where('billing_period', $price->billing_period)
                           ->where('id', '!=', $priceId)
                           ->enabled()
                           ->ordered()
                           ->get();

        // 가격 비교 데이터
        $priceComparison = $this->getPriceComparison($planId, $price);

        return view('jiny-service::admin.plan_price.show', compact(
            'plan',
            'price',
            'relatedPrices',
            'priceComparison'
        ));
    }

    protected function getPriceComparison($planId, $currentPrice)
    {
        $allPrices = ServicePlanPrice::where('service_plan_id', $planId)
                       ->enabled()
                       ->get();

        $comparison = [];
        foreach ($allPrices as $price) {
            if ($price->id === $currentPrice->id) continue;

            $comparison[] = [
                'name' => $price->name,
                'period' => $price->period_display,
                'price' => $price->effective_price,
                'savings' => $currentPrice->effective_price - $price->effective_price,
            ];
        }

        return $comparison;
    }
}