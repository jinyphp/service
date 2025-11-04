<?php

namespace Jiny\Service\Http\Controllers\Admin\ServiceDetail;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Service\Models\SiteService;
use Jiny\Service\Models\ServicePlanDetail;

class IndexController extends Controller
{
    public function __invoke(Request $request, $serviceId)
    {
        $service = SiteService::findOrFail($serviceId);

        // 서비스 상세 정보 목록 조회
        $query = ServicePlanDetail::where('service_id', $serviceId)
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
            'total' => ServicePlanDetail::where('service_id', $serviceId)->count(),
            'active' => ServicePlanDetail::where('service_id', $serviceId)->where('enable', true)->count(),
            'features' => ServicePlanDetail::where('service_id', $serviceId)->where('detail_type', 'feature')->count(),
            'limitations' => ServicePlanDetail::where('service_id', $serviceId)->where('detail_type', 'limitation')->count(),
        ];

        // 필터 옵션들
        $detailTypes = ServicePlanDetail::getDetailTypes();
        $categories = ServicePlanDetail::getCategories();

        // 그룹명 목록 (현재 서비스의 실제 그룹들)
        $groups = ServicePlanDetail::where('service_id', $serviceId)
                    ->whereNotNull('group_name')
                    ->distinct()
                    ->pluck('group_name')
                    ->filter()
                    ->sort()
                    ->values();

        return view('jiny-service::admin.service_detail.index', compact(
            'service',
            'details',
            'stats',
            'detailTypes',
            'categories',
            'groups'
        ));
    }
}