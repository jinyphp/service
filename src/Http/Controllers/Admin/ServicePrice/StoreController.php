<?php

namespace Jiny\Service\Http\Controllers\Admin\ServicePrice;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Service\Models\SiteService;
use Jiny\Service\Models\ServicePrice;

class StoreController extends Controller
{
    public function __invoke(Request $request, $serviceId)
    {
        $service = SiteService::findOrFail($serviceId);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:255|unique:service_plan_price,code',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'currency' => 'required|string|in:KRW,USD,JPY,EUR',
            'billing_period' => 'required|string|in:monthly,quarterly,yearly,once',
            'billing_cycle_count' => 'required|integer|min:1',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'setup_fee' => 'nullable|numeric|min:0',
            'trial_days' => 'nullable|integer|min:0',
            'min_quantity' => 'nullable|integer|min:1',
            'max_quantity' => 'nullable|integer|min:1',
            'additional_features' => 'nullable|array',
            'pricing_rules' => 'nullable|array',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after:valid_from',
            'auto_renewal' => 'boolean',
            'is_popular' => 'boolean',
            'is_recommended' => 'boolean',
            'pos' => 'required|integer|min:0',
            'enable' => 'boolean',
        ]);

        // 기본값 설정
        $validated['service_id'] = $serviceId;
        $validated['auto_renewal'] = $request->boolean('auto_renewal', true);
        $validated['is_popular'] = $request->boolean('is_popular');
        $validated['is_recommended'] = $request->boolean('is_recommended');
        $validated['enable'] = $request->boolean('enable', true);

        // 할인가 검증 (무료 가격이 아닌 경우에만)
        if ($validated['price'] && $validated['sale_price'] && $validated['sale_price'] >= $validated['price']) {
            return back()
                ->withErrors(['sale_price' => '할인가는 정가보다 낮아야 합니다.'])
                ->withInput();
        }

        // 수량 검증
        if ($validated['max_quantity'] && $validated['min_quantity'] && $validated['max_quantity'] < $validated['min_quantity']) {
            return back()
                ->withErrors(['max_quantity' => '최대 수량은 최소 수량보다 커야 합니다.'])
                ->withInput();
        }

        // 중복 상태 해제
        if ($validated['is_popular']) {
            ServicePrice::where('service_id', $serviceId)
                ->where('is_popular', true)
                ->update(['is_popular' => false]);
        }

        if ($validated['is_recommended']) {
            ServicePrice::where('service_id', $serviceId)
                ->where('is_recommended', true)
                ->update(['is_recommended' => false]);
        }

        ServicePrice::create($validated);

        return redirect()
            ->route('admin.site.services.price.index', $serviceId)
            ->with('success', '서비스 가격이 성공적으로 등록되었습니다.');
    }
}