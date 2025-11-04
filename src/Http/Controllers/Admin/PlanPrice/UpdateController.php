<?php

namespace Jiny\Service\Http\Controllers\Admin\PlanPrice;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Service\Models\ServicePlan;
use Jiny\Service\Models\ServicePlanPrice;

class UpdateController extends Controller
{
    public function __invoke(Request $request, $planId, $priceId)
    {
        $plan = ServicePlan::findOrFail($planId);

        $price = ServicePlanPrice::where('service_plan_id', $planId)
                   ->findOrFail($priceId);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:255|unique:service_plan_price,code,' . $priceId,
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'currency' => 'required|string|in:KRW,USD,JPY,EUR',
            'billing_period' => 'required|string|in:monthly,quarterly,yearly,once',
            'billing_cycle_count' => 'required|integer|min:1',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'setup_fee' => 'nullable|numeric|min:0',
            'trial_days' => 'nullable|integer|min:0',
            'additional_features' => 'nullable|array',
            'pricing_rules' => 'nullable|array',
            'auto_renewal' => 'boolean',
            'is_popular' => 'boolean',
            'is_recommended' => 'boolean',
            'min_quantity' => 'nullable|integer|min:1',
            'max_quantity' => 'nullable|integer|min:1',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after:valid_from',
            'pos' => 'required|integer|min:0',
            'enable' => 'boolean',
        ]);

        // 기본값 설정
        $validated['auto_renewal'] = $request->boolean('auto_renewal', true);
        $validated['is_popular'] = $request->boolean('is_popular');
        $validated['is_recommended'] = $request->boolean('is_recommended');
        $validated['enable'] = $request->boolean('enable', true);

        // 할인가 검증
        if ($validated['sale_price'] && $validated['sale_price'] >= $validated['price']) {
            return back()
                ->withErrors(['sale_price' => '할인가는 정가보다 낮아야 합니다.'])
                ->withInput();
        }

        // 중복 인기/추천 옵션 체크
        if ($validated['is_popular']) {
            ServicePlanPrice::where('service_plan_id', $planId)
                ->where('id', '!=', $priceId)
                ->where('is_popular', true)
                ->update(['is_popular' => false]);
        }

        if ($validated['is_recommended']) {
            ServicePlanPrice::where('service_plan_id', $planId)
                ->where('id', '!=', $priceId)
                ->where('is_recommended', true)
                ->update(['is_recommended' => false]);
        }

        $price->update($validated);

        return redirect()
            ->route('admin.service.plan.price.index', $planId)
            ->with('success', '플랜 가격 옵션이 성공적으로 수정되었습니다.');
    }
}