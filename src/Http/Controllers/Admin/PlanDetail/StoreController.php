<?php

namespace Jiny\Service\Http\Controllers\Admin\PlanDetail;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Service\Models\ServicePlan;
use Jiny\Service\Models\ServicePlanDetail;

class StoreController extends Controller
{
    public function __invoke(Request $request, $planId)
    {
        $plan = ServicePlan::findOrFail($planId);

        $validated = $request->validate([
            'detail_type' => 'required|string|in:feature,limitation,requirement,benefit,support,addon',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:255',
            'value' => 'nullable|string',
            'value_type' => 'required|string|in:text,number,boolean,json,html',
            'unit' => 'nullable|string|max:50',
            'is_highlighted' => 'boolean',
            'show_in_comparison' => 'boolean',
            'show_in_summary' => 'boolean',
            'tooltip' => 'nullable|string',
            'link_url' => 'nullable|url',
            'link_text' => 'nullable|string|max:255',
            'category' => 'nullable|string|in:core,addon,support,limitation,advanced',
            'group_name' => 'nullable|string|max:255',
            'group_order' => 'nullable|integer|min:0',
            'pos' => 'required|integer|min:0',
            'enable' => 'boolean',
        ]);

        // 서비스 플랜 ID 추가
        $validated['service_plan_id'] = $planId;

        // 기본값 설정
        $validated['is_highlighted'] = $request->boolean('is_highlighted');
        $validated['show_in_comparison'] = $request->boolean('show_in_comparison');
        $validated['show_in_summary'] = $request->boolean('show_in_summary');
        $validated['enable'] = $request->boolean('enable', true);

        // JSON 값 타입인 경우 배열 변환
        if ($validated['value_type'] === 'json' && $validated['value']) {
            // 쉼표로 구분된 값들을 배열로 변환
            if (is_string($validated['value'])) {
                $items = array_map('trim', explode(',', $validated['value']));
                $validated['value'] = json_encode($items);
            }
        }

        // Boolean 값 타입인 경우 정규화
        if ($validated['value_type'] === 'boolean' && $validated['value']) {
            $validated['value'] = filter_var($validated['value'], FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false';
        }

        // 조건 데이터 처리 (향후 확장용)
        if ($request->filled('conditions')) {
            $validated['conditions'] = $request->input('conditions');
        }

        $detail = ServicePlanDetail::create($validated);

        return redirect()
            ->route('admin.service.plan.detail.index', $planId)
            ->with('success', '플랜 상세 정보가 성공적으로 추가되었습니다.');
    }
}