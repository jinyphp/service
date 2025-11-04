<?php

namespace Jiny\Service\Http\Controllers\Admin\PlanDetail;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Service\Models\ServicePlan;
use Jiny\Service\Models\ServicePlanDetail;

class IndexController extends Controller
{
    public function __invoke(Request $request, $planId)
    {
        $plan = ServicePlan::with('service')->findOrFail($planId);

        // 상세 정보 목록 조회 with 필터링
        $query = ServicePlanDetail::where('service_plan_id', $planId)
                    ->orderBy('category')
                    ->orderBy('group_name')
                    ->orderBy('group_order')
                    ->orderBy('pos');

        // 상세 타입별 필터링
        if ($request->filled('detail_type')) {
            $query->where('detail_type', $request->detail_type);
        }

        // 카테고리별 필터링
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // 그룹별 필터링
        if ($request->filled('group_name')) {
            $query->where('group_name', $request->group_name);
        }

        // 상태별 필터링
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('enable', true);
            } elseif ($request->status === 'inactive') {
                $query->where('enable', false);
            }
        }

        // 표시 옵션별 필터링
        if ($request->filled('display')) {
            if ($request->display === 'comparison') {
                $query->where('show_in_comparison', true);
            } elseif ($request->display === 'summary') {
                $query->where('show_in_summary', true);
            } elseif ($request->display === 'highlighted') {
                $query->where('is_highlighted', true);
            }
        }

        // 검색
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('value', 'like', "%{$search}%");
            });
        }

        $details = $query->paginate(20)->withQueryString();

        // 통계 정보
        $stats = [
            'total' => ServicePlanDetail::where('service_plan_id', $planId)->count(),
            'active' => ServicePlanDetail::where('service_plan_id', $planId)->where('enable', true)->count(),
            'features' => ServicePlanDetail::where('service_plan_id', $planId)->where('detail_type', 'feature')->count(),
            'limitations' => ServicePlanDetail::where('service_plan_id', $planId)->where('detail_type', 'limitation')->count(),
        ];

        // 필터 옵션들
        $detailTypes = ServicePlanDetail::getDetailTypes();
        $categories = ServicePlanDetail::getCategories();

        // 그룹명 목록 (현재 플랜의 실제 그룹들)
        $groups = ServicePlanDetail::where('service_plan_id', $planId)
                    ->whereNotNull('group_name')
                    ->distinct()
                    ->pluck('group_name')
                    ->filter()
                    ->sort()
                    ->values();

        return view('jiny-service::admin.plan_detail.index', compact(
            'plan',
            'details',
            'stats',
            'detailTypes',
            'categories',
            'groups'
        ));
    }
}