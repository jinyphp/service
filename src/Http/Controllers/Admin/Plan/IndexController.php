<?php

namespace Jiny\Service\Http\Controllers\Admin\Plan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Service\Models\ServicePlan;
use Jiny\Service\Models\SiteService;

class IndexController extends Controller
{
    public function __invoke(Request $request)
    {
        // 서비스 필터링을 위한 서비스 목록
        $services = SiteService::select('id', 'title')->orderBy('title')->get();

        // 플랜 목록 조회 with 필터링
        $query = ServicePlan::with('service')
                    ->orderBy('sort_order')
                    ->orderBy('monthly_price');

        // 서비스별 필터링
        if ($request->filled('service_id')) {
            $query->where('service_id', $request->service_id);
        }

        // 플랜 타입별 필터링
        if ($request->filled('plan_type')) {
            $query->where('plan_type', $request->plan_type);
        }

        // 상태별 필터링
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // 검색
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('plan_name', 'like', "%{$search}%")
                  ->orWhere('plan_code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $plans = $query->paginate(15)->withQueryString();

        // 통계 정보
        $stats = [
            'total' => ServicePlan::count(),
            'active' => ServicePlan::where('is_active', true)->count(),
            'featured' => ServicePlan::where('is_featured', true)->count(),
            'with_trial' => ServicePlan::where('allow_trial', true)->where('trial_period_days', '>', 0)->count(),
        ];

        return view('jiny-service::admin.plan.index', compact('plans', 'services', 'stats'));
    }
}